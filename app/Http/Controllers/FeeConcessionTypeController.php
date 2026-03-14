<?php

namespace App\Http\Controllers;

use App\Models\FeeConcessionType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeConcessionTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileTypes($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesTypes($request);
        }

        return Inertia::render('FeeConcessionTypes/Index');
    }

    public function create()
    {
        return Inertia::render('FeeConcessionTypes/Create');
    }

    public function show(FeeConcessionType $feeConcessionType)
    {
        return Inertia::render('FeeConcessionTypes/Show', [
            'concessionType' => $feeConcessionType
        ]);
    }

    public function edit(FeeConcessionType $feeConcessionType)
    {
        return Inertia::render('FeeConcessionTypes/Edit', [
            'concessionType' => $feeConcessionType
        ]);
    }

    private function getMobileTypes(Request $request)
    {
        $query = FeeConcessionType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('concession_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('discount_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->get('per_page', 10);
        $types = $query->latest()->paginate($perPage);

        return response()->json($types);
    }

    private function getDataTablesTypes(Request $request)
    {
        $query = FeeConcessionType::query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('concession_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('discount_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir    = $request->input('order.0.dir', 'asc');
        $columns     = ['id', 'concession_name', 'discount_type', 'default_discount_value', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $types  = $query->skip($start)->take($length)->get();

        $data = $types->map(function ($type, $index) use ($start) {
            return [
                'DT_RowIndex'            => $start + $index + 1,
                'id'                     => $type->id,
                'concession_name'        => $type->concession_name,
                'discount_type'          => ucfirst($type->discount_type),
                'default_discount_value' => $type->discount_type === 'percentage'
                    ? $type->default_discount_value . '%'
                    : number_format($type->default_discount_value, 2),
                'applies_to'             => ucfirst($type->applies_to ?? 'all'),
                'is_active'              => $type->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editConcessionType(' . json_encode(['id' => $type->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteConcessionType(' . $type->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'concession_name'        => 'required|string|max:255',
            'discount_type'          => 'required|string|in:percentage,fixed',
            'default_discount_value' => 'required|numeric|min:0',
            'applies_to'             => 'nullable|string|max:100',
            'description'            => 'nullable|string',
            'is_active'              => 'boolean',
        ]);

        FeeConcessionType::create($validated);

        return redirect()->route('fee-concession-types.index')
            ->with('success', 'Fee concession type created successfully!');
    }

    public function update(Request $request, FeeConcessionType $feeConcessionType)
    {
        $validated = $request->validate([
            'concession_name'        => 'required|string|max:255',
            'discount_type'          => 'required|string|in:percentage,fixed',
            'default_discount_value' => 'required|numeric|min:0',
            'applies_to'             => 'nullable|string|max:100',
            'description'            => 'nullable|string',
            'is_active'              => 'boolean',
        ]);

        $feeConcessionType->update($validated);

        return redirect()->route('fee-concession-types.index')
            ->with('success', 'Fee concession type updated successfully!');
    }

    public function destroy(FeeConcessionType $feeConcessionType)
    {
        if ($feeConcessionType->studentFeeConcessions()->count() > 0) {
            return back()->with('error', 'Cannot delete concession type with student concessions!');
        }

        $feeConcessionType->delete();

        return back()->with('success', 'Fee concession type deleted successfully!');
    }

    public function dropdown()
    {
        $types = FeeConcessionType::active()
            ->select('id', 'concession_name', 'discount_type', 'default_discount_value')
            ->orderBy('concession_name')
            ->get();

        return response()->json($types);
    }
}
