<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\FeeVoucher;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\FeePaymentService;

class FeePaymentController extends Controller
{
    protected $feePaymentService;

    public function __construct(FeePaymentService $feePaymentService)
    {
        $this->feePaymentService = $feePaymentService;
    }

    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobilePayments($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesPayments($request);
        }

        return Inertia::render('FeePayments/Index');
    }

    public function create()
    {
        return Inertia::render('FeePayments/Create', $this->getFormData());
    }

    public function edit(FeePayment $feePayment)
    {
        $feePayment->load(['voucher.feeType', 'studentEnrollment.student', 'receivedBy']);

        return Inertia::render('FeePayments/Edit', array_merge(
            $this->getFormData(),
            [
                'payment' => [
                    'id'                    => $feePayment->id,
                    'receipt_no'            => $feePayment->receipt_no,
                    'voucher_id'            => $feePayment->voucher_id,
                    'student_enrollment_id' => $feePayment->student_enrollment_id,
                    'paid_amount'           => $feePayment->paid_amount,
                    'payment_date'          => $feePayment->payment_date?->format('Y-m-d'),
                    'payment_method'        => $feePayment->payment_method,
                    'bank_name'             => $feePayment->bank_name,
                    'transaction_ref'       => $feePayment->transaction_ref,
                    'is_advance'            => $feePayment->is_advance,
                    'notes'                 => $feePayment->notes,
                ]
            ]
        ));
    }

    public function show(FeePayment $feePayment)
    {
        $feePayment->load([
            'voucher.feeType',
            'studentEnrollment.student',
            'receivedBy',
            'refund',
        ]);

        return Inertia::render('FeePayments/Show', [
            'payment' => $feePayment
        ]);
    }

    private function getMobilePayments(Request $request)
    {
        $query = FeePayment::with(['voucher.feeType', 'studentEnrollment.student', 'receivedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('transaction_ref', 'like', "%{$search}%")
                  ->orWhereHas('studentEnrollment.student', function ($sq) use ($search) {
                      $sq->where('student_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('payment_date', [$request->start_date, $request->end_date]);
        }

        $perPage = $request->get('per_page', 10);
        $payments = $query->latest('payment_date')->paginate($perPage);

        return response()->json($payments);
    }

    private function getDataTablesPayments(Request $request)
    {
        $query = FeePayment::with(['voucher.feeType', 'studentEnrollment.student', 'receivedBy']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('transaction_ref', 'like', "%{$search}%")
                  ->orWhereHas('studentEnrollment.student', function ($sq) use ($search) {
                      $sq->where('student_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('payment_date', [$request->start_date, $request->end_date]);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'receipt_no', 'student_enrollment_id', 'paid_amount', 'payment_date', 'payment_method'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start    = $request->input('start', 0);
        $length   = $request->input('length', 10);
        $payments = $query->skip($start)->take($length)->get();

        $data = $payments->map(function ($payment, $index) use ($start) {
            return [
                'DT_RowIndex'    => $start + $index + 1,
                'id'             => $payment->id,
                'receipt_no'     => $payment->receipt_no,
                'student_name'   => $payment->studentEnrollment?->student?->student_name ?? '-',
                'fee_type'       => $payment->voucher?->feeType?->fee_name ?? '-',
                'voucher_no'     => $payment->voucher?->voucher_no ?? '-',
                'payment_date'   => $payment->payment_date?->format('d M, Y') ?? '-',
                'paid_amount'    => 'Rs. ' . number_format((float)($payment->paid_amount ?? 0), 2),
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
                'received_by'    => $payment->receivedBy?->name ?? '-',
                'is_advance'     => $payment->is_advance
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Advance</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Regular</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'viewPayment(' . json_encode(['id' => $payment->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View
                        </button>
                        <button onclick=\'editPayment(' . json_encode(['id' => $payment->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-yellow-600 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deletePayment(' . $payment->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                '
            ];
        });

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_id'            => 'required|exists:fee_vouchers,id',
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'paid_amount'           => 'required|numeric|min:0.01',
            'payment_date'          => 'required|date',
            'payment_method'        => 'required|in:cash,bank_transfer,cheque,online,jazzcash,easypaisa,sadapay,raast,advance_adjusted',
            'bank_name'             => 'nullable|string|max:100',
            'transaction_ref'       => 'nullable|string|max:100',
            'is_advance'            => 'boolean',
            'notes'                 => 'nullable|string',
        ]);

        $result = $this->feePaymentService->processPayment($validated);

        if ($result['success']) {
            return redirect()
                ->route('fee-payments.index')
                ->with('success', $result['message']);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $result['message']);
    }

    public function update(Request $request, FeePayment $feePayment)
    {
        $validated = $request->validate([
            'voucher_id'            => 'required|exists:fee_vouchers,id',
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'paid_amount'           => 'required|numeric|min:0.01',
            'payment_date'          => 'required|date',
            'payment_method'        => 'required|in:cash,bank_transfer,cheque,online',
            'bank_name'             => 'nullable|string|max:100',
            'transaction_ref'       => 'nullable|string|max:100',
            'is_advance'            => 'boolean',
            'notes'                 => 'nullable|string',
        ]);

        $feePayment->update($validated);

        return redirect()->route('fee-payments.index')
            ->with('success', 'Payment updated successfully!');
    }

    public function destroy(FeePayment $feePayment)
    {
        // Reverse the voucher amounts before deleting
        if (!$feePayment->is_advance && $feePayment->voucher) {
            $voucher = $feePayment->voucher;
            $voucher->paid_amount -= $feePayment->paid_amount;
            $voucher->remaining_amount = $voucher->net_amount - $voucher->paid_amount;

            if ($voucher->paid_amount <= 0) {
                $voucher->status = 'pending';
                $voucher->paid_amount = 0;
            } elseif ($voucher->paid_amount < $voucher->net_amount) {
                $voucher->status = 'partial';
            }

            $voucher->save();
        }

        $feePayment->delete();

        return back()->with('success', 'Payment deleted successfully!');
    }

    private function generateReceiptNumber()
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

    private function getFormData(): array
    {
        return [
            'enrollments' => \App\Models\StudentEnrollment::with('student:id,student_name,admission_no')
                ->select('id', 'student_id')
                ->where('status', 'active')
                ->get(),
            'vouchers' => \App\Models\FeeVoucher::select('id', 'voucher_no', 'net_amount', 'status')
                ->orderBy('id', 'desc')
                ->get(),
        ];
    }

    public function dropdown(Request $request)
    {
        $query = FeePayment::with(['voucher.feeType', 'studentEnrollment.student'])
            ->select('id', 'receipt_no', 'voucher_id', 'student_enrollment_id', 'paid_amount', 'payment_date');

        if ($request->filled('student_enrollment_id')) {
            $query->where('student_enrollment_id', $request->student_enrollment_id);
        }

        if ($request->filled('voucher_id')) {
            $query->where('voucher_id', $request->voucher_id);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        return response()->json($payments);
    }
}