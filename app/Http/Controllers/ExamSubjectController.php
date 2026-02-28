<?php

namespace App\Http\Controllers;

use App\Models\ExamSubject;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamSubjectController extends Controller
{
    /**
     * Display a listing of exam subjects
     */
    public function index(Request $request)
    {
        // Mobile pagination request
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileExamSubjects($request);
        }

        // DataTables AJAX request
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesExamSubjects($request);
        }

        return Inertia::render('ExamSubjects/Index', [
            'exams' => Exam::select('id', 'name', 'exam_code')->latest()->get(),
        ]);
    }

    private function getMobileExamSubjects(Request $request)
    {
        $query = ExamSubject::with(['exam:id,name,exam_code', 'subject:id,subject_name']);

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('exam', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('subject', function ($q) use ($search) {
                $q->where('subject_name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $examSubjects = $query->latest()->paginate($perPage);

        return response()->json($examSubjects);
    }

    private function getDataTablesExamSubjects(Request $request)
    {
        $query = ExamSubject::with(['exam:id,name,exam_code', 'subject:id,name']);
        
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->whereHas('exam', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('exam_code', 'like', "%{$search}%");
            })->orWhereHas('subject', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }
        
        $totalData = $query->count();
        
        $orderColumn = $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'exam_id', 'subject_id', 'exam_date', 'exam_time', 'total_marks'];
        
        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }
        
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        
        $examSubjects = $query->skip($start)->take($length)->get();
        
        $data = $examSubjects->map(fn($examSubject, $index) => [
                'DT_RowIndex' => $start + $index + 1,
                'id' => $examSubject->id,
                'exam' => $examSubject->exam->name . ' (' . $examSubject->exam->exam_code . ')',
                'subject' => $examSubject->subject->name ?? 'N/A',
                'exam_date' => \Carbon\Carbon::parse($examSubject->exam_date)->format('d M Y'),
                'exam_time' => \Carbon\Carbon::parse($examSubject->exam_time)->format('h:i A'),
                'duration' => $examSubject->duration_minutes . ' mins',
                'total_marks' => $examSubject->total_marks,
                'theory_marks' => $examSubject->theory_marks ?? '—',
                'practical_marks' => $examSubject->practical_marks ?? '—',
                'pass_marks' => $examSubject->pass_marks,
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <a href="' . route('exam-subjects.edit', $examSubject->id) . '" class="text-blue-600 hover:text-blue-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <button onclick="deleteExamSubject(' . $examSubject->id . ')" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                '
        ]);
        
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalData,
            'data' => $data
        ]);
    }

    public function create()
    {
        return Inertia::render('ExamSubjects/Create', [
            'exams' => Exam::select('id', 'name', 'exam_code')->latest()->get(),
            'subjects' => Subject::active()->select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date',
            'exam_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'theory_marks' => 'nullable|integer|min:0|lte:total_marks',
            'practical_marks' => 'nullable|integer|min:0|lte:total_marks',
            'pass_marks' => 'required|integer|min:1|lte:total_marks',
        ]);

        // Check if this exam-subject combination already exists
        $exists = ExamSubject::where('exam_id', $validated['exam_id'])
            ->where('subject_id', $validated['subject_id'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'This subject is already added to the exam!');
        }

        ExamSubject::create($validated);

        return redirect()
            ->route('exam-subjects.index')
            ->with('success', 'Exam subject added successfully!');
    }

    public function show(ExamSubject $examSubject)
    {
        return Inertia::render('ExamSubjects/Show', [
            'examSubject' => $examSubject->load(['exam', 'subject'])
        ]);
    }

    public function edit(ExamSubject $examSubject)
    {
        return Inertia::render('ExamSubjects/Edit', [
            'examSubject' => $examSubject->load(['exam', 'subject']),
            'subjects' => Subject::active()->select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, ExamSubject $examSubject)
    {
        $validated = $request->validate([
            'exam_date' => 'required|date',
            'exam_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'theory_marks' => 'nullable|integer|min:0|lte:total_marks',
            'practical_marks' => 'nullable|integer|min:0|lte:total_marks',
            'pass_marks' => 'required|integer|min:1|lte:total_marks',
        ]);

        $examSubject->update($validated);

        return redirect()
            ->route('exam-subjects.index')
            ->with('success', 'Exam subject updated successfully!');
    }

    public function destroy(ExamSubject $examSubject)
    {
        $examSubject->delete();

        return redirect()
            ->route('exam-subjects.index')
            ->with('success', 'Exam subject removed successfully!');
    }

    /**
     * Get subjects by exam
     */
    public function getByExam(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
        ]);

        $examSubjects = ExamSubject::where('exam_id', $request->exam_id)
            ->with('subject:id,name')
            ->get();

        return response()->json($examSubjects);
    }
}