<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects
     */
    public function index(Request $request)
    {
        // Mobile pagination request
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileSubjects($request);
        }

        // DataTables AJAX request
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesSubjects($request);
        }

        // For initial Inertia page load
        return Inertia::render('Subjects/Index');
    }

    /**
     * Show the form for creating a new subject
     */
    public function create()
    {
        return Inertia::render('Subjects/Create');
    }

    /**
     * Show the form for editing the specified subject
     */
    public function edit(Subject $subject)
    {
        return Inertia::render('Subjects/Edit', [
            'subject' => [
                'id'            => $subject->id,
                'subject_name'  => $subject->subject_name,
                'subject_code'  => $subject->subject_code,
                'is_compulsory' => $subject->is_compulsory,
                'is_active'     => $subject->is_active,
            ]
        ]);
    }

    private function getMobileSubjects(Request $request)
    {
        $query = Subject::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject_name', 'like', "%{$search}%")
                  ->orWhere('subject_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('is_compulsory')) {
            $query->where('is_compulsory', $request->is_compulsory);
        }

        $perPage = $request->get('per_page', 10);
        $subjects = $query->orderBy('is_compulsory', 'desc')->orderBy('subject_name')->paginate($perPage);

        return response()->json($subjects);
    }

    private function getDataTablesSubjects(Request $request)
    {
        $query = Subject::query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('subject_name', 'like', "%{$search}%")
                  ->orWhere('subject_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('is_compulsory')) {
            $query->where('is_compulsory', $request->is_compulsory);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir    = $request->input('order.0.dir', 'asc');
        $columns     = ['id', 'subject_name', 'subject_code', 'is_compulsory', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start    = $request->input('start', 0);
        $length   = $request->input('length', 10);
        $subjects = $query->skip($start)->take($length)->get();

        $data = $subjects->map(function ($subject, $index) use ($start) {
            return [
                'DT_RowIndex'   => $start + $index + 1,
                'id'            => $subject->id,
                'subject_name'  => $subject->subject_name,
                'subject_code'  => $subject->subject_code
                    ? '<span class="font-mono text-sm text-gray-700">' . $subject->subject_code . '</span>'
                    : '<span class="text-gray-400">—</span>',
                'is_compulsory' => $subject->is_compulsory
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">Compulsory</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Optional</span>',
                'is_active'     => $subject->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editSubject(' . json_encode([
                            'id' => $subject->id,
                            'subject_name' => $subject->subject_name,
                            'subject_code' => $subject->subject_code,
                            'is_compulsory' => $subject->is_compulsory,
                            'is_active' => $subject->is_active,
                        ]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteSubject(' . $subject->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
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

    /**
     * Store a newly created subject
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_name'  => 'required|string|max:100',
            'subject_code'  => 'nullable|string|max:20|unique:subjects,subject_code',
            'is_compulsory' => 'boolean',
            'is_active'     => 'boolean',
        ]);

        $validated['is_compulsory'] = $validated['is_compulsory'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;

        Subject::create($validated);

        return back()->with('success', 'Subject created successfully!');
    }

    /**
     * Update the specified subject
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'subject_name'  => 'required|string|max:100',
            'subject_code'  => 'nullable|string|max:20|unique:subjects,subject_code,' . $subject->id,
            'is_compulsory' => 'boolean',
            'is_active'     => 'boolean',
        ]);

        $subject->update($validated);

        return back()->with('success', 'Subject updated successfully!');
    }

    /**
     * Remove the specified subject
     */
    public function destroy(Subject $subject)
    {
        $classCount = $subject->classSubjects()->count();
        if ($classCount > 0) {
            return back()->with('error', "Cannot delete subject. It is assigned to {$classCount} class(es)!");
        }

        $subject->delete();

        return back()->with('success', 'Subject deleted successfully!');
    }

    /**
     * Get subjects for dropdown (API endpoint)
     */
    public function dropdown()
    {
        $subjects = Subject::active()
            ->select('id', 'subject_name', 'subject_code', 'is_compulsory')
            ->orderBy('is_compulsory', 'desc')
            ->orderBy('subject_name')
            ->get();

        return response()->json($subjects);
    }
}