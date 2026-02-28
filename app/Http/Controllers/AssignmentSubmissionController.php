<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignmentSubmissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileSubmissions($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesSubmissions($request);
        }
        return Inertia::render('AssignmentSubmissions/Index');
    }

    public function create() { return Inertia::render('AssignmentSubmissions/Create'); }

    public function edit(AssignmentSubmission $assignmentSubmission)
    {
        $assignmentSubmission->load(['assignment', 'student', 'gradedBy']);
        return Inertia::render('AssignmentSubmissions/Edit', [
            'submission' => [
                'id'              => $assignmentSubmission->id,
                'assignment_id'   => $assignmentSubmission->assignment_id,
                'student_id'      => $assignmentSubmission->student_id,
                'submission_date' => $assignmentSubmission->submission_date?->format('Y-m-d H:i'),
                'content'         => $assignmentSubmission->content,
                'obtained_marks'  => $assignmentSubmission->obtained_marks,
                'grade'           => $assignmentSubmission->grade,
                'feedback'        => $assignmentSubmission->feedback,
                'is_late'         => $assignmentSubmission->is_late,
                'penalty_applied' => $assignmentSubmission->penalty_applied,
                'status'          => $assignmentSubmission->status,
            ]
        ]);
    }

    private function getMobileSubmissions(Request $request)
    {
        $query = AssignmentSubmission::with(['assignment', 'student', 'gradedBy']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")->orWhere('grade', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesSubmissions(Request $request)
    {
        $query = AssignmentSubmission::with(['assignment', 'student', 'gradedBy']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")->orWhere('grade', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }

        $totalData = $query->count();
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['id', 'assignment_id', 'student_id', 'obtained_marks', 'status', 'submission_date'];
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $submissions = $query->skip($start)->take($length)->get();

        $data = $submissions->map(function ($sub, $index) use ($start) {
            $statusClass = match($sub->status) {
                'graded'    => 'bg-green-100 text-green-800',
                'submitted' => 'bg-blue-100 text-blue-800',
                'late'      => 'bg-yellow-100 text-yellow-800',
                'pending'   => 'bg-gray-100 text-gray-800',
                default     => 'bg-gray-100 text-gray-800',
            };
            return [
                'DT_RowIndex'     => $start + $index + 1,
                'id'              => $sub->id,
                'assignment_title'=> $sub->assignment?->title ?? '-',
                'student_name'    => $sub->student?->first_name ?? '-',
                'submission_date' => $sub->submission_date?->format('d M, Y H:i') ?? '-',
                'obtained_marks'  => $sub->obtained_marks ?? '-',
                'grade'           => $sub->grade ?? '-',
                'is_late'         => $sub->is_late ? '<span class="text-xs text-red-600">Late</span>' : '<span class="text-xs text-green-600">On Time</span>',
                'status'          => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($sub->status) . '</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editSubmission(' . json_encode(['id' => $sub->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteSubmission(' . $sub->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
                    </div>'
            ];
        });

        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'student_id'    => 'required|exists:students,id',
            'content'       => 'nullable|string',
            'status'        => 'nullable|string|in:pending,submitted,late,graded',
        ]);
        AssignmentSubmission::create($validated);
        return redirect()->route('assignment-submissions.index')->with('success', 'Submission created successfully!');
    }

    public function update(Request $request, AssignmentSubmission $assignmentSubmission)
    {
        $validated = $request->validate([
            'obtained_marks'  => 'nullable|numeric|min:0',
            'grade'           => 'nullable|string|max:10',
            'feedback'        => 'nullable|string',
            'penalty_applied' => 'nullable|numeric|min:0',
            'status'          => 'nullable|string|in:pending,submitted,late,graded',
        ]);
        if (isset($validated['obtained_marks'])) {
            $validated['graded_by'] = auth()->id();
            $validated['graded_at'] = now();
            $validated['status'] = 'graded';
        }
        $assignmentSubmission->update($validated);
        return redirect()->route('assignment-submissions.index')->with('success', 'Submission updated successfully!');
    }

    public function destroy(AssignmentSubmission $assignmentSubmission)
    {
        $assignmentSubmission->delete();
        return back()->with('success', 'Submission deleted successfully!');
    }
}
