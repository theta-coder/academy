<?php

namespace App\Http\Controllers;

use App\Models\PreviousYearBalance;
use App\Models\StudentEnrollment;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PreviousYearBalanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileBalances($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesBalances($request);
        }

        return Inertia::render('PreviousYearBalances/Index');
    }

    public function create()
    {
        $students = Student::select('id', 'student_name', 'admission_no')
            ->orderBy('student_name')
            ->get();

        $academicYears = AcademicYear::select('id', 'year_name')
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('PreviousYearBalances/Create', [
            'students'        => $students,
            'academicYears'   => $academicYears,
        ]);
    }

    public function edit(PreviousYearBalance $previousYearBalance)
    {
        $previousYearBalance->load(['studentEnrollment.student', 'fromAcademicYear', 'toAcademicYear', 'carriedForwardBy']);

        $students = Student::select('id', 'student_name', 'admission_no')
            ->orderBy('student_name')
            ->get();

        $academicYears = AcademicYear::select('id', 'year_name')
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('PreviousYearBalances/Edit', [
            'balance' => [
                'id'                      => $previousYearBalance->id,
                'student_enrollment_id'   => $previousYearBalance->student_enrollment_id,
                'from_academic_year_id'   => $previousYearBalance->from_academic_year_id,
                'to_academic_year_id'     => $previousYearBalance->to_academic_year_id,
                'original_outstanding'    => $previousYearBalance->original_outstanding,
                'breakup'                 => $previousYearBalance->breakup,
                'recovered_amount'        => $previousYearBalance->recovered_amount,
                'remaining_amount'        => $previousYearBalance->remaining_amount,
                'carry_forward_date'      => $previousYearBalance->carry_forward_date?->format('Y-m-d'),
                'status'                  => $previousYearBalance->status,
                'notes'                   => $previousYearBalance->notes,
            ],
            'students'      => $students,
            'academicYears' => $academicYears,
        ]);
    }

    private function getMobileBalances(Request $request)
    {
        $query = PreviousYearBalance::with(['studentEnrollment.student', 'fromAcademicYear', 'toAcademicYear']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('student_enrollment_id')) {
            $query->where('student_enrollment_id', $request->student_enrollment_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_academic_year_id')) {
            $query->where('from_academic_year_id', $request->from_academic_year_id);
        }

        $perPage = $request->get('per_page', 10);
        $balances = $query->latest()->paginate($perPage);

        return response()->json($balances);
    }

    private function getDataTablesBalances(Request $request)
    {
        $query = PreviousYearBalance::with(['studentEnrollment.student', 'fromAcademicYear', 'toAcademicYear', 'carriedForwardBy']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('student_enrollment_id')) {
            $query->where('student_enrollment_id', $request->student_enrollment_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_academic_year_id')) {
            $query->where('from_academic_year_id', $request->from_academic_year_id);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'student_enrollment_id', 'original_outstanding', 'recovered_amount', 'remaining_amount', 'status', 'carry_forward_date'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start    = $request->input('start', 0);
        $length   = $request->input('length', 10);
        $balances = $query->skip($start)->take($length)->get();

        $data = $balances->map(function ($balance, $index) use ($start) {
            $statusClass = match ($balance->status) {
                'unpaid'   => 'bg-red-100 text-red-800',
                'partial'  => 'bg-yellow-100 text-yellow-800',
                'cleared'  => 'bg-green-100 text-green-800',
                default    => 'bg-gray-100 text-gray-800',
            };

            return [
                'DT_RowIndex'          => $start + $index + 1,
                'id'                   => $balance->id,
                'student_name'         => $balance->studentEnrollment?->student?->student_name ?? '-',
                'admission_no'         => $balance->studentEnrollment?->student?->admission_no ?? '-',
                'from_year'            => $balance->fromAcademicYear?->year_name ?? '-',
                'to_year'              => $balance->toAcademicYear?->year_name ?? '-',
                'original_outstanding' => number_format($balance->original_outstanding, 2),
                'recovered_amount'     => number_format($balance->recovered_amount, 2),
                'remaining_amount'     => number_format($balance->remaining_amount, 2),
                'status'               => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($balance->status) . '</span>',
                'carry_forward_date'   => $balance->carry_forward_date?->format('d M, Y') ?? '-',
                'carried_by'           => $balance->carriedForwardBy?->name ?? '-',
                'notes'                => \Illuminate\Support\Str::limit($balance->notes ?? '-', 30),
                'action'               => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'viewBalance(' . json_encode(['id' => $balance->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View
                        </button>
                        <button onclick=\'editBalance(' . json_encode(['id' => $balance->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteBalance(' . $balance->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                ',
            ];
        });

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_enrollment_id'   => 'required|exists:student_enrollments,id',
            'from_academic_year_id'   => 'required|exists:academic_years,id',
            'to_academic_year_id'     => 'required|exists:academic_years,id',
            'original_outstanding'    => 'required|numeric|min:0',
            'breakup'                 => 'nullable|array',
            'recovered_amount'        => 'nullable|numeric|min:0',
            'remaining_amount'        => 'required|numeric|min:0',
            'carry_forward_date'      => 'required|date',
            'status'                  => 'required|string|in:unpaid,partial,cleared',
            'notes'                   => 'nullable|string',
        ]);

        $validated['carry_forward_by'] = auth()->id() ?? 1;

        PreviousYearBalance::create($validated);

        return redirect()->route('previous-year-balances.index')
            ->with('success', 'Previous year balance carried forward successfully!');
    }

    public function update(Request $request, PreviousYearBalance $previousYearBalance)
    {
        $validated = $request->validate([
            'student_enrollment_id'   => 'required|exists:student_enrollments,id',
            'from_academic_year_id'   => 'required|exists:academic_years,id',
            'to_academic_year_id'     => 'required|exists:academic_years,id',
            'original_outstanding'    => 'required|numeric|min:0',
            'breakup'                 => 'nullable|array',
            'recovered_amount'        => 'nullable|numeric|min:0',
            'remaining_amount'        => 'required|numeric|min:0',
            'carry_forward_date'      => 'required|date',
            'status'                  => 'required|string|in:unpaid,partial,cleared',
            'notes'                   => 'nullable|string',
        ]);

        $previousYearBalance->update($validated);

        return redirect()->route('previous-year-balances.index')
            ->with('success', 'Previous year balance updated successfully!');
    }

    public function destroy(PreviousYearBalance $previousYearBalance)
    {
        $previousYearBalance->delete();

        return back()->with('success', 'Previous year balance deleted successfully!');
    }

    public function getEnrollmentsByStudent($studentId)
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

    public function studentBalances($studentId)
    {
        $balances = PreviousYearBalance::whereHas('studentEnrollment', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })
            ->with(['fromAcademicYear', 'toAcademicYear'])
            ->orderBy('from_academic_year_id', 'desc')
            ->get();

        return response()->json($balances);
    }

    public function carryForwardBulk(Request $request)
    {
        $validated = $request->validate([
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'to_academic_year_id'   => 'required|exists:academic_years,id',
            'student_ids'           => 'required|array',
            'carry_forward_date'    => 'required|date',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['student_ids'] as $studentId) {
                $enrollment = StudentEnrollment::where('student_id', $studentId)
                    ->where('academic_year_id', $validated['to_academic_year_id'])
                    ->first();

                if (!$enrollment) {
                    continue;
                }

                // Get unpaid balances from previous year
                $unpaidBalances = PreviousYearBalance::where('student_enrollment_id', $enrollment->id)
                    ->where('to_academic_year_id', $validated['from_academic_year_id'])
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->sum('remaining_amount');

                if ($unpaidBalances > 0) {
                    PreviousYearBalance::create([
                        'student_enrollment_id'   => $enrollment->id,
                        'from_academic_year_id'   => $validated['from_academic_year_id'],
                        'to_academic_year_id'     => $validated['to_academic_year_id'],
                        'original_outstanding'    => $unpaidBalances,
                        'recovered_amount'        => 0,
                        'remaining_amount'        => $unpaidBalances,
                        'carry_forward_date'      => $validated['carry_forward_date'],
                        'carry_forward_by'        => auth()->id() ?? 1,
                        'status'                  => 'unpaid',
                    ]);
                }
            }
        });

        return back()->with('success', 'Balances carried forward successfully!');
    }

    public function recordRecovery(Request $request, PreviousYearBalance $previousYearBalance)
    {
        $validated = $request->validate([
            'recovered_amount' => 'required|numeric|min:0',
            'notes'            => 'nullable|string',
        ]);

        $previousYearBalance->recovered_amount += $validated['recovered_amount'];
        $previousYearBalance->remaining_amount = max(0, $previousYearBalance->remaining_amount - $validated['recovered_amount']);

        if ($previousYearBalance->remaining_amount <= 0) {
            $previousYearBalance->status = 'cleared';
        } elseif ($previousYearBalance->recovered_amount > 0) {
            $previousYearBalance->status = 'partial';
        }

        $previousYearBalance->save();

        return back()->with('success', 'Recovery recorded successfully!');
    }

    public function summary(Request $request)
    {
        $summary = PreviousYearBalance::select('status', DB::raw('SUM(original_outstanding) as total_original'), DB::raw('SUM(recovered_amount) as total_recovered'), DB::raw('SUM(remaining_amount) as total_remaining'), DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json($summary);
    }
}
