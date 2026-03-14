<?php

namespace App\Services;

use App\Models\FeeVoucher;
use App\Models\StudentEnrollment;
use App\Models\FeeStructure;
use App\Models\StudentFeeConcession;
use App\Models\SiblingDiscountRule;
use App\Models\FeeFineRule;
use App\Models\PreviousYearBalance;
use App\Models\VoucherDiscountBreakdown;
use App\Models\StudentLedger;
use Illuminate\Support\Facades\DB;
use Exception;

class FeeGenerationService
{
    /**
     * Generate monthly vouchers for a specific branch and academic year
     */
    public function generateMonthlyVouchers($branchId, $academicYearId, $month, $year)
    {
        DB::beginTransaction();
        try {
            // Find all active students in this branch/year
            $students = StudentEnrollment::where('branch_id', $branchId)
                ->where('academic_year_id', $academicYearId)
                ->where('status', 'active')
                ->with(['student', 'classSection.branchClass.class', 'feeConcessions'])
                ->get();

            $generatedCount = 0;
            $skippedCount = 0;

            foreach ($students as $enrollment) {
                // Get active fee structures for this student's class
                $feeStructures = FeeStructure::active()
                    ->where('branch_id', $branchId)
                    ->where('class_id', $enrollment->classSection->branch_class_id ?? null)
                    ->where('academic_year_id', $academicYearId)
                    ->with('feeType')
                    ->get();

                foreach ($feeStructures as $structure) {
                    // Check if fee type is recurring for this month
                    if ($structure->feeType->is_recurring) {
                        $recurringMonths = $this->parseRecurringMonths($structure->feeType->recurring_months);
                        if (!in_array($month, $recurringMonths)) {
                            continue;
                        }
                    }

                    // Check if voucher already exists
                    $existingVoucher = FeeVoucher::where('student_enrollment_id', $enrollment->id)
                        ->where('fee_type_id', $structure->fee_type_id)
                        ->where('month', $month)
                        ->where('year', $year)
                        ->first();

                    if ($existingVoucher) {
                        $skippedCount++;
                        continue;
                    }

                    // Calculate amounts
                    $baseAmount = $structure->amount;
                    $discounts = $this->calculateDiscounts($enrollment, $structure->fee_type_id, $baseAmount);
                    $fine = $this->calculateApplicableFines($enrollment, $structure->fee_type_id, $month, $year);

                    $originalAmount = $baseAmount;
                    $discountAmount = $discounts['total_discount'];
                    $fineAmount = $fine;
                    $netAmount = $originalAmount - $discountAmount + $fineAmount;

                    // Create voucher
                    $voucher = FeeVoucher::create([
                        'voucher_no'            => $this->generateVoucherNo(),
                        'student_enrollment_id' => $enrollment->id,
                        'fee_type_id'           => $structure->fee_type_id,
                        'academic_year_id'      => $academicYearId,
                        'month'                 => $month,
                        'year'                  => $year,
                        'generated_for'         => $this->getMonthName($month) . ' ' . $year,
                        'original_amount'       => $originalAmount,
                        'discount_amount'       => $discountAmount,
                        'fine_amount'           => $fineAmount,
                        'net_amount'            => $netAmount,
                        'paid_amount'           => 0,
                        'remaining_amount'      => $netAmount,
                        'due_date'              => $this->calculateDueDate($year, $month, $structure->due_day),
                        'status'                => 'pending',
                        'generated_by'          => auth()->id() ?? 1,
                        'notes'                 => 'Auto-generated monthly fee voucher',
                    ]);

                    // Save discount breakdown
                    foreach ($discounts['breakdown'] as $discount) {
                        VoucherDiscountBreakdown::create([
                            'voucher_id'       => $voucher->id,
                            'discount_type'    => $discount['type'],
                            'discount_source'  => $discount['source'],
                            'discount_amount'  => $discount['amount'],
                            'description'      => $discount['description'],
                        ]);
                    }

                    // Create ledger entry (debit)
                    StudentLedger::create([
                        'student_enrollment_id' => $enrollment->id,
                        'transaction_type'      => 'debit',
                        'amount'                => $netAmount,
                        'description'           => 'Fee voucher generated: ' . $voucher->voucher_no,
                        'reference_type'        => 'voucher',
                        'reference_id'          => $voucher->id,
                        'balance_after'         => $this->calculateStudentBalance($enrollment->id),
                        'created_by'            => auth()->id() ?? 1,
                    ]);

                    $generatedCount++;
                }
            }

            DB::commit();
            return [
                'success' => true,
                'message' => "Successfully generated {$generatedCount} vouchers. Skipped {$skippedCount} existing vouchers.",
                'count'   => $generatedCount,
                'skipped' => $skippedCount,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error generating vouchers: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Recalculate a single voucher (used when edited or concession added later)
     */
    public function recalculateVoucher(FeeVoucher $voucher)
    {
        DB::beginTransaction();
        try {
            $feeStructure = FeeStructure::active()
                ->where('student_enrollment_id', $voucher->student_enrollment_id)
                ->where('fee_type_id', $voucher->fee_type_id)
                ->first();

            if (!$feeStructure) {
                return ['success' => false, 'message' => 'Fee structure not found'];
            }

            $baseAmount = $feeStructure->amount;
            $discounts = $this->calculateDiscounts(
                $voucher->studentEnrollment,
                $voucher->fee_type_id,
                $baseAmount
            );

            $voucher->original_amount = $baseAmount;
            $voucher->discount_amount = $discounts['total_discount'];
            $voucher->net_amount = $baseAmount - $discounts['total_discount'] + $voucher->fine_amount;
            $voucher->remaining_amount = $voucher->net_amount - $voucher->paid_amount;

            // Update status
            if ($voucher->remaining_amount <= 0) {
                $voucher->status = 'paid';
            } elseif ($voucher->paid_amount > 0) {
                $voucher->status = 'partial';
            } else {
                $voucher->status = 'pending';
            }

            $voucher->save();

            // Update discount breakdowns
            VoucherDiscountBreakdown::where('voucher_id', $voucher->id)->delete();
            foreach ($discounts['breakdown'] as $discount) {
                VoucherDiscountBreakdown::create([
                    'voucher_id'       => $voucher->id,
                    'discount_type'    => $discount['type'],
                    'discount_source'  => $discount['source'],
                    'discount_amount'  => $discount['amount'],
                    'description'      => $discount['description'],
                ]);
            }

            DB::commit();
            return ['success' => true, 'message' => 'Voucher recalculated successfully'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error recalculating voucher: ' . $e->getMessage()];
        }
    }

    /**
     * Calculate all applicable discounts for a student
     */
    private function calculateDiscounts($enrollment, $feeTypeId, $baseAmount)
    {
        $discounts = ['total_discount' => 0, 'breakdown' => []];

        // 1. Student Fee Concessions
        $concessions = StudentFeeConcession::active()
            ->where('student_enrollment_id', $enrollment->id)
            ->where(function ($q) use ($feeTypeId) {
                $q->where('fee_type_id', $feeTypeId)
                  ->orWhereNull('fee_type_id');
            })
            ->with('concessionType')
            ->get();

        foreach ($concessions as $concession) {
            $discountAmount = $concession->discount_type === 'percentage'
                ? ($baseAmount * $concession->discount_value) / 100
                : $concession->discount_value;

            $discounts['total_discount'] += $discountAmount;
            $discounts['breakdown'][] = [
                'type'        => 'concession',
                'source'      => 'student_fee_concession',
                'amount'      => $discountAmount,
                'description' => $concession->concessionType->concession_name ?? 'Fee Concession',
            ];
        }

        // 2. Sibling Discounts
        if ($enrollment->sibling_order && $enrollment->sibling_order > 1) {
            $siblingRules = SiblingDiscountRule::active()
                ->where('child_number', $enrollment->sibling_order)
                ->where(function ($q) use ($feeTypeId) {
                    $q->where('applies_to_fee_type_id', $feeTypeId)
                      ->orWhereNull('applies_to_fee_type_id');
                })
                ->get();

            foreach ($siblingRules as $rule) {
                $discountAmount = $rule->discount_type === 'percentage'
                    ? ($baseAmount * $rule->discount_value) / 100
                    : $rule->discount_value;

                $discounts['total_discount'] += $discountAmount;
                $discounts['breakdown'][] = [
                    'type'        => 'sibling_discount',
                    'source'      => 'sibling_discount_rule',
                    'amount'      => $discountAmount,
                    'description' => "Sibling Discount (Child #{$rule->child_number})",
                ];
            }
        }

        return $discounts;
    }

    /**
     * Calculate applicable fines for a voucher
     */
    private function calculateApplicableFines($enrollment, $feeTypeId, $month, $year)
    {
        // This would calculate fines based on FeeFineRule and due date
        // For now, return 0 as fines are typically applied after due date passes
        return 0;
    }

    /**
     * Calculate student's current balance from ledger
     */
    private function calculateStudentBalance($enrollmentId)
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
     * Generate unique voucher number
     */
    private function generateVoucherNo(): string
    {
        $latest = FeeVoucher::lockForUpdate()->latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        return 'VCH-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Parse recurring months string to array
     */
    private function parseRecurringMonths(?string $months): array
    {
        if (!$months) {
            return range(1, 12); // All months
        }

        return array_map('intval', explode(',', $months));
    }

    /**
     * Get month name from number
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        return $months[$month] ?? 'Unknown';
    }

    /**
     * Calculate due date based on year, month and due day
     */
    private function calculateDueDate(int $year, int $month, ?int $dueDay): string
    {
        $day = $dueDay ?? 10; // Default to 10th if not set

        // Handle months with fewer days
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = min($day, $daysInMonth);

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * Generate vouchers for all students in a specific class
     */
    public function generateForClass($branchId, $classId, $academicYearId, $month, $year)
    {
        DB::beginTransaction();
        try {
            $enrollments = StudentEnrollment::where('branch_id', $branchId)
                ->where('academic_year_id', $academicYearId)
                ->whereHas('classSection', function ($q) use ($classId) {
                    $q->where('branch_class_id', $classId);
                })
                ->where('status', 'active')
                ->get();

            $count = 0;
            foreach ($enrollments as $enrollment) {
                $result = $this->generateMonthlyVouchers(
                    $branchId,
                    $academicYearId,
                    $month,
                    $year
                );

                if ($result['success']) {
                    $count += $result['count'];
                }
            }

            DB::commit();
            return ['success' => true, 'count' => $count];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Apply fine to overdue vouchers
     */
    public function applyFinesToOverdueVouchers()
    {
        DB::beginTransaction();
        try {
            $overdueVouchers = FeeVoucher::whereIn('status', ['pending', 'partial'])
                ->where('due_date', '<', now())
                ->get();

            $appliedCount = 0;

            foreach ($overdueVouchers as $voucher) {
                $daysOverdue = now()->diffInDays($voucher->due_date, false);

                $fineRules = FeeFineRule::active()
                    ->where(function ($q) use ($voucher) {
                        $q->where('applies_to_all_fee_types', true)
                          ->orWhere('fee_type_id', $voucher->fee_type_id);
                    })
                    ->where('days_after_due', '<=', $daysOverdue)
                    ->get();

                foreach ($fineRules as $rule) {
                    $fineAmount = $rule->fine_type === 'percentage'
                        ? ($voucher->net_amount * $rule->fine_value) / 100
                        : $rule->fine_value;

                    // Apply max fine cap if set
                    if ($rule->max_fine && $fineAmount > $rule->max_fine) {
                        $fineAmount = $rule->max_fine;
                    }

                    $voucher->fine_amount += $fineAmount;
                    $voucher->net_amount += $fineAmount;
                    $voucher->remaining_amount += $fineAmount;
                    $voucher->save();

                    $appliedCount++;
                }
            }

            DB::commit();
            return ['success' => true, 'count' => $appliedCount];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
