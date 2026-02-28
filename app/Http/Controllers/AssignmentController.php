<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileAssignments($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesAssignments($request);
        }

        return Inertia::render('Assignments/Index');
    }

    public function create()
    {
        $branches = Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get();
        $classes = Classes::active()->ordered()->select('id', 'class_name')->get();
        $sections = Section::select('id', 'section_name')->orderBy('section_name')->get();
        $subjects = Subject::select('id', 'subject_name')->orderBy('subject_name')->get();
        $teachers = Teacher::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();

        return Inertia::render('Assignments/Create', compact('branches', 'classes', 'sections', 'subjects', 'teachers'));
    }

    public function edit(Assignment $assignment)
    {
        $branches = Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get();
        $classes = Classes::active()->ordered()->select('id', 'class_name')->get();
        $sections = Section::select('id', 'section_name')->orderBy('section_name')->get();
        $subjects = Subject::select('id', 'subject_name')->orderBy('subject_name')->get();
        $teachers = Teacher::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();

        return Inertia::render('Assignments/Edit', [
            'assignment' => [
                'id'                      => $assignment->id,
                'branch_id'               => $assignment->branch_id,
                'class_id'                => $assignment->class_id,
                'section_id'              => $assignment->section_id,
                'subject_id'              => $assignment->subject_id,
                'teacher_id'              => $assignment->teacher_id,
                'title'                   => $assignment->title,
                'description'             => $assignment->description,
                'total_marks'             => $assignment->total_marks,
                'issue_date'              => $assignment->issue_date?->format('Y-m-d'),
                'submission_date'         => $assignment->submission_date?->format('Y-m-d'),
                'late_submission_allowed' => $assignment->late_submission_allowed,
                'late_penalty_percent'    => $assignment->late_penalty_percent,
                'status'                  => $assignment->status,
            ],
            'branches' => $branches, 'classes' => $classes, 'sections' => $sections,
            'subjects' => $subjects, 'teachers' => $teachers,
        ]);
    }

    private function getMobileAssignments(Request $request)
    {
        $query = Assignment::with(['branch', 'class', 'section', 'subject', 'teacher'])
            ->withCount('submissions');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $assignments = $query->latest()->paginate($perPage);

        return response()->json($assignments);
    }

    private function getDataTablesAssignments(Request $request)
    {
        $query = Assignment::with(['branch', 'class', 'section', 'subject', 'teacher'])
            ->withCount('submissions');

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'title', 'class_id', 'subject_id', 'issue_date', 'submission_date', 'status'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start       = $request->input('start', 0);
        $length      = $request->input('length', 10);
        $assignments = $query->skip($start)->take($length)->get();

        $data = $assignments->map(function ($asgn, $index) use ($start) {
            $statusClass = match($asgn->status) {
                'active'    => 'bg-green-100 text-green-800',
                'closed'    => 'bg-gray-100 text-gray-800',
                'draft'     => 'bg-yellow-100 text-yellow-800',
                default     => 'bg-gray-100 text-gray-800',
            };

            return [
                'DT_RowIndex'      => $start + $index + 1,
                'id'               => $asgn->id,
                'title'            => \Illuminate\Support\Str::limit($asgn->title, 35),
                'class_name'       => $asgn->class?->class_name ?? '-',
                'subject_name'     => $asgn->subject?->subject_name ?? '-',
                'teacher_name'     => $asgn->teacher?->first_name ?? '-',
                'total_marks'      => $asgn->total_marks,
                'issue_date'       => $asgn->issue_date?->format('d M, Y') ?? '-',
                'submission_date'  => $asgn->submission_date?->format('d M, Y') ?? '-',
                'submissions_count'=> '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . $asgn->submissions_count . '</span>',
                'status'           => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($asgn->status) . '</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editAssignment(' . json_encode(['id' => $asgn->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <button onclick="deleteAssignment(' . $asgn->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'branch_id'               => 'required|exists:branches,id',
            'class_id'                => 'required|exists:classes,id',
            'section_id'              => 'required|exists:sections,id',
            'subject_id'              => 'required|exists:subjects,id',
            'teacher_id'              => 'required|exists:teachers,id',
            'title'                   => 'required|string|max:255',
            'description'             => 'nullable|string',
            'attachments'             => 'nullable|array',
            'total_marks'             => 'required|integer|min:1',
            'issue_date'              => 'required|date',
            'submission_date'         => 'required|date|after_or_equal:issue_date',
            'late_submission_allowed' => 'boolean',
            'late_penalty_percent'    => 'nullable|numeric|min:0|max:100',
            'status'                  => 'nullable|string|in:draft,active,closed',
        ]);

        Assignment::create($validated);

        return redirect()->route('assignments.index')
            ->with('success', 'Assignment created successfully!');
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'branch_id'               => 'required|exists:branches,id',
            'class_id'                => 'required|exists:classes,id',
            'section_id'              => 'required|exists:sections,id',
            'subject_id'              => 'required|exists:subjects,id',
            'teacher_id'              => 'required|exists:teachers,id',
            'title'                   => 'required|string|max:255',
            'description'             => 'nullable|string',
            'attachments'             => 'nullable|array',
            'total_marks'             => 'required|integer|min:1',
            'issue_date'              => 'required|date',
            'submission_date'         => 'required|date|after_or_equal:issue_date',
            'late_submission_allowed' => 'boolean',
            'late_penalty_percent'    => 'nullable|numeric|min:0|max:100',
            'status'                  => 'nullable|string|in:draft,active,closed',
        ]);

        $assignment->update($validated);

        return redirect()->route('assignments.index')
            ->with('success', 'Assignment updated successfully!');
    }

    public function destroy(Assignment $assignment)
    {
        if ($assignment->submissions()->count() > 0) {
            return back()->with('error', 'Cannot delete assignment with existing submissions!');
        }

        $assignment->delete();

        return back()->with('success', 'Assignment deleted successfully!');
    }
}
