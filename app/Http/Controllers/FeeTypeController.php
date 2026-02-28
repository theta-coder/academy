<?php

namespace App\Http\Controllers;

use App\Models\FeeType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileFeeTypes($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesFeeTypes($request);
        }

        return Inertia::render('FeeTypes/Index');
    }

    public function create()
    {
        return Inertia::render('FeeTypes/Create');
    }

    public function edit(FeeType $feeType)
    {
        return Inertia::render('FeeTypes/Edit', [
            'feeType' => [
                'id'               => $feeType->id,
                'fee_name'         => $feeType->fee_name,
                'fee_category'     => $feeType->fee_category,
                'is_recurring'     => $feeType->is_recurring,
                'recurring_months' => $feeType->recurring_months,
                'description'      => $feeType->description,
                'display_order'    => $feeType->display_order,
                'is_active'        => $feeType->is_active,
            ],
        ]);
    }

    private function getMobileFeeTypes(Request $request)
    {
        $query = FeeType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fee_name', 'like', "%{$search}%")
                  ->orWhere('fee_category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('fee_category')) {
            $query->where('fee_category', $request->fee_category);
        }

        $perPage = $request->get('per_page', 10);
        return response()->json($query->orderBy('display_order')->paginate($perPage));
    }

    private function getDataTablesFeeTypes(Request $request)
    {
        $query = FeeType::query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('fee_name', 'like', "%{$search}%")
                  ->orWhere('fee_category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('fee_category')) {
            $query->where('fee_category', $request->fee_category);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'asc');
        $columns     = ['id', 'fee_name', 'fee_category', 'is_recurring', 'display_order', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start    = $request->input('start', 0);
        $length   = $request->input('length', 10);
        $feeTypes = $query->skip($start)->take($length)->get();

        $data = $feeTypes->map(function ($ft, $index) use ($start) {
            return [
                'DT_RowIndex'     => $start + $index + 1,
                'id'              => $ft->id,
                'fee_name'        => $ft->fee_name,
                'fee_category'    => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . ucfirst($ft->fee_category ?? '-') . '</span>',
                'is_recurring'    => $ft->is_recurring
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Yes (' . ($ft->recurring_months ?? '-') . ' months)</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">One-Time</span>',
                'display_order'   => $ft->display_order ?? '-',
                'is_active'       => $ft->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editFeeType(' . json_encode(['id' => $ft->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteFeeType(' . $ft->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'fee_name'         => 'required|string|max:100',
            'fee_category'     => 'nullable|string|in:school,academy,both',
            'is_recurring'     => 'boolean',
            'recurring_months' => 'nullable|string|max:50',
            'description'      => 'nullable|string',
            'display_order'    => 'nullable|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        FeeType::create($validated);

        return redirect()->route('fee-types.index')
            ->with('success', 'Fee type created successfully!');
    }

    public function update(Request $request, FeeType $feeType)
    {
        $validated = $request->validate([
            'fee_name'         => 'required|string|max:100',
            'fee_category'     => 'nullable|string|in:school,academy,both',
            'is_recurring'     => 'boolean',
            'recurring_months' => 'nullable|string|max:50',
            'description'      => 'nullable|string',
            'display_order'    => 'nullable|integer|min:0',
            'is_active'        => 'boolean',
        ]);

        $feeType->update($validated);

        return redirect()->route('fee-types.index')
            ->with('success', 'Fee type updated successfully!');
    }

    public function destroy(FeeType $feeType)
    {
        if ($feeType->feeStructures()->count() > 0) {
            return back()->with('error', 'Cannot delete fee type — it has fee structures!');
        }

        $feeType->delete();

        return redirect()->route('fee-types.index')
            ->with('success', 'Fee type deleted successfully!');
    }

    public function dropdown(Request $request)
    {
        $query = FeeType::active()
            ->select('id', 'fee_name', 'fee_category', 'is_recurring');

        if ($request->filled('fee_category')) {
            $query->forStudent($request->fee_category);
        }

        return response()->json($query->orderBy('display_order')->get());
    }
}