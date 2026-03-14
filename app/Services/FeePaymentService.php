<?php

namespace App\Services;

use App\Models\FeePayment;
use App\Models\FeeVoucher;
use App\Models\StudentLedger;
use App\Models\StudentAdvanceBalance;
use App\Models\InstallmentSchedule;
use Illuminate\Support\Facades\DB;
use Exception;

class FeePaymentService
{
    /**
     * Process an incoming fee payment
     */
    public function processPayment(array $paymentData)
    {
        DB::beginTransaction();
        try {
            $voucher = FeeVoucher::findOrFail($paymentData['voucher_id']);

            // 1. Create the Payment Record
            $payment = FeePayment::create([
                'receipt_no'            => $this->generateReceiptNo(),
                'voucher_id'            => $voucher->id,
                'student_enrollment_id' => $voucher->student_enrollment_id,
                'paid_amount'           => $paymentData['paid_amount'],
                'payment_date'          => $paymentData['payment_date'] ?? now(),
                'payment_method'        => $paymentData['payment_method'],
                'bank_name'             => $paymentData['bank_name'] ?? null,
                'transaction_ref'       => $paymentData['transaction_ref'] ?? null,
                'received_by'           => auth()->id() ?? 1,
                'is_advance'            => $paymentData['is_advance'] ?? false,
                'notes'                 => $paymentData['notes'] ?? null,
            ]);

            // 2. Handle Advance Payment
            if (!empty($paymentData['is_advance']) && $paymentData['is_advance'] === true) {
                return $this->processAdvancePayment($payment, $paymentData);
            }

            // 3. Update the Voucher balances
            $voucher->paid_amount += $paymentData['paid_amount'];
            $voucher->remaining_amount = $voucher->net_amount - $voucher->paid_amount;

            if ($voucher->remaining_amount <= 0) {
                $voucher->status = 'paid';
            } elseif ($voucher->paid_amount > 0) {
                $voucher->status = 'partial';
            }

            $voucher->save();

            // 4. Update the Student Ledger (Credit)
            $balanceAfter = $this->calculateStudentBalance($voucher->student_enrollment_id);

            StudentLedger::create([
                'student_enrollment_id' => $voucher->student_enrollment_id,
                'transaction_type'      => 'credit',
                'amount'                => $paymentData['paid_amount'],
                'description'           => 'Payment received against voucher ' . $voucher->voucher_no,
                'reference_type'        => 'payment',
                'reference_id'          => $payment->id,
                'balance_after'         => $balanceAfter,
                'created_by'            => auth()->id() ?? 1,
            ]);

            // 5. Update Installment Schedule if applicable
            $this->updateInstallmentSchedule($voucher, $payment);

            // 6. Check and update Previous Year Balance if any payment is made against it
            $this->updatePreviousYearBalance($voucher->student_enrollment_id, $paymentData['paid_amount']);

            DB::commit();
            return [
                'success' => true,
                'payment' => $payment,
                'message' => 'Payment processed successfully. Receipt: ' . $payment->receipt_no,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process advance payment (not linked to specific voucher)
     */
    private function processAdvancePayment(FeePayment $payment, array $paymentData)
    {
        // Update advance balance
        $advanceBalance = StudentAdvanceBalance::firstOrNew([
            'student_enrollment_id' => $payment->student_enrollment_id,
        ]);

        $advanceBalance->balance = ($advanceBalance->balance ?? 0) + $paymentData['paid_amount'];
        $advanceBalance->last_transaction_id = $payment->id;
        $advanceBalance->last_updated = now();
        $advanceBalance->save();

        // Create ledger entry
        StudentLedger::create([
            'student_enrollment_id' => $payment->student_enrollment_id,
            'transaction_type'      => 'credit',
            'amount'                => $paymentData['paid_amount'],
            'description'           => 'Advance payment received. Receipt: ' . $payment->receipt_no,
            'reference_type'        => 'payment',
            'reference_id'          => $payment->id,
            'balance_after'         => $advanceBalance->balance,
            'created_by'            => auth()->id() ?? 1,
        ]);

        DB::commit();
        return [
            'success' => true,
            'payment' => $payment,
            'message' => 'Advance payment recorded successfully. Receipt: ' . $payment->receipt_no,
        ];
    }

    /**
     * Use advance balance to pay off vouchers
     */
    public function useAdvanceBalance($studentEnrollmentId, $amountToUse)
    {
        DB::beginTransaction();
        try {
            $advanceBalance = StudentAdvanceBalance::where('student_enrollment_id', $studentEnrollmentId)
                ->lockForUpdate()
                ->first();

            if (!$advanceBalance || $advanceBalance->balance < $amountToUse) {
                return ['success' => false, 'message' => 'Insufficient advance balance'];
            }

            // Reduce advance balance
            $advanceBalance->balance -= $amountToUse;
            $advanceBalance->last_updated = now();
            $advanceBalance->save();

            // Create ledger entry (debit from advance)
            StudentLedger::create([
                'student_enrollment_id' => $studentEnrollmentId,
                'transaction_type'      => 'debit',
                'amount'                => $amountToUse,
                'description'           => 'Used advance balance for fee payment',
                'reference_type'        => 'advance_adjustment',
                'reference_id'          => null,
                'balance_after'         => $advanceBalance->balance,
                'created_by'            => auth()->id() ?? 1,
            ]);

            DB::commit();
            return [
                'success' => true,
                'message' => 'Advance balance used successfully',
                'remaining_balance' => $advanceBalance->balance,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process payment against installment
     */
    public function processInstallmentPayment($installmentAssignmentId, array $paymentData)
    {
        DB::beginTransaction();
        try {
            $assignment = \App\Models\StudentInstallmentAssignment::with('installmentPlan')
                ->findOrFail($installmentAssignmentId);

            // Create payment
            $payment = FeePayment::create([
                'receipt_no'            => $this->generateReceiptNo(),
                'student_enrollment_id' => $assignment->student_enrollment_id,
                'paid_amount'           => $paymentData['paid_amount'],
                'payment_date'          => $paymentData['payment_date'] ?? now(),
                'payment_method'        => $paymentData['payment_method'],
                'received_by'           => auth()->id() ?? 1,
                'notes'                 => 'Installment payment: ' . ($paymentData['installment_number'] ?? ''),
            ]);

            // Update installment schedule
            InstallmentSchedule::where('student_installment_assignment_id', $assignment->id)
                ->where('installment_number', $paymentData['installment_number'] ?? 1)
                ->update([
                    'payment_id'   => $payment->id,
                    'paid_amount'  => $paymentData['paid_amount'],
                    'payment_date' => now(),
                    'status'       => 'paid',
                ]);

            // Create ledger entry
            StudentLedger::create([
                'student_enrollment_id' => $assignment->student_enrollment_id,
                'transaction_type'      => 'credit',
                'amount'                => $paymentData['paid_amount'],
                'description'           => 'Installment payment received',
                'reference_type'        => 'payment',
                'reference_id'          => $payment->id,
                'balance_after'         => $this->calculateStudentBalance($assignment->student_enrollment_id),
                'created_by'            => auth()->id() ?? 1,
            ]);

            DB::commit();
            return [
                'success' => true,
                'payment' => $payment,
                'message' => 'Installment payment processed successfully',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reverse a payment (for refunds or corrections)
     */
    public function reversePayment(FeePayment $payment, $reason = null)
    {
        DB::beginTransaction();
        try {
            // If payment was against a voucher, restore voucher amounts
            if ($payment->voucher && !$payment->is_advance) {
                $voucher = $payment->voucher;
                $voucher->paid_amount -= $payment->paid_amount;
                $voucher->remaining_amount = $voucher->net_amount - $voucher->paid_amount;

                if ($voucher->paid_amount <= 0) {
                    $voucher->status = 'pending';
                    $voucher->paid_amount = 0;
                } elseif ($voucher->paid_amount < $voucher->net_amount) {
                    $voucher->status = 'partial';
                }

                $voucher->save();
            }

            // Create reversing ledger entry (debit)
            StudentLedger::create([
                'student_enrollment_id' => $payment->student_enrollment_id,
                'transaction_type'      => 'debit',
                'amount'                => $payment->paid_amount,
                'description'           => 'Payment reversed: ' . ($reason ?? 'No reason provided'),
                'reference_type'        => 'payment_reversal',
                'reference_id'          => $payment->id,
                'balance_after'         => $this->calculateStudentBalance($payment->student_enrollment_id),
                'created_by'            => auth()->id() ?? 1,
            ]);

            // Mark payment as deleted (soft delete handles this)
            $payment->delete();

            DB::commit();
            return [
                'success' => true,
                'message' => 'Payment reversed successfully',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Calculate student's current balance from ledger
     */
    public function calculateStudentBalance($enrollmentId)
    {
        $debits = StudentLedger::where('student_enrollment_id', $enrollmentId)
            ->where('transaction_type', 'debit')
            ->sum('amount');

        $credits = StudentLedger::where('student_enrollment_id', $enrollmentId)
            ->where('transaction_type', 'credit')
            ->sum('amount');

        return $debits - $credits;
    }

    /**
     * Generate unique receipt number
     */
    private function generateReceiptNo(): string
    {
        $year = now()->format('Y');
        $lastPayment = FeePayment::where('receipt_no', 'like', "RCP-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPayment && preg_match('/RCP-\d{4}-(\d+)/', $lastPayment->receipt_no, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf("RCP-%s-%05d", $year, $nextNumber);
    }

    /**
     * Update installment schedule after payment
     */
    private function updateInstallmentSchedule(FeeVoucher $voucher, FeePayment $payment)
    {
        $assignment = \App\Models\StudentInstallmentAssignment::where('fee_voucher_id', $voucher->id)
            ->first();

        if (!$assignment) {
            return;
        }

        // Find the current installment
        $schedule = InstallmentSchedule::where('student_installment_assignment_id', $assignment->id)
            ->whereNull('payment_id')
            ->orderBy('installment_number')
            ->first();

        if ($schedule) {
            $schedule->update([
                'payment_id'   => $payment->id,
                'paid_amount'  => $payment->paid_amount,
                'payment_date' => now(),
                'status'       => 'paid',
            ]);
        }
    }

    /**
     * Update previous year balance if payment is made
     */
    private function updatePreviousYearBalance($enrollmentId, $amount)
    {
        $balances = \App\Models\PreviousYearBalance::where('student_enrollment_id', $enrollmentId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('from_academic_year_id')
            ->get();

        $remainingAmount = $amount;

        foreach ($balances as $balance) {
            if ($remainingAmount <= 0) {
                break;
            }

            $recoveryAmount = min($remainingAmount, $balance->remaining_amount);
            $balance->recovered_amount += $recoveryAmount;
            $balance->remaining_amount -= $recoveryAmount;

            if ($balance->remaining_amount <= 0) {
                $balance->status = 'cleared';
            } else {
                $balance->status = 'partial';
            }

            $balance->save();
            $remainingAmount -= $recoveryAmount;
        }
    }

    /**
     * Get payment statistics for dashboard
     */
    public function getPaymentStats($branchId = null, $startDate = null, $endDate = null)
    {
        $query = FeePayment::query();

        if ($branchId) {
            $query->whereHas('studentEnrollment', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        $stats = [
            'total_collected' => $query->sum('paid_amount'),
            'total_payments'  => $query->count(),
            'advance_payments' => $query->where('is_advance', true)->sum('paid_amount'),
            'regular_payments' => $query->where('is_advance', false)->sum('paid_amount'),
            'by_method'       => $query->selectRaw('payment_method, SUM(paid_amount) as total, COUNT(*) as count')
                ->groupBy('payment_method')
                ->get(),
        ];

        return $stats;
    }

    /**
     * Get daily collection report
     */
    public function getDailyCollection($date)
    {
        $payments = FeePayment::whereDate('payment_date', $date)
            ->with(['voucher.feeType', 'studentEnrollment.student', 'receivedBy'])
            ->orderBy('created_at')
            ->get();

        return [
            'date'       => $date,
            'total'      => $payments->sum('paid_amount'),
            'count'      => $payments->count(),
            'payments'   => $payments,
            'by_method'  => $payments->groupBy('payment_method')->map(fn($items) => $items->sum('paid_amount')),
        ];
    }
}
