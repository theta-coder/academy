<?php

namespace App\Http\Controllers;

use App\Models\FeeRefund;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeRefundController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileRefunds($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesRefunds($request);
        }

        return Inertia::render('FeeRefunds/Index');
    }

    public function create()
    {
        return Inertia::render('FeeRefunds/Create');
    }

    public function edit(FeeRefund $feeRefund)
    {
        $feeRefund->load(['studentEnrollment.student', 'payment', 'refundedBy']);

        return Inertia::render('FeeRefunds/Edit', [
            'refund' => [
                'id'                    => $feeRefund->id,
                'student_enrollment_id' => $feeRefund->student_enrollment_id,
                'payment_id'            => $feeRefund->payment_id,
                'refund_amount'         => $feeRefund->refund_amount,
                'refund_date'           => $feeRefund->refund_date?->format('Y-m-d'),
                'reason'                => $feeRefund->reason,
                'refund_method'         => $feeRefund->refund_method,
                'bank_details'          => $feeRefund->bank_details,
                'status'                => $feeRefund->status,
                'notes'                 => $feeRefund->notes,
            ]
        ]);
    }

    private function getMobileRefunds(Request $request)
    {
        $query = FeeRefund::with(['studentEnrollment.student', 'payment', 'refundedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('refund_amount', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $refunds = $query->latest()->paginate($perPage);

        return response()->json($refunds);
    }

    private function getDataTablesRefunds(Request $request)
    {
        $query = FeeRefund::with(['studentEnrollment.student', 'payment', 'refundedBy']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('refund_amount', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'student_enrollment_id', 'refund_amount', 'refund_date', 'status'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start   = $request->input('start', 0);
        $length  = $request->input('length', 10);
        $refunds = $query->skip($start)->take($length)->get();

        $data = $refunds->map(function ($refund, $index) use ($start) {
            $statusClass = match($refund->status) {
                'approved'  => 'bg-green-100 text-green-800',
                'pending'   => 'bg-yellow-100 text-yellow-800',
                'rejected'  => 'bg-red-100 text-red-800',
                default     => 'bg-gray-100 text-gray-800',
            };

            return [
                'DT_RowIndex'    => $start + $index + 1,
                'id'             => $refund->id,
                'student_name'   => $refund->studentEnrollment?->student?->first_name ?? '-',
                'payment_id'     => $refund->payment_id ?? '-',
                'refund_amount'  => number_format($refund->refund_amount, 2),
                'refund_date'    => $refund->refund_date?->format('d M, Y') ?? '-',
                'refund_method'  => ucfirst($refund->refund_method ?? '-'),
                'status'         => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($refund->status) . '</span>',
                'refunded_by'    => $refund->refundedBy?->name ?? '-',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editRefund(' . json_encode(['id' => $refund->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteRefund(' . $refund->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'payment_id'            => 'required|exists:fee_payments,id',
            'refund_amount'         => 'required|numeric|min:0.01',
            'refund_date'           => 'required|date',
            'reason'                => 'required|string',
            'refund_method'         => 'nullable|string|max:100',
            'bank_details'          => 'nullable|string',
            'status'                => 'nullable|string|in:pending,approved,rejected',
            'notes'                 => 'nullable|string',
        ]);

        $validated['refunded_by'] = auth()->id();

        FeeRefund::create($validated);

        return redirect()->route('fee-refunds.index')
            ->with('success', 'Fee refund created successfully!');
    }

    public function update(Request $request, FeeRefund $feeRefund)
    {
        $validated = $request->validate([
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'payment_id'            => 'required|exists:fee_payments,id',
            'refund_amount'         => 'required|numeric|min:0.01',
            'refund_date'           => 'required|date',
            'reason'                => 'required|string',
            'refund_method'         => 'nullable|string|max:100',
            'bank_details'          => 'nullable|string',
            'status'                => 'nullable|string|in:pending,approved,rejected',
            'notes'                 => 'nullable|string',
        ]);

        $feeRefund->update($validated);

        return redirect()->route('fee-refunds.index')
            ->with('success', 'Fee refund updated successfully!');
    }

    public function destroy(FeeRefund $feeRefund)
    {
        $feeRefund->delete();

        return back()->with('success', 'Fee refund deleted successfully!');
    }
}
