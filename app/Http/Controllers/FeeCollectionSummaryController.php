<?php

namespace App\Http\Controllers;

use App\Models\FeeCollectionSummary;
use App\Models\Branch;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeCollectionSummaryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileSummaries($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesSummaries($request);
        }

        return Inertia::render('FeeCollectionSummaries/Index');
    }

    public function create()
    {
        $branches = Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get();
        $academicYears = AcademicYear::select('id', 'year_name')->orderBy('start_date', 'desc')->get();

        return Inertia::render('FeeCollectionSummaries/Create', [
            'branches'      => $branches,
            'academicYears' => $academicYears,
        ]);
    }

    public function edit(FeeCollectionSummary $feeCollectionSummary)
    {
        $branches = Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get();
        $academicYears = AcademicYear::select('id', 'year_name')->orderBy('start_date', 'desc')->get();

        return Inertia::render('FeeCollectionSummaries/Edit', [
            'summary' => [
                'id'               => $feeCollectionSummary->id,
                'branch_id'        => $feeCollectionSummary->branch_id,
                'academic_year_id' => $feeCollectionSummary->academic_year_id,
                'summary_month'    => $feeCollectionSummary->summary_month,
                'summary_year'     => $feeCollectionSummary->summary_year,
                'total_students'   => $feeCollectionSummary->total_students,
                'total_billed'     => $feeCollectionSummary->total_billed,
                'total_discount'   => $feeCollectionSummary->total_discount,
                'total_fine'       => $feeCollectionSummary->total_fine,
                'total_net'        => $feeCollectionSummary->total_net,
                'total_collected'  => $feeCollectionSummary->total_collected,
                'total_pending'    => $feeCollectionSummary->total_pending,
                'total_waived'     => $feeCollectionSummary->total_waived,
                'vouchers_paid'    => $feeCollectionSummary->vouchers_paid,
                'vouchers_partial' => $feeCollectionSummary->vouchers_partial,
                'vouchers_pending' => $feeCollectionSummary->vouchers_pending,
            ],
            'branches'      => $branches,
            'academicYears' => $academicYears,
        ]);
    }

    private function getMobileSummaries(Request $request)
    {
        $query = FeeCollectionSummary::with(['branch', 'academicYear']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('summary_year', 'like', "%{$search}%")
                  ->orWhereHas('branch', function ($sq) use ($search) {
                      $sq->where('branch_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $perPage = $request->get('per_page', 10);
        $summaries = $query->latest()->paginate($perPage);

        return response()->json($summaries);
    }

    private function getDataTablesSummaries(Request $request)
    {
        $query = FeeCollectionSummary::with(['branch', 'academicYear']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('summary_year', 'like', "%{$search}%")
                  ->orWhereHas('branch', function ($sq) use ($search) {
                      $sq->where('branch_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'branch_id', 'summary_month', 'summary_year', 'total_collected', 'total_pending'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start     = $request->input('start', 0);
        $length    = $request->input('length', 10);
        $summaries = $query->skip($start)->take($length)->get();

        $data = $summaries->map(function ($summary, $index) use ($start) {
            return [
                'DT_RowIndex'     => $start + $index + 1,
                'id'              => $summary->id,
                'branch_name'     => $summary->branch?->branch_name ?? '-',
                'academic_year'   => $summary->academicYear?->year_name ?? '-',
                'month_year'      => $summary->summary_month . '/' . $summary->summary_year,
                'total_collected'  => number_format($summary->total_collected, 2),
                'total_pending'    => number_format($summary->total_pending, 2),
                'total_students'   => $summary->total_students,
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editSummary(' . json_encode(['id' => $summary->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteSummary(' . $summary->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
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
            'branch_id'        => 'required|exists:branches,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'summary_month'    => 'required|integer|min:1|max:12',
            'summary_year'     => 'required|integer',
            'total_students'   => 'nullable|integer|min:0',
            'total_billed'     => 'nullable|numeric|min:0',
            'total_discount'   => 'nullable|numeric|min:0',
            'total_fine'       => 'nullable|numeric|min:0',
            'total_net'        => 'nullable|numeric|min:0',
            'total_collected'  => 'nullable|numeric|min:0',
            'total_pending'    => 'nullable|numeric|min:0',
            'total_waived'     => 'nullable|numeric|min:0',
            'vouchers_paid'    => 'nullable|integer|min:0',
            'vouchers_partial' => 'nullable|integer|min:0',
            'vouchers_pending' => 'nullable|integer|min:0',
        ]);

        $validated['generated_at'] = now();

        FeeCollectionSummary::create($validated);

        return redirect()->route('fee-collection-summaries.index')
            ->with('success', 'Fee collection summary created successfully!');
    }

    public function update(Request $request, FeeCollectionSummary $feeCollectionSummary)
    {
        $validated = $request->validate([
            'branch_id'        => 'required|exists:branches,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'summary_month'    => 'required|integer|min:1|max:12',
            'summary_year'     => 'required|integer',
            'total_students'   => 'nullable|integer|min:0',
            'total_billed'     => 'nullable|numeric|min:0',
            'total_discount'   => 'nullable|numeric|min:0',
            'total_fine'       => 'nullable|numeric|min:0',
            'total_net'        => 'nullable|numeric|min:0',
            'total_collected'  => 'nullable|numeric|min:0',
            'total_pending'    => 'nullable|numeric|min:0',
            'total_waived'     => 'nullable|numeric|min:0',
            'vouchers_paid'    => 'nullable|integer|min:0',
            'vouchers_partial' => 'nullable|integer|min:0',
            'vouchers_pending' => 'nullable|integer|min:0',
        ]);

        $feeCollectionSummary->update($validated);

        return redirect()->route('fee-collection-summaries.index')
            ->with('success', 'Fee collection summary updated successfully!');
    }

    public function destroy(FeeCollectionSummary $feeCollectionSummary)
    {
        $feeCollectionSummary->delete();

        return back()->with('success', 'Fee collection summary deleted successfully!');
    }
}
