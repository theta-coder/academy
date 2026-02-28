<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParentController extends Controller
{

    /**
     * Display a listing of parents
     */
    public function index(Request $request)
    {
        // Check if it's a mobile request
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileParents($request);
        }

        // DataTables AJAX request
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesParents($request);
        }

        // For initial Inertia page load
        return Inertia::render('Parents/Index');
    }

    /**
     * Show the form for creating a new parent
     */
    public function create()
    {
        return Inertia::render('Parents/Create');
    }

    /**
     * Show the form for editing the specified parent
     */
    public function edit(Parents $parent)
    {
        return Inertia::render('Parents/Edit', [
            'parent' => [
                'id'                     => $parent->id,
                'father_name'            => $parent->father_name,
                'father_cnic'            => $parent->father_cnic,
                'father_phone'           => $parent->father_phone,
                'father_occupation'      => $parent->father_occupation,
                'mother_name'            => $parent->mother_name,
                'mother_cnic'            => $parent->mother_cnic,
                'mother_phone'           => $parent->mother_phone,
                'address'                => $parent->address,
                'city'                   => $parent->city,
                'emergency_contact_name' => $parent->emergency_contact_name,
                'emergency_contact_phone'=> $parent->emergency_contact_phone,
                'is_active'              => $parent->is_active,
            ]
        ]);
    }

    private function getMobileParents(Request $request)
    {
        $query = Parents::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('father_name', 'like', "%{$search}%")
                  ->orWhere('mother_name', 'like', "%{$search}%")
                  ->orWhere('father_phone', 'like', "%{$search}%")
                  ->orWhere('father_cnic', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->get('per_page', 10);
        $parents = $query->latest()->paginate($perPage);

        return response()->json($parents);
    }

    private function getDataTablesParents(Request $request)
    {
        $query = Parents::query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('father_name', 'like', "%{$search}%")
                  ->orWhere('mother_name', 'like', "%{$search}%")
                  ->orWhere('father_phone', 'like', "%{$search}%")
                  ->orWhere('father_cnic', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'father_name', 'father_cnic', 'father_phone', 'mother_name', 'city', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $parents = $query->skip($start)->take($length)->get();

        $data = $parents->map(function ($parent, $index) use ($start) {
            return [
                'DT_RowIndex'            => $start + $index + 1,
                'id'                     => $parent->id,
                'father_name'            => $parent->father_name,
                'father_cnic'            => $parent->father_cnic ?? '-',
                'father_phone'           => $parent->father_phone ?? '-',
                'father_occupation'      => $parent->father_occupation ?? '-',
                'mother_name'            => $parent->mother_name ?? '-',
                'mother_phone'           => $parent->mother_phone ?? '-',
                'city'                   => $parent->city ?? '-',
                'emergency_contact_name' => $parent->emergency_contact_name ?? '-',
                'total_children'         => $parent->students()->count(),
                'is_active' => $parent->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editParent(' . json_encode(['id' => $parent->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteParent(' . $parent->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
     * Store a newly created parent
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'father_name'            => 'required|string|max:100',
            'father_cnic'            => 'required|string|max:20|unique:parents,father_cnic',
            'father_phone'           => 'required|string|max:20',
            'father_occupation'      => 'nullable|string|max:100',
            'mother_name'            => 'nullable|string|max:100',
            'mother_cnic'            => 'nullable|string|max:20',
            'mother_phone'           => 'nullable|string|max:20',
            'address'                => 'required|string',
            'city'                   => 'required|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone'=> 'nullable|string|max:20',
            'is_active'              => 'boolean',
        ]);

        Parents::create($validated);

        return redirect()->route('parents.index')
            ->with('success', 'Parent created successfully!');
    }

    /**
     * Update the specified parent
     */
    public function update(Request $request, Parents $parent)
    {
        $validated = $request->validate([
            'father_name'            => 'required|string|max:100',
            'father_cnic'            => 'required|string|max:20|unique:parents,father_cnic,' . $parent->id,
            'father_phone'           => 'required|string|max:20',
            'father_occupation'      => 'nullable|string|max:100',
            'mother_name'            => 'nullable|string|max:100',
            'mother_cnic'            => 'nullable|string|max:20',
            'mother_phone'           => 'nullable|string|max:20',
            'address'                => 'required|string',
            'city'                   => 'required|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone'=> 'nullable|string|max:20',
            'is_active'              => 'boolean',
        ]);

        $parent->update($validated);

        return redirect()->route('parents.index')
            ->with('success', 'Parent updated successfully!');
    }

    /**
     * Remove the specified parent
     */
    public function destroy(Parents $parent)
    {
        if ($parent->students()->count() > 0) {
            return back()->with('error', 'Cannot delete parent with existing children!');
        }

        $parent->delete();

        return back()->with('success', 'Parent deleted successfully!');
    }

    /**
     * Get parents for dropdown (API endpoint)
     */
    public function dropdown(Request $request)
    {
        $query = Parents::active()
            ->select('id', 'father_name', 'father_phone', 'mother_name');

        $parents = $query->orderBy('father_name')->get();

        return response()->json($parents);
    }
}