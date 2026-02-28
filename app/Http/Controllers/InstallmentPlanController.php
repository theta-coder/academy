<?php

namespace App\Http\Controllers;

use App\Models\InstallmentPlan;
use App\Models\FeeType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstallmentPlanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobilePlans($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesPlans($request);
        }

        return Inertia::render('InstallmentPlans/Index');
    }

    public function create()
    {
        $feeTypes = FeeType::select('id', 'fee_name')->orderBy('fee_name')->get();

        return Inertia::render('InstallmentPlans/Create', [
            'feeTypes' => $feeTypes,
        ]);
    }

    public function edit(InstallmentPlan $installmentPlan)
    {
        $feeTypes = FeeType::select('id', 'fee_name')->orderBy('fee_name')->get();

        return Inertia::render('InstallmentPlans/Edit', [
            'plan' => [
                'id'                     => $installmentPlan->id,
                'plan_name'              => $installmentPlan->plan_name,
                'total_installments'     => $installmentPlan->total_installments,
                'applicable_fee_type_id' => $installmentPlan->applicable_fee_type_id,
                'description'            => $installmentPlan->description,
                'is_active'              => $installmentPlan->is_active,
            ],
            'feeTypes' => $feeTypes,
        ]);
    }

    private function getMobilePlans(Request $request)
    {
        $query = InstallmentPlan::with('feeType');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plan_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->get('per_page', 10);
        $plans = $query->latest()->paginate($perPage);

        return response()->json($plans);
    }

    private function getDataTablesPlans(Request $request)
    {
        $query = InstallmentPlan::with('feeType');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('plan_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir    = $request->input('order.0.dir', 'asc');
        $columns     = ['id', 'plan_name', 'total_installments', 'applicable_fee_type_id', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $plans  = $query->skip($start)->take($length)->get();

        $data = $plans->map(function ($plan, $index) use ($start) {
            return [
                'DT_RowIndex'        => $start + $index + 1,
                'id'                 => $plan->id,
                'plan_name'          => $plan->plan_name,
                'total_installments' => $plan->total_installments,
                'fee_type'           => $plan->feeType?->fee_name ?? 'All',
                'is_active'          => $plan->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editPlan(' . json_encode(['id' => $plan->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <button onclick="deletePlan(' . $plan->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
            'plan_name'              => 'required|string|max:255',
            'total_installments'     => 'required|integer|min:2',
            'applicable_fee_type_id' => 'nullable|exists:fee_types,id',
            'description'            => 'nullable|string',
            'is_active'              => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        InstallmentPlan::create($validated);

        return redirect()->route('installment-plans.index')
            ->with('success', 'Installment plan created successfully!');
    }

    public function update(Request $request, InstallmentPlan $installmentPlan)
    {
        $validated = $request->validate([
            'plan_name'              => 'required|string|max:255',
            'total_installments'     => 'required|integer|min:2',
            'applicable_fee_type_id' => 'nullable|exists:fee_types,id',
            'description'            => 'nullable|string',
            'is_active'              => 'boolean',
        ]);

        $installmentPlan->update($validated);

        return redirect()->route('installment-plans.index')
            ->with('success', 'Installment plan updated successfully!');
    }

    public function destroy(InstallmentPlan $installmentPlan)
    {
        if ($installmentPlan->studentAssignments()->count() > 0) {
            return back()->with('error', 'Cannot delete plan with student assignments!');
        }

        $installmentPlan->delete();

        return back()->with('success', 'Installment plan deleted successfully!');
    }

    public function dropdown()
    {
        $plans = InstallmentPlan::active()
            ->with('feeType:id,fee_name')
            ->select('id', 'plan_name', 'total_installments', 'applicable_fee_type_id')
            ->orderBy('plan_name')
            ->get();

        return response()->json($plans);
    }
}
