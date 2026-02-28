<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileTimetables($request);
        }
        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesTimetables($request);
        }
        return Inertia::render('Timetables/Index');
    }

    public function create()
    {
        return Inertia::render('Timetables/Create', [
            'branches' => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
            'classes'  => Classes::active()->ordered()->select('id', 'class_name')->get(),
            'sections' => Section::select('id', 'section_name')->orderBy('section_name')->get(),
            'subjects' => Subject::select('id', 'subject_name')->orderBy('subject_name')->get(),
            'teachers' => Teacher::select('id', 'first_name', 'last_name')->orderBy('first_name')->get(),
        ]);
    }

    public function edit(Timetable $timetable)
    {
        return Inertia::render('Timetables/Edit', [
            'timetable' => [
                'id'            => $timetable->id,
                'branch_id'     => $timetable->branch_id,
                'class_id'      => $timetable->class_id,
                'section_id'    => $timetable->section_id,
                'subject_id'    => $timetable->subject_id,
                'teacher_id'    => $timetable->teacher_id,
                'day_of_week'   => $timetable->day_of_week,
                'period_number' => $timetable->period_number,
                'start_time'    => $timetable->start_time?->format('H:i'),
                'end_time'      => $timetable->end_time?->format('H:i'),
                'room_no'       => $timetable->room_no,
                'is_break'      => $timetable->is_break,
                'status'        => $timetable->status,
            ],
            'branches' => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
            'classes'  => Classes::active()->ordered()->select('id', 'class_name')->get(),
            'sections' => Section::select('id', 'section_name')->orderBy('section_name')->get(),
            'subjects' => Subject::select('id', 'subject_name')->orderBy('subject_name')->get(),
            'teachers' => Teacher::select('id', 'first_name', 'last_name')->orderBy('first_name')->get(),
        ]);
    }

    private function getMobileTimetables(Request $request)
    {
        $query = Timetable::with(['branch', 'class', 'section', 'subject', 'teacher']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('day_of_week', 'like', "%{$search}%")->orWhere('room_no', 'like', "%{$search}%");
            });
        }
        if ($request->filled('day_of_week')) { $query->where('day_of_week', $request->day_of_week); }
        if ($request->filled('class_id')) { $query->where('class_id', $request->class_id); }
        return response()->json($query->orderBy('day_of_week')->orderBy('period_number')->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesTimetables(Request $request)
    {
        $query = Timetable::with(['branch', 'class', 'section', 'subject', 'teacher']);
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('day_of_week', 'like', "%{$search}%")->orWhere('room_no', 'like', "%{$search}%");
            });
        }
        if ($request->filled('day_of_week')) { $query->where('day_of_week', $request->day_of_week); }

        $totalData = $query->count();
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'day_of_week', 'period_number', 'class_id', 'subject_id', 'teacher_id', 'status'];
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $timetables = $query->skip($start)->take($length)->get();

        $data = $timetables->map(function ($tt, $index) use ($start) {
            $statusClass = $tt->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
            return [
                'DT_RowIndex'   => $start + $index + 1, 'id' => $tt->id,
                'day_of_week'   => ucfirst($tt->day_of_week),
                'period_number' => $tt->is_break ? 'Break' : 'Period ' . $tt->period_number,
                'class_section' => ($tt->class?->class_name ?? '-') . ' - ' . ($tt->section?->section_name ?? '-'),
                'subject_name'  => $tt->is_break ? 'Break' : ($tt->subject?->subject_name ?? '-'),
                'teacher_name'  => $tt->teacher?->first_name ?? '-',
                'time'          => ($tt->start_time?->format('h:i A') ?? '') . ' - ' . ($tt->end_time?->format('h:i A') ?? ''),
                'room_no'       => $tt->room_no ?? '-',
                'status'        => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($tt->status) . '</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editTimetable(' . json_encode(['id' => $tt->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>
                        <button onclick="deleteTimetable(' . $tt->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button>
                    </div>'
            ];
        });

        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'     => 'required|exists:branches,id',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'required|exists:sections,id',
            'subject_id'    => 'nullable|exists:subjects,id',
            'teacher_id'    => 'nullable|exists:teachers,id',
            'day_of_week'   => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'period_number' => 'required|integer|min:1',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'room_no'       => 'nullable|string|max:50',
            'is_break'      => 'boolean',
            'status'        => 'nullable|string|in:active,inactive',
        ]);
        Timetable::create($validated);
        return redirect()->route('timetables.index')->with('success', 'Timetable entry created successfully!');
    }

    public function update(Request $request, Timetable $timetable)
    {
        $validated = $request->validate([
            'branch_id'     => 'required|exists:branches,id',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'required|exists:sections,id',
            'subject_id'    => 'nullable|exists:subjects,id',
            'teacher_id'    => 'nullable|exists:teachers,id',
            'day_of_week'   => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'period_number' => 'required|integer|min:1',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'room_no'       => 'nullable|string|max:50',
            'is_break'      => 'boolean',
            'status'        => 'nullable|string|in:active,inactive',
        ]);
        $timetable->update($validated);
        return redirect()->route('timetables.index')->with('success', 'Timetable entry updated successfully!');
    }

    public function destroy(Timetable $timetable)
    {
        $timetable->delete();
        return back()->with('success', 'Timetable entry deleted successfully!');
    }
}
