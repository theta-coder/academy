<?php

namespace App\Http\Controllers;

use App\Models\BranchClass;
use App\Models\Branch;
use App\Models\Classes;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BranchClassController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileBranchClasses($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesBranchClasses($request);
        }
        return Inertia::render('BranchClasses/Index');
    }

    public function create()
    {
        return Inertia::render('BranchClasses/Create', [
            'branches' => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
            'classes'  => Classes::active()->ordered()->select('id', 'class_name')->get(),
        ]);
    }

    public function edit(BranchClass $branchClass)
    {
        return Inertia::render('BranchClasses/Edit', [
            'branchClass' => [
                'id'        => $branchClass->id,
                'branch_id' => $branchClass->branch_id,
                'class_id'  => $branchClass->class_id,
                'is_active' => $branchClass->is_active,
            ],
            'branches' => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
            'classes'  => Classes::active()->ordered()->select('id', 'class_name')->get(),
        ]);
    }

    private function getMobileBranchClasses(Request $request)
    {
        $query = BranchClass::with(['branch', 'class']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('branch', fn($sq) => $sq->where('branch_name', 'like', "%{$search}%"))
                  ->orWhereHas('class', fn($sq) => $sq->where('class_name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesBranchClasses(Request $request)
    {
        $query = BranchClass::with(['branch', 'class']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->whereHas('branch', fn($sq) => $sq->where('branch_name', 'like', "%{$search}%"))
                  ->orWhereHas('class', fn($sq) => $sq->where('class_name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }

        $totalData = $query->count();
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'branch_id', 'class_id', 'is_active'];
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $branchClasses = $query->skip($start)->take($length)->get();

        $data = $branchClasses->map(function ($bc, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $bc->id,
                'branch_name' => $bc->branch?->branch_name ?? '-',
                'class_name'  => $bc->class?->class_name ?? '-',
                'is_active'   => $bc->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editBranchClass(' . json_encode(['id' => $bc->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteBranchClass(' . $bc->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
                    </div>'
            ];
        });

        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'class_id'  => 'required|exists:classes,id',
            'is_active' => 'boolean',
        ]);
        BranchClass::create($validated);
        return redirect()->route('branch-classes.index')->with('success', 'Branch class assignment created!');
    }

    public function update(Request $request, BranchClass $branchClass)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'class_id'  => 'required|exists:classes,id',
            'is_active' => 'boolean',
        ]);
        $branchClass->update($validated);
        return redirect()->route('branch-classes.index')->with('success', 'Branch class assignment updated!');
    }

    public function destroy(BranchClass $branchClass)
    {
        $branchClass->delete();
        return back()->with('success', 'Branch class assignment deleted!');
    }
}