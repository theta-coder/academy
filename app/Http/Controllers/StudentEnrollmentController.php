<?php

namespace App\Http\Controllers;

use App\Models\StudentEnrollment;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\ClassSection;
use App\Models\BranchClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentEnrollmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileEnrollments($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesEnrollments($request);
        }
        return Inertia::render('StudentEnrollments/Index');
    }

    public function create()
    {
        return Inertia::render('StudentEnrollments/Create', [
            'students'      => Student::select('id', 'student_name', 'admission_no')->orderBy('student_name')->get(),
            'academicYears' => AcademicYear::select('id', 'year_name')->orderBy('start_date', 'desc')->get(),
            'branches'      => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
        ]);
    }

    public function edit(StudentEnrollment $studentEnrollment)
    {
        $studentEnrollment->load(['student', 'academicYear', 'branch', 'classSection.branchClass']);

        // Pre-load the branch_class_id for cascading dropdown
        $branchClassId = $studentEnrollment->classSection?->branch_class_id;

        return Inertia::render('StudentEnrollments/Edit', [
            'enrollment' => [
                'id'               => $studentEnrollment->id,
                'student_id'       => $studentEnrollment->student_id,
                'academic_year_id' => $studentEnrollment->academic_year_id,
                'branch_id'        => $studentEnrollment->branch_id,
                'branch_class_id'  => $branchClassId,
                'class_section_id' => $studentEnrollment->class_section_id,
                'roll_number'      => $studentEnrollment->roll_number,
                'admission_date'   => $studentEnrollment->admission_date?->format('Y-m-d'),
                'leaving_date'     => $studentEnrollment->leaving_date?->format('Y-m-d'),
                'enrollment_type'  => $studentEnrollment->enrollment_type,
                'sibling_order'    => $studentEnrollment->sibling_order,
                'status'           => $studentEnrollment->status,
                'leaving_reason'   => $studentEnrollment->leaving_reason,
                'remarks'          => $studentEnrollment->remarks,
            ],
            'students'      => Student::select('id', 'student_name', 'admission_no')->orderBy('student_name')->get(),
            'academicYears' => AcademicYear::select('id', 'year_name')->orderBy('start_date', 'desc')->get(),
            'branches'      => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
            // Pre-load classes & sections for the selected branch so Edit form shows them immediately
            'initialClasses'  => $branchClassId
                ? BranchClass::where('branch_id', $studentEnrollment->branch_id)
                    ->active()->with('class:id,class_name')->get()
                    ->map(fn($bc) => ['id' => $bc->id, 'class_name' => $bc->class?->class_name])
                : [],
            'initialSections' => $branchClassId
                ? ClassSection::where('branch_class_id', $branchClassId)
                    ->active()->with('section:id,section_name')->get()
                    ->map(fn($cs) => ['id' => $cs->id, 'section_name' => $cs->section?->section_name])
                : [],
        ]);
    }

    /**
     * API: Get classes mapped to a branch (via branch_classes table)
     */
    public function classesByBranch($branchId)
    {
        $branchClasses = BranchClass::where('branch_id', $branchId)
            ->active()
            ->with('class:id,class_name')
            ->get()
            ->map(fn($bc) => [
                'id'         => $bc->id,           // branch_class_id
                'class_name' => $bc->class?->class_name,
            ]);

        return response()->json($branchClasses);
    }

    /**
     * API: Get sections mapped to a branch_class (via class_sections table)
     */
    public function sectionsByBranchClass($branchClassId)
    {
        $classSections = ClassSection::where('branch_class_id', $branchClassId)
            ->active()
            ->with('section:id,section_name')
            ->get()
            ->map(fn($cs) => [
                'id'           => $cs->id,          // class_section_id
                'section_name' => $cs->section?->section_name,
            ]);

        return response()->json($classSections);
    }

    private function getMobileEnrollments(Request $request)
    {
        $query = StudentEnrollment::with(['student', 'academicYear', 'branch', 'classSection.branchClass.class', 'classSection.section']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('roll_number', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($sq) => $sq->where('student_name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesEnrollments(Request $request)
    {
        $query = StudentEnrollment::with(['student', 'academicYear', 'branch', 'classSection.branchClass.class', 'classSection.section']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('roll_number', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($sq) => $sq->where('student_name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('academic_year_id')) { $query->where('academic_year_id', $request->academic_year_id); }

        $totalData = $query->count();
        $columns = ['id', 'student_id', 'academic_year_id', 'branch_id', 'roll_number', 'admission_date', 'status'];
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $enrollments = $query->skip($start)->take($length)->get();

        $data = $enrollments->map(function ($enr, $index) use ($start) {
            $statusClass = match($enr->status) {
                'active'    => 'bg-green-100 text-green-800',
                'promoted'  => 'bg-blue-100 text-blue-800',
                'withdrawn' => 'bg-red-100 text-red-800',
                'graduated' => 'bg-purple-100 text-purple-800',
                default     => 'bg-gray-100 text-gray-800',
            };
            return [
                'DT_RowIndex'     => $start + $index + 1, 'id' => $enr->id,
                'student_name'    => $enr->student?->student_name ?? '-',
                'academic_year'   => $enr->academicYear?->year_name ?? '-',
                'branch_name'     => $enr->branch?->branch_name ?? '-',
                'class_section'   => ($enr->classSection?->branchClass?->class?->class_name ?? '-') . ' - ' . ($enr->classSection?->section?->section_name ?? ''),
                'roll_number'     => $enr->roll_number ?? '-',
                'enrollment_type' => ucfirst($enr->enrollment_type ?? '-'),
                'admission_date'  => $enr->admission_date?->format('d M, Y') ?? '-',
                'status'          => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($enr->status) . '</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editEnrollment(' . json_encode(['id' => $enr->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteEnrollment(' . $enr->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
                    </div>'
            ];
        });

        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id'        => 'required|exists:branches,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'roll_number'      => 'nullable|string|max:50',
            'admission_date'   => 'required|date',
            'enrollment_type'  => 'nullable|string|in:school,academy,both',
            'sibling_order'    => 'nullable|integer|min:1',
            'status'           => 'nullable|string|in:active,left,graduated,transferred,suspended',
            'remarks'          => 'nullable|string|max:1000',
        ]);
        StudentEnrollment::create($validated);
        return redirect()->route('student-enrollments.index')->with('success', 'Student enrolled successfully!');
    }

    public function update(Request $request, StudentEnrollment $studentEnrollment)
    {
        $validated = $request->validate([
            'student_id'       => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id'        => 'required|exists:branches,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'roll_number'      => 'nullable|string|max:50',
            'admission_date'   => 'required|date',
            'leaving_date'     => 'nullable|date',
            'enrollment_type'  => 'nullable|string|in:school,academy,both',
            'sibling_order'    => 'nullable|integer|min:1',
            'status'           => 'nullable|string|in:active,left,graduated,transferred,suspended',
            'leaving_reason'   => 'nullable|string|max:1000',
            'remarks'          => 'nullable|string|max:1000',
        ]);
        $studentEnrollment->update($validated);
        return redirect()->route('student-enrollments.index')->with('success', 'Enrollment updated successfully!');
    }

    public function destroy(StudentEnrollment $studentEnrollment)
    {
        if ($studentEnrollment->feeVouchers()->count() > 0) {
            return back()->with('error', 'Cannot delete enrollment with existing fee vouchers!');
        }
        $studentEnrollment->delete();
        return back()->with('success', 'Enrollment deleted successfully!');
    }
}
