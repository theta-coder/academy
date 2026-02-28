<?php

namespace App\Http\Controllers;

use App\Models\FeeFineRule;
use App\Models\Branch;
use App\Models\FeeType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeFineRuleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileRules($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesRules($request);
        }

        return Inertia::render('FeeFineRules/Index');
    }

    public function create()
    {
        $branches = Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get();
        $feeTypes = FeeType::select('id', 'fee_name')->orderBy('fee_name')->get();

        return Inertia::render('FeeFineRules/Create', [
            'branches' => $branches,
            'feeTypes' => $feeTypes,
        ]);
    }

    public function edit(FeeFineRule $feeFineRule)
    {
        $branches = Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get();
        $feeTypes = FeeType::select('id', 'fee_name')->orderBy('fee_name')->get();

        return Inertia::render('FeeFineRules/Edit', [
            'fineRule' => [
                'id'                      => $feeFineRule->id,
                'branch_id'               => $feeFineRule->branch_id,
                'fee_type_id'             => $feeFineRule->fee_type_id,
                'applies_to_all_fee_types'=> $feeFineRule->applies_to_all_fee_types,
                'days_after_due'          => $feeFineRule->days_after_due,
                'fine_type'               => $feeFineRule->fine_type,
                'fine_value'              => $feeFineRule->fine_value,
                'max_fine'                => $feeFineRule->max_fine,
                'description'             => $feeFineRule->description,
                'is_active'               => $feeFineRule->is_active,
            ],
            'branches' => $branches,
            'feeTypes' => $feeTypes,
        ]);
    }

    private function getMobileRules(Request $request)
    {
        $query = FeeFineRule::with(['branch', 'feeType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('fine_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->get('per_page', 10);
        $rules = $query->latest()->paginate($perPage);

        return response()->json($rules);
    }

    private function getDataTablesRules(Request $request)
    {
        $query = FeeFineRule::with(['branch', 'feeType']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('fine_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'branch_id', 'fee_type_id', 'days_after_due', 'fine_value', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $rules  = $query->skip($start)->take($length)->get();

        $data = $rules->map(function ($rule, $index) use ($start) {
            return [
                'DT_RowIndex'    => $start + $index + 1,
                'id'             => $rule->id,
                'branch_name'    => $rule->branch?->branch_name ?? 'All',
                'fee_type'       => $rule->applies_to_all_fee_types ? 'All Fee Types' : ($rule->feeType?->fee_name ?? '-'),
                'days_after_due' => $rule->days_after_due . ' days',
                'fine_type'      => ucfirst($rule->fine_type),
                'fine_value'     => $rule->fine_type === 'percentage'
                    ? $rule->fine_value . '%'
                    : number_format($rule->fine_value, 2),
                'max_fine'       => $rule->max_fine ? number_format($rule->max_fine, 2) : '-',
                'is_active'      => $rule->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editRule(' . json_encode(['id' => $rule->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteRule(' . $rule->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'branch_id'                => 'nullable|exists:branches,id',
            'fee_type_id'              => 'nullable|exists:fee_types,id',
            'applies_to_all_fee_types' => 'boolean',
            'days_after_due'           => 'required|integer|min:1',
            'fine_type'                => 'required|string|in:fixed,percentage,daily_fixed,daily_percentage',
            'fine_value'               => 'required|numeric|min:0',
            'max_fine'                 => 'nullable|numeric|min:0',
            'description'              => 'nullable|string',
            'is_active'                => 'boolean',
        ]);

        FeeFineRule::create($validated);

        return redirect()->route('fee-fine-rules.index')
            ->with('success', 'Fee fine rule created successfully!');
    }

    public function update(Request $request, FeeFineRule $feeFineRule)
    {
        $validated = $request->validate([
            'branch_id'                => 'nullable|exists:branches,id',
            'fee_type_id'              => 'nullable|exists:fee_types,id',
            'applies_to_all_fee_types' => 'boolean',
            'days_after_due'           => 'required|integer|min:1',
            'fine_type'                => 'required|string|in:fixed,percentage,daily_fixed,daily_percentage',
            'fine_value'               => 'required|numeric|min:0',
            'max_fine'                 => 'nullable|numeric|min:0',
            'description'              => 'nullable|string',
            'is_active'                => 'boolean',
        ]);

        $feeFineRule->update($validated);

        return redirect()->route('fee-fine-rules.index')
            ->with('success', 'Fee fine rule updated successfully!');
    }

    public function destroy(FeeFineRule $feeFineRule)
    {
        if ($feeFineRule->feeVoucherFines()->count() > 0) {
            return back()->with('error', 'Cannot delete fine rule with existing voucher fines!');
        }

        $feeFineRule->delete();

        return back()->with('success', 'Fee fine rule deleted successfully!');
    }

    public function dropdown()
    {
        $rules = FeeFineRule::active()
            ->with('feeType:id,fee_name')
            ->select('id', 'fee_type_id', 'fine_type', 'fine_value', 'days_after_due')
            ->get();

        return response()->json($rules);
    }
}
