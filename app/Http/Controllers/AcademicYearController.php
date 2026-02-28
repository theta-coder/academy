<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{

    /**
     * Display a listing of academic years
     */
    public function index(Request $request)
    {
        // Check if it's a mobile request
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileYears($request);
        }

        // DataTables AJAX request
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesYears($request);
        }

        // For initial Inertia page load
        return Inertia::render('AcademicYears/Index');
    }

    /**
     * Show the form for creating a new academic year
     */
    public function create()
    {
        return Inertia::render('AcademicYears/Create');
    }

    /**
     * Show the form for editing the specified academic year
     */
    public function edit(AcademicYear $academicYear)
    {
        return Inertia::render('AcademicYears/Edit', [
            'academicYear' => [
                'id'         => $academicYear->id,
                'year_name'  => $academicYear->year_name,
                'start_date' => $academicYear->start_date->format('Y-m-d'),
                'end_date'   => $academicYear->end_date->format('Y-m-d'),
                'is_active'  => $academicYear->is_active,
            ]
        ]);
    }

    private function getMobileYears(Request $request)
    {
        $query = AcademicYear::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('year_name', 'like', "%{$search}%")
                  ->orWhere('start_date', 'like', "%{$search}%")
                  ->orWhere('end_date', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->get('per_page', 10);
        $years = $query->latest()->paginate($perPage);

        return response()->json($years);
    }

    private function getDataTablesYears(Request $request)
    {
        $query = AcademicYear::query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('year_name', 'like', "%{$search}%")
                  ->orWhere('start_date', 'like', "%{$search}%")
                  ->orWhere('end_date', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'year_name', 'start_date', 'end_date', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $years  = $query->skip($start)->take($length)->get();

        $data = $years->map(function ($year, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1,
                'id'          => $year->id,
                'year_name'   => $year->year_name,
                'start_date'  => $year->start_date->format('d M, Y'),
                'end_date'    => $year->end_date->format('d M, Y'),
                'duration'    => $year->start_date->diffInDays($year->end_date) . ' days',
                'is_active'   => $year->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        ' . (!$year->is_active ? '
                        <button onclick="activateYear(' . $year->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Activate
                        </button>' : '') . '
                        <button onclick=\'editYear(' . json_encode(['id' => $year->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteYear(' . $year->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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

    /**
     * Store a newly created year
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_name'  => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active'  => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
        }

        AcademicYear::create($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year created successfully!');
    }

    /**
     * Update the specified year
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'year_name'  => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'is_active'  => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            AcademicYear::where('is_active', true)
                ->where('id', '!=', $academicYear->id)
                ->update(['is_active' => false]);
        }

        $academicYear->update($validated);

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic year updated successfully!');
    }

    /**
     * Remove the specified year
     */
    public function destroy(AcademicYear $academicYear)
{
    if ($academicYear->studentEnrollments()->count() > 0) {
        return back()->with('error', 'Cannot delete academic year with existing enrollments!');
    }

    $academicYear->delete();

    return back()->with('success', 'Academic year deleted successfully!');
}

    /**
     * Activate a year
     */
    public function activate(AcademicYear $academicYear)
    {
        AcademicYear::where('is_active', true)->update(['is_active' => false]);
        $academicYear->update(['is_active' => true]);

        return back()->with('success', 'Academic year activated successfully!');
    }

    /**
     * Get current active year
     */
    public function current()
    {
        $currentYear = AcademicYear::active()->first();

        if (!$currentYear) {
            return response()->json(['message' => 'No active academic year found'], 404);
        }

        return response()->json($currentYear);
    }

    /**
     * Get years for dropdown (API endpoint)
     */
    public function dropdown(Request $request)
    {
        $query = AcademicYear::query();

        if (!$request->filled('include_all')) {
            $query->where('is_active', true);
        }

        $years = $query
            ->select('id', 'year_name', 'start_date', 'end_date', 'is_active')
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json($years);
    }
}