<?php

namespace App\Http\Controllers;

use App\Models\StudentAdvanceBalance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentAdvanceBalanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileBalances($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesBalances($request);
        }
        return Inertia::render('StudentAdvanceBalances/Index');
    }

    public function create() { return Inertia::render('StudentAdvanceBalances/Create'); }

    public function edit(StudentAdvanceBalance $studentAdvanceBalance)
    {
        $studentAdvanceBalance->load(['studentEnrollment.student']);
        return Inertia::render('StudentAdvanceBalances/Edit', [
            'balance' => [
                'id'                    => $studentAdvanceBalance->id,
                'student_enrollment_id' => $studentAdvanceBalance->student_enrollment_id,
                'balance'               => $studentAdvanceBalance->balance,
            ]
        ]);
    }

    private function getMobileBalances(Request $request)
    {
        $query = StudentAdvanceBalance::with(['studentEnrollment.student']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('studentEnrollment.student', fn($sq) => $sq->where('first_name', 'like', "%{$search}%"));
        }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesBalances(Request $request)
    {
        $query = StudentAdvanceBalance::with(['studentEnrollment.student']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('balance', 'like', "%{$search}%")
                  ->orWhereHas('studentEnrollment.student', fn($sq) => $sq->where('first_name', 'like', "%{$search}%"));
            });
        }

        $totalData = $query->count();
        $columns = ['id', 'student_enrollment_id', 'balance', 'updated_at'];
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $balances = $query->skip($start)->take($length)->get();

        $data = $balances->map(function ($bal, $index) use ($start) {
            return [
                'DT_RowIndex'  => $start + $index + 1, 'id' => $bal->id,
                'student_name' => $bal->studentEnrollment?->student?->first_name ?? '-',
                'balance'      => number_format($bal->balance, 2),
                'updated_at'   => $bal->updated_at?->format('d M, Y H:i') ?? '-',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editBalance(' . json_encode(['id' => $bal->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteBalance(' . $bal->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'balance'               => 'required|numeric|min:0',
        ]);
        StudentAdvanceBalance::create($validated);
        return redirect()->route('student-advance-balances.index')->with('success', 'Advance balance created successfully!');
    }

    public function update(Request $request, StudentAdvanceBalance $studentAdvanceBalance)
    {
        $validated = $request->validate([
            'balance' => 'required|numeric|min:0',
        ]);
        $studentAdvanceBalance->update($validated);
        return redirect()->route('student-advance-balances.index')->with('success', 'Advance balance updated successfully!');
    }

    public function destroy(StudentAdvanceBalance $studentAdvanceBalance)
    {
        $studentAdvanceBalance->delete();
        return back()->with('success', 'Advance balance deleted successfully!');
    }
}
