<?php

namespace App\Http\Controllers;

use App\Models\InstallmentSchedule;
use App\Models\StudentInstallmentAssignment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstallmentScheduleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileSchedules($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesSchedules($request);
        }

        return Inertia::render('InstallmentSchedules/Index');
    }

    public function create()
    {
        return Inertia::render('InstallmentSchedules/Create', [
            'assignments' => StudentInstallmentAssignment::with([
                'studentEnrollment.student:id,student_name',
                'installmentPlan:id,plan_name',
            ])->get()->map(fn($a) => [
                'id' => $a->id,
                'student_enrollment' => [
                    'student' => $a->studentEnrollment?->student,
                ],
                'installment_plan' => $a->installmentPlan,
            ]),
        ]);
    }

    public function edit(InstallmentSchedule $installmentSchedule)
    {
        $installmentSchedule->load(['assignment.installmentPlan', 'payment']);

        return Inertia::render('InstallmentSchedules/Edit', [
            'schedule' => [
                'id'            => $installmentSchedule->id,
                'assignment_id' => $installmentSchedule->assignment_id,
                'kist_number'   => $installmentSchedule->kist_number,
                'kist_amount'   => $installmentSchedule->kist_amount,
                'due_date'      => $installmentSchedule->due_date?->format('Y-m-d'),
                'paid_amount'   => $installmentSchedule->paid_amount,
                'payment_date'  => $installmentSchedule->payment_date?->format('Y-m-d'),
                'status'        => $installmentSchedule->status,
                'notes'         => $installmentSchedule->notes,
            ],
            'assignments' => StudentInstallmentAssignment::with([
                'studentEnrollment.student:id,student_name',
                'installmentPlan:id,plan_name',
            ])->get()->map(fn($a) => [
                'id' => $a->id,
                'student_enrollment' => [
                    'student' => $a->studentEnrollment?->student,
                ],
                'installment_plan' => $a->installmentPlan,
            ]),
        ]);
    }

    private function getMobileSchedules(Request $request)
    {
        $query = InstallmentSchedule::with(['assignment.studentEnrollment.student', 'assignment.installmentPlan', 'payment']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kist_amount', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $schedules = $query->orderBy('due_date')->paginate($perPage);

        return response()->json($schedules);
    }

    private function getDataTablesSchedules(Request $request)
    {
        $query = InstallmentSchedule::with(['assignment.studentEnrollment.student', 'assignment.installmentPlan', 'payment']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('kist_amount', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'asc');
        $columns     = ['id', 'kist_number', 'kist_amount', 'due_date', 'paid_amount', 'status'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start     = $request->input('start', 0);
        $length    = $request->input('length', 10);
        $schedules = $query->skip($start)->take($length)->get();

        $data = $schedules->map(function ($schedule, $index) use ($start) {
            $statusClass = match($schedule->status) {
                'paid'    => 'bg-green-100 text-green-800',
                'pending' => 'bg-yellow-100 text-yellow-800',
                'overdue' => 'bg-red-100 text-red-800',
                default   => 'bg-gray-100 text-gray-800',
            };

            return [
                'DT_RowIndex'  => $start + $index + 1,
                'id'           => $schedule->id,
                'student_name' => $schedule->assignment?->studentEnrollment?->student?->student_name ?? '-',
                'kist_number'  => 'Kist #' . $schedule->kist_number,
                'kist_amount'  => number_format($schedule->kist_amount, 2),
                'due_date'     => $schedule->due_date?->format('d M, Y') ?? '-',
                'paid_amount'  => number_format($schedule->paid_amount ?? 0, 2),
                'payment_date' => $schedule->payment_date?->format('d M, Y') ?? '-',
                'status'       => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($schedule->status) . '</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editSchedule(' . json_encode(['id' => $schedule->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <button onclick="deleteSchedule(' . $schedule->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'assignment_id' => 'required|exists:student_installment_assignments,id',
            'kist_number'   => 'required|integer|min:1',
            'kist_amount'   => 'required|numeric|min:0',
            'due_date'      => 'required|date',
            'status'        => 'nullable|string|in:pending,paid,overdue',
            'notes'         => 'nullable|string',
        ]);

        InstallmentSchedule::create($validated);

        return redirect()->route('installment-schedules.index')
            ->with('success', 'Installment schedule created successfully!');
    }

    public function update(Request $request, InstallmentSchedule $installmentSchedule)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:student_installment_assignments,id',
            'kist_number'   => 'required|integer|min:1',
            'kist_amount'   => 'required|numeric|min:0',
            'due_date'      => 'required|date',
            'paid_amount'   => 'nullable|numeric|min:0',
            'payment_date'  => 'nullable|date',
            'status'        => 'nullable|string|in:pending,paid,overdue',
            'notes'         => 'nullable|string',
        ]);

        $installmentSchedule->update($validated);

        return redirect()->route('installment-schedules.index')
            ->with('success', 'Installment schedule updated successfully!');
    }

    public function destroy(InstallmentSchedule $installmentSchedule)
    {
        $installmentSchedule->delete();

        return back()->with('success', 'Installment schedule deleted successfully!');
    }
}
