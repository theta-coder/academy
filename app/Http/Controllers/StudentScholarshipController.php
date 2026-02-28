<?php

namespace App\Http\Controllers;

use App\Models\StudentScholarship;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Scholarship;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentScholarshipController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileScholarships($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesScholarships($request);
        }
        return Inertia::render('StudentScholarships/Index');
    }

    public function create()
    {
        return Inertia::render('StudentScholarships/Create', [
            'students'      => Student::select('id', 'student_name', 'admission_no')->orderBy('student_name')->get(),
            'scholarships'  => Scholarship::select('id', 'scholarship_name')->orderBy('scholarship_name')->get(),
            'academicYears' => AcademicYear::select('id', 'year_name')->orderBy('year_name', 'desc')->get(),
        ]);
    }

    public function edit(StudentScholarship $studentScholarship)
    {
        $studentScholarship->load(['studentEnrollment.student', 'studentEnrollment.academicYear', 'studentEnrollment.classSection.branchClass.class', 'studentEnrollment.classSection.section', 'scholarship', 'academicYear']);

        $enrollment = $studentScholarship->studentEnrollment;
        $studentId = $enrollment?->student_id;

        return Inertia::render('StudentScholarships/Edit', [
            'studentScholarship' => [
                'id'                    => $studentScholarship->id,
                'student_id'            => $studentId,
                'student_enrollment_id' => $studentScholarship->student_enrollment_id,
                'scholarship_id'        => $studentScholarship->scholarship_id,
                'academic_year_id'      => $studentScholarship->academic_year_id,
                'awarded_on'            => $studentScholarship->awarded_on?->format('Y-m-d'),
                'valid_from'            => $studentScholarship->valid_from?->format('Y-m-d'),
                'valid_to'              => $studentScholarship->valid_to?->format('Y-m-d'),
                'position_achieved'     => $studentScholarship->position_achieved,
                'marks_percentage'      => $studentScholarship->marks_percentage,
                'status'                => $studentScholarship->status,
                'revoke_reason'         => $studentScholarship->revoke_reason,
                'notes'                 => $studentScholarship->notes,
            ],
            'students'      => Student::select('id', 'student_name', 'admission_no')->orderBy('student_name')->get(),
            'scholarships'  => Scholarship::select('id', 'scholarship_name')->orderBy('scholarship_name')->get(),
            'academicYears' => AcademicYear::select('id', 'year_name')->orderBy('year_name', 'desc')->get(),
            'initialEnrollments' => $studentId
                ? StudentEnrollment::where('student_id', $studentId)
                    ->with(['academicYear:id,year_name', 'classSection.branchClass.class', 'classSection.section'])
                    ->get()
                    ->map(fn($e) => [
                        'id'            => $e->id,
                        'class_name'    => $e->classSection?->branchClass?->class?->class_name ?? '-',
                        'section_name'  => $e->classSection?->section?->section_name ?? '-',
                        'academic_year' => $e->academicYear?->year_name ?? '-',
                    ])
                : [],
        ]);
    }

    /**
     * API: Get enrollments for a specific student (for cascading dropdown)
     */
    public function enrollmentsByStudent($studentId)
    {
        $enrollments = StudentEnrollment::where('student_id', $studentId)
            ->with(['academicYear:id,year_name', 'classSection.branchClass.class', 'classSection.section'])
            ->get()
            ->map(fn($e) => [
                'id'            => $e->id,
                'class_name'    => $e->classSection?->branchClass?->class?->class_name ?? '-',
                'section_name'  => $e->classSection?->section?->section_name ?? '-',
                'academic_year' => $e->academicYear?->year_name ?? '-',
            ]);

        return response()->json($enrollments);
    }

    private function getMobileScholarships(Request $request)
    {
        $query = StudentScholarship::with(['studentEnrollment.student', 'scholarship', 'academicYear']);
        if ($request->filled('search')) {
            $query->where('notes', 'like', "%{$request->search}%")
                  ->orWhereHas('studentEnrollment.student', fn($q) => $q->where('student_name', 'like', "%{$request->search}%"));
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesScholarships(Request $request)
    {
        $query = StudentScholarship::with(['studentEnrollment.student', 'scholarship', 'academicYear']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) { $q->where('notes', 'like', "%{$search}%")->orWhere('status', 'like', "%{$search}%"); });
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }

        $totalData = $query->count();
        $columns = ['id', 'student_enrollment_id', 'scholarship_id', 'awarded_on', 'status'];
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $scholarships = $query->skip($start)->take($length)->get();

        $data = $scholarships->map(function ($s, $index) use ($start) {
            $statusClass = match($s->status) { 'active' => 'bg-green-100 text-green-800', 'expired' => 'bg-gray-100 text-gray-800', 'revoked' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-800' };
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $s->id,
                'student_name'     => $s->studentEnrollment?->student?->student_name ?? '-',
                'scholarship_name' => $s->scholarship?->scholarship_name ?? '-',
                'year_name'        => $s->academicYear?->year_name ?? '-',
                'awarded_on'       => $s->awarded_on?->format('d M, Y') ?? '-',
                'valid_period'     => ($s->valid_from?->format('d M, Y') ?? '-') . ' to ' . ($s->valid_to?->format('d M, Y') ?? 'Ongoing'),
                'status' => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($s->status) . '</span>',
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'editScholarship(' . json_encode(['id' => $s->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="deleteScholarship(' . $s->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'scholarship_id'        => 'required|exists:scholarships,id',
            'academic_year_id'      => 'required|exists:academic_years,id',
            'awarded_on'            => 'required|date',
            'valid_from'            => 'required|date',
            'valid_to'              => 'nullable|date|after_or_equal:valid_from',
            'position_achieved'     => 'nullable|string|max:50',
            'marks_percentage'      => 'nullable|numeric|min:0|max:100',
            'status'                => 'nullable|string|in:active,expired,revoked',
            'notes'                 => 'nullable|string',
        ]);
        $validated['awarded_by'] = auth()->id();
        StudentScholarship::create($validated);
        return redirect()->route('student-scholarships.index')->with('success', 'Scholarship assigned successfully!');
    }

    public function update(Request $request, StudentScholarship $studentScholarship)
    {
        $validated = $request->validate([
            'scholarship_id'   => 'required|exists:scholarships,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'awarded_on'       => 'required|date',
            'valid_from'       => 'required|date',
            'valid_to'         => 'nullable|date|after_or_equal:valid_from',
            'position_achieved'=> 'nullable|string|max:50',
            'marks_percentage' => 'nullable|numeric|min:0|max:100',
            'status'           => 'nullable|string|in:active,expired,revoked',
            'revoke_reason'    => 'nullable|string',
            'notes'            => 'nullable|string',
        ]);
        $studentScholarship->update($validated);
        return redirect()->route('student-scholarships.index')->with('success', 'Scholarship updated successfully!');
    }

    public function destroy(StudentScholarship $studentScholarship)
    {
        $studentScholarship->delete();
        return back()->with('success', 'Scholarship assignment deleted successfully!');
    }
}
