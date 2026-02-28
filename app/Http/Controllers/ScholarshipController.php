<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\FeeType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScholarshipController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileScholarships($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesScholarships($request); }
        return Inertia::render('Scholarships/Index');
    }

    public function create()
    {
        return Inertia::render('Scholarships/Create', [
            'feeTypes' => FeeType::select('id', 'fee_name')->orderBy('fee_name')->get(),
        ]);
    }

    public function edit(Scholarship $scholarship)
    {
        return Inertia::render('Scholarships/Edit', [
            'scholarship' => [
                'id' => $scholarship->id, 'scholarship_name' => $scholarship->scholarship_name,
                'applicable_fee_type_id' => $scholarship->applicable_fee_type_id,
                'discount_type' => $scholarship->discount_type, 'discount_value' => $scholarship->discount_value,
                'applies_to' => $scholarship->applies_to, 'criteria' => $scholarship->criteria,
                'max_recipients' => $scholarship->max_recipients, 'is_renewable' => $scholarship->is_renewable,
                'description' => $scholarship->description, 'is_active' => $scholarship->is_active,
            ],
            'feeTypes' => FeeType::select('id', 'fee_name')->orderBy('fee_name')->get(),
        ]);
    }

    private function getMobileScholarships(Request $request)
    {
        $query = Scholarship::with('feeType');
        if ($request->filled('search')) { $query->where('scholarship_name', 'like', "%{$request->search}%"); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesScholarships(Request $request)
    {
        $query = Scholarship::with('feeType');
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('scholarship_name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"); }); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }

        $totalData = $query->count();
        $columns = ['id', 'scholarship_name', 'discount_type', 'discount_value', 'max_recipients', 'is_renewable', 'is_active'];
        $orderColumn = $request->input('order.0.column', 1); $orderDir = $request->input('order.0.dir', 'asc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $scholarships = $query->skip($start)->take($length)->get();

        $data = $scholarships->map(function ($s, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $s->id,
                'scholarship_name' => $s->scholarship_name,
                'fee_type' => $s->feeType?->fee_name ?? 'All',
                'discount' => $s->discount_type === 'percentage' ? $s->discount_value . '%' : 'Rs. ' . number_format($s->discount_value, 2),
                'max_recipients' => $s->max_recipients ?? '<span class="text-gray-400">—</span>',
                'is_renewable' => $s->is_renewable
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Yes</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">No</span>',
                'is_active' => $s->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'editScholarship(' . json_encode(['id' => $s->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="deleteScholarship(' . $s->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'scholarship_name' => 'required|string|max:255', 'applicable_fee_type_id' => 'nullable|exists:fee_types,id',
            'discount_type' => 'required|string|in:percentage,fixed', 'discount_value' => 'required|numeric|min:0',
            'applies_to' => 'nullable|string|in:all_fees,specific_fee_type,tuition_only',
            'criteria' => 'nullable|string|max:255', 'max_recipients' => 'nullable|integer|min:1',
            'is_renewable' => 'boolean', 'description' => 'nullable|string', 'is_active' => 'boolean',
        ]);
        $validated['created_by'] = auth()->id();
        Scholarship::create($validated);
        return redirect()->route('scholarships.index')->with('success', 'Scholarship created successfully!');
    }

    public function update(Request $request, Scholarship $scholarship)
    {
        $validated = $request->validate([
            'scholarship_name' => 'required|string|max:255', 'applicable_fee_type_id' => 'nullable|exists:fee_types,id',
            'discount_type' => 'required|string|in:percentage,fixed', 'discount_value' => 'required|numeric|min:0',
            'applies_to' => 'nullable|string|in:all_fees,specific_fee_type,tuition_only',
            'criteria' => 'nullable|string|max:255', 'max_recipients' => 'nullable|integer|min:1',
            'is_renewable' => 'boolean', 'description' => 'nullable|string', 'is_active' => 'boolean',
        ]);
        $scholarship->update($validated);
        return redirect()->route('scholarships.index')->with('success', 'Scholarship updated successfully!');
    }

    public function destroy(Scholarship $scholarship)
    {
        if ($scholarship->studentScholarships()->count() > 0) { return back()->with('error', 'Cannot delete scholarship with assigned students!'); }
        $scholarship->delete();
        return back()->with('success', 'Scholarship deleted successfully!');
    }

    public function dropdown()
    {
        return response()->json(Scholarship::active()->with('feeType:id,fee_name')->select('id', 'scholarship_name', 'applicable_fee_type_id', 'discount_type', 'discount_value')->orderBy('scholarship_name')->get());
    }
}
