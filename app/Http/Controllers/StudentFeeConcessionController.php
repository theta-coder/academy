<?php

namespace App\Http\Controllers;

use App\Models\StudentFeeConcession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\FeeType;
use App\Models\FeeConcessionType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentFeeConcessionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileConcessions($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesConcessions($request);
        }
        return Inertia::render('StudentFeeConcessions/Index');
    }

    public function create()
    {
        return Inertia::render('StudentFeeConcessions/Create', [
            'students'        => Student::select('id', 'student_name', 'admission_no')->orderBy('student_name')->get(),
            'feeTypes'        => FeeType::select('id', 'fee_name')->orderBy('fee_name')->get(),
            'concessionTypes' => FeeConcessionType::select('id', 'concession_name')->orderBy('concession_name')->get(),
        ]);
    }

    public function edit(StudentFeeConcession $studentFeeConcession)
    {
        $studentFeeConcession->load(['studentEnrollment.student', 'studentEnrollment.academicYear', 'studentEnrollment.classSection.branchClass.class', 'studentEnrollment.classSection.section', 'feeType', 'concessionType']);

        $enrollment = $studentFeeConcession->studentEnrollment;
        $studentId = $enrollment?->student_id;

        return Inertia::render('StudentFeeConcessions/Edit', [
            'concession' => [
                'id'                    => $studentFeeConcession->id,
                'student_id'            => $studentId,
                'student_enrollment_id' => $studentFeeConcession->student_enrollment_id,
                'fee_type_id'           => $studentFeeConcession->fee_type_id,
                'concession_type_id'    => $studentFeeConcession->concession_type_id,
                'discount_type'         => $studentFeeConcession->discount_type,
                'discount_value'        => $studentFeeConcession->discount_value,
                'start_date'            => $studentFeeConcession->start_date?->format('Y-m-d'),
                'end_date'              => $studentFeeConcession->end_date?->format('Y-m-d'),
                'remarks'               => $studentFeeConcession->remarks,
                'is_active'             => $studentFeeConcession->is_active,
            ],
            'students'        => Student::select('id', 'student_name', 'admission_no')->orderBy('student_name')->get(),
            'feeTypes'        => FeeType::select('id', 'fee_name')->orderBy('fee_name')->get(),
            'concessionTypes' => FeeConcessionType::select('id', 'concession_name')->orderBy('concession_name')->get(),
            // Pre-load enrollments for the selected student so Edit form shows them immediately
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

    private function getMobileConcessions(Request $request)
    {
        $query = StudentFeeConcession::with(['studentEnrollment.student', 'feeType', 'concessionType']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('remarks', 'like', "%{$search}%")
                  ->orWhereHas('studentEnrollment.student', fn($sq) => $sq->where('student_name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesConcessions(Request $request)
    {
        $query = StudentFeeConcession::with(['studentEnrollment.student', 'feeType', 'concessionType']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('remarks', 'like', "%{$search}%");
            });
        }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }

        $totalData = $query->count();
        $columns = ['id', 'student_enrollment_id', 'fee_type_id', 'discount_value', 'is_active'];
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $concessions = $query->skip($start)->take($length)->get();

        $data = $concessions->map(function ($c, $index) use ($start) {
            return [
                'DT_RowIndex'     => $start + $index + 1, 'id' => $c->id,
                'student_name'    => $c->studentEnrollment?->student?->student_name ?? '-',
                'fee_type'        => $c->feeType?->fee_name ?? 'All',
                'concession_type' => $c->concessionType?->concession_name ?? '-',
                'discount'        => $c->discount_type === 'percentage' ? $c->discount_value . '%' : number_format($c->discount_value, 2),
                'dates'           => ($c->start_date?->format('d M, Y') ?? '-') . ' to ' . ($c->end_date?->format('d M, Y') ?? 'ongoing'),
                'is_active'       => $c->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editConcession(' . json_encode(['id' => $c->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteConcession(' . $c->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
                    </div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'fee_type_id'           => 'nullable|exists:fee_types,id',
            'concession_type_id'    => 'required|exists:fee_concession_types,id',
            'discount_type'         => 'required|string|in:percentage,fixed',
            'discount_value'        => 'required|numeric|min:0',
            'start_date'            => 'nullable|date',
            'end_date'              => 'nullable|date|after_or_equal:start_date',
            'remarks'               => 'nullable|string',
            'is_active'             => 'boolean',
        ]);
        $validated['approved_by'] = auth()->id();
        StudentFeeConcession::create($validated);
        return redirect()->route('student-fee-concessions.index')->with('success', 'Fee concession assigned successfully!');
    }

    public function update(Request $request, StudentFeeConcession $studentFeeConcession)
    {
        $validated = $request->validate([
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'fee_type_id'           => 'nullable|exists:fee_types,id',
            'concession_type_id'    => 'required|exists:fee_concession_types,id',
            'discount_type'         => 'required|string|in:percentage,fixed',
            'discount_value'        => 'required|numeric|min:0',
            'start_date'            => 'nullable|date',
            'end_date'              => 'nullable|date|after_or_equal:start_date',
            'remarks'               => 'nullable|string',
            'is_active'             => 'boolean',
        ]);
        $studentFeeConcession->update($validated);
        return redirect()->route('student-fee-concessions.index')->with('success', 'Fee concession updated successfully!');
    }

    public function destroy(StudentFeeConcession $studentFeeConcession)
    {
        $studentFeeConcession->delete();
        return back()->with('success', 'Fee concession deleted successfully!');
    }
}
