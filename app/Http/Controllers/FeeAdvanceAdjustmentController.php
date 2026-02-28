<?php

namespace App\Http\Controllers;

use App\Models\FeeAdvanceAdjustment;
use App\Models\StudentEnrollment;
use App\Models\FeePayment;
use App\Models\FeeVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeAdvanceAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileAdjustments($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesAdjustments($request);
        }

        return Inertia::render('FeeAdvanceAdjustments/Index');
    }

    public function create()
    {
        return Inertia::render('FeeAdvanceAdjustments/Create');
    }

    public function edit(FeeAdvanceAdjustment $feeAdvanceAdjustment)
    {
        $feeAdvanceAdjustment->load(['studentEnrollment.student', 'fromPayment', 'toVoucher']);

        return Inertia::render('FeeAdvanceAdjustments/Edit', [
            'adjustment' => [
                'id'                    => $feeAdvanceAdjustment->id,
                'student_enrollment_id' => $feeAdvanceAdjustment->student_enrollment_id,
                'from_payment_id'       => $feeAdvanceAdjustment->from_payment_id,
                'to_voucher_id'         => $feeAdvanceAdjustment->to_voucher_id,
                'adjusted_amount'       => $feeAdvanceAdjustment->adjusted_amount,
                'adjusted_at'           => $feeAdvanceAdjustment->adjusted_at?->format('Y-m-d'),
                'notes'                 => $feeAdvanceAdjustment->notes,
            ]
        ]);
    }

    private function getMobileAdjustments(Request $request)
    {
        $query = FeeAdvanceAdjustment::with(['studentEnrollment.student', 'fromPayment', 'toVoucher', 'adjustedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('adjusted_amount', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('studentEnrollment.student', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 10);
        $adjustments = $query->latest()->paginate($perPage);

        return response()->json($adjustments);
    }

    private function getDataTablesAdjustments(Request $request)
    {
        $query = FeeAdvanceAdjustment::with(['studentEnrollment.student', 'fromPayment', 'toVoucher', 'adjustedBy']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('adjusted_amount', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'student_enrollment_id', 'adjusted_amount', 'adjusted_at'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start       = $request->input('start', 0);
        $length      = $request->input('length', 10);
        $adjustments = $query->skip($start)->take($length)->get();

        $data = $adjustments->map(function ($adj, $index) use ($start) {
            return [
                'DT_RowIndex'      => $start + $index + 1,
                'id'               => $adj->id,
                'student_name'     => $adj->studentEnrollment?->student?->first_name ?? '-',
                'from_payment'     => $adj->fromPayment?->id ?? '-',
                'to_voucher'       => $adj->toVoucher?->voucher_no ?? '-',
                'adjusted_amount'  => number_format($adj->adjusted_amount, 2),
                'adjusted_at'      => $adj->adjusted_at?->format('d M, Y') ?? '-',
                'adjusted_by'      => $adj->adjustedBy?->name ?? '-',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editAdjustment(' . json_encode(['id' => $adj->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteAdjustment(' . $adj->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'from_payment_id'       => 'required|exists:fee_payments,id',
            'to_voucher_id'         => 'required|exists:fee_vouchers,id',
            'adjusted_amount'       => 'required|numeric|min:0.01',
            'adjusted_at'           => 'nullable|date',
            'notes'                 => 'nullable|string',
        ]);

        $validated['adjusted_by'] = auth()->id();
        $validated['adjusted_at'] = $validated['adjusted_at'] ?? now();

        FeeAdvanceAdjustment::create($validated);

        return redirect()->route('fee-advance-adjustments.index')
            ->with('success', 'Advance adjustment created successfully!');
    }

    public function update(Request $request, FeeAdvanceAdjustment $feeAdvanceAdjustment)
    {
        $validated = $request->validate([
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'from_payment_id'       => 'required|exists:fee_payments,id',
            'to_voucher_id'         => 'required|exists:fee_vouchers,id',
            'adjusted_amount'       => 'required|numeric|min:0.01',
            'adjusted_at'           => 'nullable|date',
            'notes'                 => 'nullable|string',
        ]);

        $feeAdvanceAdjustment->update($validated);

        return redirect()->route('fee-advance-adjustments.index')
            ->with('success', 'Advance adjustment updated successfully!');
    }

    public function destroy(FeeAdvanceAdjustment $feeAdvanceAdjustment)
    {
        $feeAdvanceAdjustment->delete();

        return back()->with('success', 'Advance adjustment deleted successfully!');
    }
}
