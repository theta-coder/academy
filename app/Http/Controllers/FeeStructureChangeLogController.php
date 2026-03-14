<?php

namespace App\Http\Controllers;

use App\Models\FeeStructureChangeLog;
use App\Models\FeeStructure;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\FeeType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FeeStructureChangeLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileLogs($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesLogs($request);
        }

        return Inertia::render('FeeStructureChangeLogs/Index');
    }

    public function show(FeeStructureChangeLog $feeStructureChangeLog)
    {
        $feeStructureChangeLog->load(['feeStructure', 'changedBy']);

        return Inertia::render('FeeStructureChangeLogs/Show', [
            'log' => $feeStructureChangeLog,
        ]);
    }

    private function getMobileLogs(Request $request)
    {
        $query = FeeStructureChangeLog::with(['feeStructure', 'changedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('change_reason', 'like', "%{$search}%")
                  ->orWhereHas('feeStructure', function ($sq) use ($search) {
                      $sq->whereHas('feeType', fn($q) => $q->where('fee_name', 'like', "%{$search}%"));
                  });
            });
        }

        if ($request->filled('fee_structure_id')) {
            $query->where('fee_structure_id', $request->fee_structure_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $perPage = $request->get('per_page', 10);
        $logs = $query->latest('changed_at')->paginate($perPage);

        return response()->json($logs);
    }

    private function getDataTablesLogs(Request $request)
    {
        $query = FeeStructureChangeLog::with(['feeStructure.feeType', 'feeStructure.branch', 'feeStructure.class', 'changedBy']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('change_reason', 'like', "%{$search}%")
                  ->orWhereHas('feeStructure', function ($sq) use ($search) {
                      $sq->whereHas('feeType', fn($q) => $q->where('fee_name', 'like', "%{$search}%"));
                  });
            });
        }

        if ($request->filled('fee_structure_id')) {
            $query->where('fee_structure_id', $request->fee_structure_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'fee_structure_id', 'old_amount', 'new_amount', 'change_reason', 'changed_at'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $logs   = $query->skip($start)->take($length)->get();

        $data = $logs->map(function ($log, $index) use ($start) {
            $changeClass = $log->new_amount > $log->old_amount
                ? 'bg-red-100 text-red-800'  // Fee increased (bad for students)
                : 'bg-green-100 text-green-800';  // Fee decreased (good for students)

            $changeIndicator = $log->new_amount > $log->old_amount ? '↑' : '↓';

            return [
                'DT_RowIndex'      => $start + $index + 1,
                'id'               => $log->id,
                'fee_type'         => $log->feeStructure?->feeType?->fee_name ?? '-',
                'branch_name'      => $log->feeStructure?->branch?->branch_name ?? '-',
                'class_name'       => $log->feeStructure?->class?->class_name ?? '-',
                'academic_year'    => $log->feeStructure?->academicYear?->year_name ?? '-',
                'old_amount'       => number_format($log->old_amount, 2),
                'new_amount'       => number_format($log->new_amount, 2),
                'change'           => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $changeClass . '">'
                                        . $changeIndicator . ' ' . number_format(abs($log->new_amount - $log->old_amount), 2)
                                     . '</span>',
                'due_day_change'   => ($log->old_due_day ?? '-') . ' → ' . ($log->new_due_day ?? '-'),
                'effective_from'   => $log->effective_from?->format('d M, Y') ?? '-',
                'change_reason'    => \Illuminate\Support\Str::limit($log->change_reason ?? '-', 40),
                'affects_existing' => $log->affects_existing_vouchers
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Yes</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">No</span>',
                'changed_by'       => $log->changedBy?->name ?? '-',
                'changed_at'       => $log->changed_at?->format('d M, Y h:i A') ?? '-',
                'action'           => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'viewLog(' . json_encode(['id' => $log->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View
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

    public function create()
    {
        $academicYears = AcademicYear::select('id', 'year_name')
            ->orderBy('start_date', 'desc')
            ->get();

        $branches = Branch::active()
            ->select('id', 'branch_name')
            ->orderBy('branch_name')
            ->get();

        $classes = Classes::active()
            ->select('id', 'class_name')
            ->orderBy('class_name')
            ->get();

        $feeTypes = FeeType::select('id', 'fee_name')
            ->orderBy('fee_name')
            ->get();

        $feeStructures = FeeStructure::with(['feeType', 'branch', 'class', 'academicYear'])
            ->active()
            ->get();

        return Inertia::render('FeeStructureChangeLogs/Create', [
            'academicYears' => $academicYears,
            'branches'      => $branches,
            'classes'       => $classes,
            'feeTypes'      => $feeTypes,
            'feeStructures' => $feeStructures,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fee_structure_id'        => 'required|exists:fee_structures,id',
            'branch_id'               => 'nullable|exists:branches,id',
            'class_id'                => 'nullable|exists:classes,id',
            'fee_type_id'             => 'nullable|exists:fee_types,id',
            'academic_year_id'        => 'nullable|exists:academic_years,id',
            'old_amount'              => 'required|numeric|min:0',
            'new_amount'              => 'required|numeric|min:0',
            'old_due_day'             => 'nullable|integer|min:1|max:31',
            'new_due_day'             => 'nullable|integer|min:1|max:31',
            'change_reason'           => 'required|string',
            'effective_from'          => 'required|date',
            'affects_existing_vouchers' => 'boolean',
            'changed_by'              => 'nullable|exists:users,id',
            'changed_at'              => 'nullable|date',
        ]);

        $validated['changed_by'] = auth()->id() ?? 1;
        $validated['changed_at'] = $validated['changed_at'] ?? now();

        DB::transaction(function () use ($validated) {
            // Create the change log
            FeeStructureChangeLog::create($validated);

            // Update the actual fee structure
            $feeStructure = FeeStructure::findOrFail($validated['fee_structure_id']);
            $feeStructure->update([
                'amount'       => $validated['new_amount'],
                'due_day'      => $validated['new_due_day'] ?? $feeStructure->due_day,
                'effective_from' => $validated['effective_from'],
            ]);
        });

        return redirect()->route('fee-structure-change-logs.index')
            ->with('success', 'Fee structure change logged and applied successfully!');
    }

    public function edit(FeeStructureChangeLog $feeStructureChangeLog)
    {
        $feeStructureChangeLog->load(['feeStructure', 'changedBy']);

        $academicYears = AcademicYear::select('id', 'year_name')
            ->orderBy('start_date', 'desc')
            ->get();

        $branches = Branch::active()
            ->select('id', 'branch_name')
            ->orderBy('branch_name')
            ->get();

        $classes = Classes::active()
            ->select('id', 'class_name')
            ->orderBy('class_name')
            ->get();

        $feeTypes = FeeType::select('id', 'fee_name')
            ->orderBy('fee_name')
            ->get();

        return Inertia::render('FeeStructureChangeLogs/Edit', [
            'log' => [
                'id'                      => $feeStructureChangeLog->id,
                'fee_structure_id'        => $feeStructureChangeLog->fee_structure_id,
                'branch_id'               => $feeStructureChangeLog->branch_id,
                'class_id'                => $feeStructureChangeLog->class_id,
                'fee_type_id'             => $feeStructureChangeLog->fee_type_id,
                'academic_year_id'        => $feeStructureChangeLog->academic_year_id,
                'old_amount'              => $feeStructureChangeLog->old_amount,
                'new_amount'              => $feeStructureChangeLog->new_amount,
                'old_due_day'             => $feeStructureChangeLog->old_due_day,
                'new_due_day'             => $feeStructureChangeLog->new_due_day,
                'change_reason'           => $feeStructureChangeLog->change_reason,
                'effective_from'          => $feeStructureChangeLog->effective_from?->format('Y-m-d'),
                'affects_existing_vouchers' => $feeStructureChangeLog->affects_existing_vouchers,
            ],
            'academicYears' => $academicYears,
            'branches'      => $branches,
            'classes'       => $classes,
            'feeTypes'      => $feeTypes,
        ]);
    }

    public function update(Request $request, FeeStructureChangeLog $feeStructureChangeLog)
    {
        $validated = $request->validate([
            'old_amount'              => 'required|numeric|min:0',
            'new_amount'              => 'required|numeric|min:0',
            'old_due_day'             => 'nullable|integer|min:1|max:31',
            'new_due_day'             => 'nullable|integer|min:1|max:31',
            'change_reason'           => 'required|string',
            'effective_from'          => 'required|date',
            'affects_existing_vouchers' => 'boolean',
        ]);

        $feeStructureChangeLog->update($validated);

        return redirect()->route('fee-structure-change-logs.index')
            ->with('success', 'Fee structure change log updated successfully!');
    }

    public function destroy(FeeStructureChangeLog $feeStructureChangeLog)
    {
        // Note: Deleting a change log doesn't revert the fee structure change
        // This is intentional for audit trail purposes
        // If you need to revert, create a new change with reversed values

        $feeStructureChangeLog->delete();

        return back()->with('success', 'Fee structure change log deleted successfully!');
    }

    public function getFeeStructureDetails($structureId)
    {
        $structure = FeeStructure::with(['feeType', 'branch', 'class', 'academicYear'])
            ->findOrFail($structureId);

        return response()->json([
            'id'               => $structure->id,
            'fee_type_id'      => $structure->fee_type_id,
            'branch_id'        => $structure->branch_id,
            'class_id'         => $structure->class_id,
            'academic_year_id' => $structure->academic_year_id,
            'amount'           => $structure->amount,
            'due_day'          => $structure->due_day,
            'fee_type_name'    => $structure->feeType?->fee_name ?? '-',
            'branch_name'      => $structure->branch?->branch_name ?? '-',
            'class_name'       => $structure->class?->class_name ?? '-',
            'academic_year'    => $structure->academicYear?->year_name ?? '-',
        ]);
    }

    public function historyByStructure($structureId)
    {
        $logs = FeeStructureChangeLog::where('fee_structure_id', $structureId)
            ->with('changedBy')
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json($logs);
    }

    public function recentChanges(Request $request)
    {
        $query = FeeStructureChangeLog::with(['feeStructure.feeType', 'changedBy'])
            ->orderBy('changed_at', 'desc')
            ->limit(50);

        if ($request->filled('days')) {
            $query->where('changed_at', '>=', now()->subDays($request->days));
        }

        $changes = $query->get();

        return response()->json($changes);
    }
}
