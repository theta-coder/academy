<?php

namespace App\Http\Controllers;

use App\Models\StudentLedger;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentLedgerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileLedger($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesLedger($request);
        }
        return Inertia::render('StudentLedgers/Index');
    }

    public function create() { return Inertia::render('StudentLedgers/Create'); }

    public function edit(StudentLedger $studentLedger)
    {
        $studentLedger->load(['studentEnrollment.student', 'createdBy']);
        return Inertia::render('StudentLedgers/Edit', [
            'ledger' => [
                'id'                    => $studentLedger->id,
                'student_enrollment_id' => $studentLedger->student_enrollment_id,
                'transaction_type'      => $studentLedger->transaction_type,
                'amount'                => $studentLedger->amount,
                'description'           => $studentLedger->description,
                'reference_type'        => $studentLedger->reference_type,
                'reference_id'          => $studentLedger->reference_id,
                'balance_after'         => $studentLedger->balance_after,
            ]
        ]);
    }

    private function getMobileLedger(Request $request)
    {
        $query = StudentLedger::with(['studentEnrollment.student', 'createdBy']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")->orWhere('transaction_type', 'like', "%{$search}%");
            });
        }
        if ($request->filled('student_enrollment_id')) { $query->where('student_enrollment_id', $request->student_enrollment_id); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesLedger(Request $request)
    {
        $query = StudentLedger::with(['studentEnrollment.student', 'createdBy']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")->orWhere('transaction_type', 'like', "%{$search}%");
            });
        }
        if ($request->filled('student_enrollment_id')) { $query->where('student_enrollment_id', $request->student_enrollment_id); }

        $totalData = $query->count();
        $columns = ['id', 'student_enrollment_id', 'transaction_type', 'amount', 'balance_after', 'created_at'];
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $entries = $query->skip($start)->take($length)->get();

        $data = $entries->map(function ($entry, $index) use ($start) {
            $typeClass = match($entry->transaction_type) {
                'debit' => 'bg-red-100 text-red-800', 'credit' => 'bg-green-100 text-green-800',
                default => 'bg-gray-100 text-gray-800',
            };
            return [
                'DT_RowIndex'      => $start + $index + 1, 'id' => $entry->id,
                'student_name'     => $entry->studentEnrollment?->student?->student_name ?? '-',
                'transaction_type' => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $typeClass . '">' . ucfirst($entry->transaction_type) . '</span>',
                'description'      => \Illuminate\Support\Str::limit($entry->description, 40),
                'amount'           => number_format($entry->amount, 2),
                'balance_after'    => number_format($entry->balance_after, 2),
                'date'             => $entry->created_at?->format('d M, Y') ?? '-',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editLedger(' . json_encode(['id' => $entry->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteLedger(' . $entry->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'transaction_type'      => 'required|string|in:debit,credit',
            'amount'                => 'required|numeric|min:0',
            'description'           => 'required|string|max:255',
            'reference_type'        => 'required|string|in:voucher,payment,refund,concession,adjustment',
            'reference_id'          => 'nullable|integer',
            'balance_after'         => 'required|numeric',
        ]);
        $validated['created_by'] = auth()->id();
        StudentLedger::create($validated);
        return redirect()->route('student-ledgers.index')->with('success', 'Ledger entry created successfully!');
    }

    public function update(Request $request, StudentLedger $studentLedger)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|string|in:debit,credit',
            'amount'           => 'required|numeric|min:0',
            'description'      => 'required|string|max:255',
            'balance_after'    => 'required|numeric',
        ]);
        $studentLedger->update($validated);
        return redirect()->route('student-ledgers.index')->with('success', 'Ledger entry updated successfully!');
    }

    public function destroy(StudentLedger $studentLedger)
    {
        $studentLedger->delete();
        return back()->with('success', 'Ledger entry deleted successfully!');
    }
}
