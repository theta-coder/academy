<?php

namespace App\Http\Controllers;

use App\Models\SiblingDiscountRule;
use App\Models\FeeType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiblingDiscountRuleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileRules($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesRules($request); }
        return Inertia::render('SiblingDiscountRules/Index');
    }

    public function create()
    {
        return Inertia::render('SiblingDiscountRules/Create', [
            'feeTypes' => FeeType::select('id', 'fee_name')->orderBy('fee_name')->get(),
        ]);
    }

    public function edit(SiblingDiscountRule $siblingDiscountRule)
    {
        return Inertia::render('SiblingDiscountRules/Edit', [
            'rule' => $siblingDiscountRule,
            'feeTypes' => FeeType::select('id', 'fee_name')->orderBy('fee_name')->get(),
        ]);
    }

    private function getMobileRules(Request $request)
    {
        $query = SiblingDiscountRule::with('feeType');
        if ($request->filled('search')) { $query->where('description', 'like', "%{$request->search}%"); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }
        return response()->json($query->orderBy('child_number')->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesRules(Request $request)
    {
        $query = SiblingDiscountRule::with('feeType');
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('description', 'like', "%{$search}%")->orWhere('child_number', 'like', "%{$search}%"); }); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }

        $totalData = $query->count();
        $columns = ['id', 'child_number', 'discount_type', 'discount_value', 'is_active'];
        $orderColumn = $request->input('order.0.column', 1); $orderDir = $request->input('order.0.dir', 'asc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $rules = $query->skip($start)->take($length)->get();

        $data = $rules->map(function ($rule, $index) use ($start) {
            $ordinal = match($rule->child_number) { 1 => '1st', 2 => '2nd', 3 => '3rd', default => $rule->child_number . 'th' };
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $rule->id,
                'child_number' => $ordinal . ' Child',
                'fee_type' => $rule->feeType?->fee_name ?? 'All',
                'discount' => $rule->discount_type === 'percentage' ? $rule->discount_value . '%' : number_format($rule->discount_value, 2),
                'is_active' => $rule->is_active ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>' : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'window.editSiblingRule(' . json_encode(['id' => $rule->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="window.deleteSiblingRule(' . $rule->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'child_number' => 'required|integer|min:1|max:10',
                'discount_type' => 'required|string|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0|max:999999',
                'applies_to_fee_type_id' => 'nullable|exists:fee_types,id',
                'description' => 'nullable|string|max:500',
                'is_active' => 'boolean',
            ]);

            SiblingDiscountRule::create($validated);
            return redirect()->route('sibling-discount-rules.index')->with('success', 'Sibling discount rule created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, SiblingDiscountRule $siblingDiscountRule)
    {
        try {
            $validated = $request->validate([
                'child_number' => 'required|integer|min:1|max:10',
                'discount_type' => 'required|string|in:percentage,fixed',
                'discount_value' => 'required|numeric|min:0|max:999999',
                'applies_to_fee_type_id' => 'nullable|exists:fee_types,id',
                'description' => 'nullable|string|max:500',
                'is_active' => 'boolean',
            ]);

            $siblingDiscountRule->update($validated);
            return redirect()->route('sibling-discount-rules.index')->with('success', 'Sibling discount rule updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(SiblingDiscountRule $siblingDiscountRule)
    {
        $siblingDiscountRule->delete();
        return back()->with('success', 'Sibling discount rule deleted successfully!');
    }
}
