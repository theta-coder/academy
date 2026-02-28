<?php

namespace App\Http\Controllers;

use App\Models\StudentInstallmentAssignment;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\InstallmentPlan;
use App\Models\FeeVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentInstallmentAssignmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileAssignments($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesAssignments($request);
        }
        return Inertia::render('StudentInstallmentAssignments/Index');
    }

    public function create()
    {
        return Inertia::render('StudentInstallmentAssignments/Create', [
            'students'         => Student::select('id', 'student_name', 'admission_no')->orderBy('student_name')->get(),
            'installmentPlans' => InstallmentPlan::select('id', 'plan_name', 'total_installments')->where('is_active', true)->orderBy('plan_name')->get(),
            'feeVouchers'      => FeeVoucher::select('id', 'voucher_no', 'net_amount', 'status')->orderBy('id', 'desc')->get(),
        ]);
    }

    public function edit(StudentInstallmentAssignment $studentInstallmentAssignment)
    {
        $studentInstallmentAssignment->load(['studentEnrollment.student', 'studentEnrollment.academicYear', 'studentEnrollment.classSection.branchClass.class', 'studentEnrollment.classSection.section', 'installmentPlan', 'feeVoucher']);

        $enrollment = $studentInstallmentAssignment->studentEnrollment;
        $studentId = $enrollment?->student_id;

        return Inertia::render('StudentInstallmentAssignments/Edit', [
            'assignment' => [
                'id'                    => $studentInstallmentAssignment->id,
                'student_id'            => $studentId,
                'student_enrollment_id' => $studentInstallmentAssignment->student_enrollment_id,
                'installment_plan_id'   => $studentInstallmentAssignment->installment_plan_id,
                'fee_voucher_id'        => $studentInstallmentAssignment->fee_voucher_id,
                'total_amount'          => $studentInstallmentAssignment->total_amount,
                'status'                => $studentInstallmentAssignment->status,
            ],
            'students'         => Student::select('id', 'student_name', 'admission_no')->orderBy('student_name')->get(),
            'installmentPlans' => InstallmentPlan::select('id', 'plan_name', 'total_installments')->where('is_active', true)->orderBy('plan_name')->get(),
            'feeVouchers'      => FeeVoucher::select('id', 'voucher_no', 'net_amount', 'status')->orderBy('id', 'desc')->get(),
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

    private function getMobileAssignments(Request $request)
    {
        $query = StudentInstallmentAssignment::with(['studentEnrollment.student', 'installmentPlan', 'feeVoucher']);
        if ($request->filled('search')) {
            $query->whereHas('studentEnrollment.student', fn($sq) => $sq->where('student_name', 'like', "%{$request->search}%"));
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesAssignments(Request $request)
    {
        $query = StudentInstallmentAssignment::with(['studentEnrollment.student', 'installmentPlan', 'feeVoucher']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('total_amount', 'like', "%{$search}%")->orWhere('status', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) { $query->where('status', $request->status); }

        $totalData = $query->count();
        $columns = ['id', 'student_enrollment_id', 'installment_plan_id', 'total_amount', 'status'];
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $assignments = $query->skip($start)->take($length)->get();

        $data = $assignments->map(function ($a, $index) use ($start) {
            $statusClass = match($a->status) { 'active' => 'bg-green-100 text-green-800', 'completed' => 'bg-blue-100 text-blue-800', 'cancelled' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-800' };
            return [
                'DT_RowIndex'      => $start + $index + 1, 'id' => $a->id,
                'student_name'     => $a->studentEnrollment?->student?->student_name ?? '-',
                'plan_name'        => $a->installmentPlan?->plan_name ?? '-',
                'total_amount'     => number_format($a->total_amount, 2),
                'amount_paid'      => number_format($a->amount_paid ?? 0, 2),
                'remaining_amount' => number_format($a->remaining_amount ?? 0, 2),
                'status'         => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($a->status) . '</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editAssignment(' . json_encode(['id' => $a->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteAssignment(' . $a->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'installment_plan_id'   => 'required|exists:installment_plans,id',
            'fee_voucher_id'        => 'nullable|exists:fee_vouchers,id',
            'total_amount'          => 'required|numeric|min:0',
            'status'                => 'nullable|string|in:active,completed,defaulted',
            'notes'                 => 'nullable|string',
        ]);
        $validated['approved_by'] = auth()->id();
        if (empty($validated['fee_voucher_id'])) {
            unset($validated['fee_voucher_id']);
        }
        if (empty($validated['notes'])) {
            unset($validated['notes']);
        }
        StudentInstallmentAssignment::create($validated);
        return redirect()->route('student-installment-assignments.index')->with('success', 'Installment assigned successfully!');
    }

    public function update(Request $request, StudentInstallmentAssignment $studentInstallmentAssignment)
    {
        $validated = $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'status'       => 'nullable|string|in:active,completed,defaulted',
        ]);
        $studentInstallmentAssignment->update($validated);
        return redirect()->route('student-installment-assignments.index')->with('success', 'Assignment updated successfully!');
    }

    public function destroy(StudentInstallmentAssignment $studentInstallmentAssignment)
    {
        if ($studentInstallmentAssignment->schedule()->count() > 0) {
            return back()->with('error', 'Cannot delete assignment with existing installment schedules!');
        }
        $studentInstallmentAssignment->delete();
        return back()->with('success', 'Assignment deleted successfully!');
    }
}
