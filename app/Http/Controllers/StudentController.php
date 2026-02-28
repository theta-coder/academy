<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Parents;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of students
     */
    public function index(Request $request)
    {
        // Mobile pagination request
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileStudents($request);
        }

        // DataTables AJAX request
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesStudents($request);
        }

        // For initial Inertia page load
        return Inertia::render('Students/Index');
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        return Inertia::render('Students/Create', [
            'parents' => Parents::active()->select('id', 'father_name', 'father_phone')->orderBy('father_name')->get(),
        ]);
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        return Inertia::render('Students/Edit', [
            'student' => [
                'id'                => $student->id,
                'admission_no'      => $student->admission_no,
                'parent_id'         => $student->parent_id,
                'student_name'      => $student->student_name,
                'date_of_birth'     => $student->date_of_birth?->format('Y-m-d'),
                'gender'            => $student->gender,
                'photo'             => $student->photo,
                'whatsapp_number'   => $student->whatsapp_number,
                'b_form_no'         => $student->b_form_no,
                'blood_group'       => $student->blood_group,
                'religion'          => $student->religion,
                'is_hafiz'          => $student->is_hafiz,
                'student_type'      => $student->student_type,
                'previous_school'   => $student->previous_school,
                'medical_condition' => $student->medical_condition,
                'is_active'         => $student->is_active,
            ],
            'parents' => Parents::active()->select('id', 'father_name', 'father_phone')->orderBy('father_name')->get(),
        ]);
    }

    private function getMobileStudents(Request $request)
    {
        $query = Student::with(['parent']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('b_form_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('student_type')) {
            $query->where('student_type', $request->student_type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $perPage = $request->get('per_page', 10);
        $students = $query->latest()->paginate($perPage);

        return response()->json($students);
    }

    private function getDataTablesStudents(Request $request)
    {
        $query = Student::with(['parent']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('b_form_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('student_type')) {
            $query->where('student_type', $request->student_type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'admission_no', 'student_name', 'date_of_birth', 'gender', 'student_type', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start    = $request->input('start', 0);
        $length   = $request->input('length', 10);
        $students = $query->skip($start)->take($length)->get();

        $data = $students->map(function ($student, $index) use ($start) {
            $typeClass = match($student->student_type) {
                'school'  => 'bg-blue-100 text-blue-800',
                'academy' => 'bg-purple-100 text-purple-800',
                'both'    => 'bg-indigo-100 text-indigo-800',
                default   => 'bg-gray-100 text-gray-800',
            };

            return [
                'DT_RowIndex'   => $start + $index + 1,
                'id'            => $student->id,
                'admission_no'  => $student->admission_no ?? '-',
                'student_name'  => $student->student_name,
                'father_name'   => $student->parent?->father_name ?? '-',
                'date_of_birth' => $student->date_of_birth?->format('d M, Y') ?? '-',
                'gender'        => ucfirst($student->gender ?? '-'),
                'student_type'  => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $typeClass . '">' . ucfirst($student->student_type ?? '-') . '</span>',
                'is_hafiz'      => $student->is_hafiz
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Yes</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">No</span>',
                'is_active'     => $student->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editStudent(' . json_encode(['id' => $student->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteStudent(' . $student->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
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
     * Store a newly created student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'admission_no'      => 'nullable|string|max:50|unique:students,admission_no',
            'parent_id'         => 'required|exists:parents,id',
            'student_name'      => 'required|string|max:150',
            'date_of_birth'     => 'required|date',
            'gender'            => 'required|string|in:male,female,other',
            'photo'             => 'nullable|string',
            'whatsapp_number'   => 'nullable|string|max:20',
            'b_form_no'         => 'nullable|string|max:20',
            'blood_group'       => 'nullable|string|max:5',
            'religion'          => 'nullable|string|max:50',
            'is_hafiz'          => 'boolean',
            'student_type'      => 'required|string|in:school,academy,both',
            'previous_school'   => 'nullable|string|max:200',
            'medical_condition' => 'nullable|string',
            'is_active'         => 'boolean',
        ]);

        Student::create($validated);

        return redirect()->route('students.index')
            ->with('success', 'Student created successfully!');
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'admission_no'      => 'nullable|string|max:50|unique:students,admission_no,' . $student->id,
            'parent_id'         => 'required|exists:parents,id',
            'student_name'      => 'required|string|max:150',
            'date_of_birth'     => 'required|date',
            'gender'            => 'required|string|in:male,female,other',
            'photo'             => 'nullable|string',
            'whatsapp_number'   => 'nullable|string|max:20',
            'b_form_no'         => 'nullable|string|max:20',
            'blood_group'       => 'nullable|string|max:5',
            'religion'          => 'nullable|string|max:50',
            'is_hafiz'          => 'boolean',
            'student_type'      => 'required|string|in:school,academy,both',
            'previous_school'   => 'nullable|string|max:200',
            'medical_condition' => 'nullable|string',
            'is_active'         => 'boolean',
        ]);

        $student->update($validated);

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully!');
    }

    /**
     * Remove the specified student (soft delete)
     */
    public function destroy(Student $student)
    {
        if ($student->enrollments()->count() > 0) {
            return back()->with('error', 'Cannot delete student with existing enrollments!');
        }

        $student->delete();

        return back()->with('success', 'Student deleted successfully!');
    }

    /**
     * Get students for dropdown (API endpoint)
     */
    public function dropdown(Request $request)
    {
        $query = Student::active();

        if ($request->filled('student_type')) {
            $query->where('student_type', $request->student_type);
        }

        $students = $query
            ->select('id', 'admission_no', 'student_name', 'student_type')
            ->orderBy('student_name')
            ->get();

        return response()->json($students);
    }
}
