<?php

namespace App\Http\Controllers;

use App\Models\SubjectGroup;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectGroupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileGroups($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesGroups($request); }
        return Inertia::render('SubjectGroups/Index');
    }

    public function create()
    {
        return Inertia::render('SubjectGroups/Create', [
            'subjects' => Subject::active()->select('id', 'subject_name', 'subject_code', 'is_compulsory')->orderBy('subject_name')->get(),
        ]);
    }

    public function edit(SubjectGroup $subjectGroup)
    {
        return Inertia::render('SubjectGroups/Edit', [
            'subjectGroup' => [
                'id' => $subjectGroup->id, 'group_name' => $subjectGroup->group_name,
                'description' => $subjectGroup->description, 'subject_ids' => $subjectGroup->subject_ids,
                'is_active' => $subjectGroup->is_active,
            ],
            'subjects' => Subject::active()->select('id', 'subject_name', 'subject_code', 'is_compulsory')->orderBy('subject_name')->get(),
        ]);
    }

    private function getMobileGroups(Request $request)
    {
        $query = SubjectGroup::query();
        if ($request->filled('search')) { $query->where('group_name', 'like', "%{$request->search}%"); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }
        return response()->json($query->orderBy('group_name')->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesGroups(Request $request)
    {
        $query = SubjectGroup::query();
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('group_name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"); }); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }

        $totalData = $query->count();
        $columns = ['id', 'group_name', 'is_active'];
        $orderColumn = $request->input('order.0.column', 1); $orderDir = $request->input('order.0.dir', 'asc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $groups = $query->skip($start)->take($length)->get();

        $data = $groups->map(function ($g, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $g->id,
                'group_name' => $g->group_name,
                'description' => $g->description ?? '-',
                'subjects_count' => $g->subject_ids ? count(is_array($g->subject_ids) ? $g->subject_ids : explode(',', $g->subject_ids)) : 0,
                'is_active' => $g->is_active ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>' : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'editGroup(' . json_encode(['id' => $g->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="deleteGroup(' . $g->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name'  => 'required|string|max:50',
            'description' => 'nullable|string',
            'subject_ids' => 'nullable',
            'is_active'   => 'boolean',
        ]);
        SubjectGroup::create($validated);
        return redirect()->route('subject-groups.index')->with('success', 'Subject group created successfully!');
    }

    public function update(Request $request, SubjectGroup $subjectGroup)
    {
        $validated = $request->validate([
            'group_name'  => 'required|string|max:50',
            'description' => 'nullable|string',
            'subject_ids' => 'nullable',
            'is_active'   => 'boolean',
        ]);
        $subjectGroup->update($validated);
        return redirect()->route('subject-groups.index')->with('success', 'Subject group updated successfully!');
    }

    public function destroy(SubjectGroup $subjectGroup)
    {
        if ($subjectGroup->classSubjects()->count() > 0) {
            return back()->with('error', 'Cannot delete subject group with assigned class subjects!');
        }
        $subjectGroup->delete();
        return back()->with('success', 'Subject group deleted successfully!');
    }

    public function dropdown()
    {
        return response()->json(SubjectGroup::active()->select('id', 'group_name')->orderBy('group_name')->get());
    }
}