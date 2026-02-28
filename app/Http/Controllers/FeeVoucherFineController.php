<?php

namespace App\Http\Controllers;

use App\Models\FeeVoucherFine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeVoucherFineController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileFines($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesFines($request);
        }

        return Inertia::render('FeeVoucherFines/Index');
    }

    public function create()
    {
        return Inertia::render('FeeVoucherFines/Create', $this->getFormData());
    }

    public function edit(FeeVoucherFine $feeVoucherFine)
    {
        $feeVoucherFine->load(['voucher', 'fineRule', 'appliedBy']);

        return Inertia::render('FeeVoucherFines/Edit', array_merge(
            $this->getFormData(),
            [
                'fine' => [
                    'id'                => $feeVoucherFine->id,
                    'voucher_id'        => $feeVoucherFine->voucher_id,
                    'fine_rule_id'      => $feeVoucherFine->fine_rule_id,
                    'days_overdue'      => $feeVoucherFine->days_overdue,
                    'fine_type'         => $feeVoucherFine->fine_type,
                    'fine_value'        => $feeVoucherFine->fine_value,
                    'calculated_amount' => $feeVoucherFine->calculated_amount,
                    'applied_on'        => $feeVoucherFine->applied_on?->format('Y-m-d'),
                    'is_waived'         => $feeVoucherFine->is_waived,
                    'notes'             => $feeVoucherFine->notes,
                ]
            ]
        ));
    }

    private function getFormData(): array
    {
        return [
            'vouchers' => \App\Models\FeeVoucher::select('id', 'voucher_no', 'net_amount', 'status')
                ->orderBy('id', 'desc')
                ->get(),
            'fineRules' => \App\Models\FeeFineRule::select('id', 'description', 'fine_type', 'fine_value', 'days_after_due')
                ->where('is_active', true)
                ->orderBy('description')
                ->get(),
        ];
    }

    private function getMobileFines(Request $request)
    {
        $query = FeeVoucherFine::with(['voucher', 'fineRule', 'appliedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('calculated_amount', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $fines = $query->latest()->paginate($perPage);

        return response()->json($fines);
    }

    private function getDataTablesFines(Request $request)
    {
        $query = FeeVoucherFine::with(['voucher', 'fineRule', 'appliedBy']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('calculated_amount', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'voucher_id', 'days_overdue', 'calculated_amount', 'applied_on'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $fines  = $query->skip($start)->take($length)->get();

        $data = $fines->map(function ($fine, $index) use ($start) {
            return [
                'DT_RowIndex'       => $start + $index + 1,
                'id'                => $fine->id,
                'voucher_no'        => $fine->voucher?->voucher_no ?? '-',
                'days_overdue'      => $fine->days_overdue . ' days',
                'fine_type'         => ucfirst($fine->fine_type),
                'fine_value'        => $fine->fine_type === 'percentage' ? $fine->fine_value . '%' : number_format($fine->fine_value, 2),
                'calculated_amount' => number_format($fine->calculated_amount, 2),
                'applied_on'        => $fine->applied_on?->format('d M, Y') ?? '-',
                'applied_by'        => $fine->appliedBy?->name ?? '-',
                'is_waived'         => $fine->is_waived
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Waived</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Applied</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editFine(' . json_encode(['id' => $fine->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteFine(' . $fine->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'voucher_id'        => 'required|exists:fee_vouchers,id',
            'fine_rule_id'      => 'nullable|exists:fee_fine_rules,id',
            'days_overdue'      => 'required|integer|min:0',
            'fine_type'         => 'required|string',
            'fine_value'        => 'required|numeric|min:0',
            'calculated_amount' => 'required|numeric|min:0',
            'applied_on'        => 'nullable|date',
            'is_waived'         => 'boolean',
            'notes'             => 'nullable|string',
        ]);

        $validated['applied_by'] = auth()->id();
        $validated['applied_on'] = $validated['applied_on'] ?? now();

        FeeVoucherFine::create($validated);

        return redirect()->route('fee-voucher-fines.index')
            ->with('success', 'Voucher fine created successfully!');
    }

    public function update(Request $request, FeeVoucherFine $feeVoucherFine)
    {
        $validated = $request->validate([
            'voucher_id'        => 'required|exists:fee_vouchers,id',
            'fine_rule_id'      => 'nullable|exists:fee_fine_rules,id',
            'days_overdue'      => 'required|integer|min:0',
            'fine_type'         => 'required|string',
            'fine_value'        => 'required|numeric|min:0',
            'calculated_amount' => 'required|numeric|min:0',
            'applied_on'        => 'nullable|date',
            'is_waived'         => 'boolean',
            'notes'             => 'nullable|string',
        ]);

        $feeVoucherFine->update($validated);

        return redirect()->route('fee-voucher-fines.index')
            ->with('success', 'Voucher fine updated successfully!');
    }

    public function destroy(FeeVoucherFine $feeVoucherFine)
    {
        $feeVoucherFine->delete();

        return back()->with('success', 'Voucher fine deleted successfully!');
    }
}
