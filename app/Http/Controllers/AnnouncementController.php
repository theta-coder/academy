<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Classes;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileAnnouncements($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesAnnouncements($request);
        }

        return Inertia::render('Announcements/Index');
    }

    public function create()
    {
        $branches = Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get();
        $classes = Classes::active()->ordered()->select('id', 'class_name')->get();

        return Inertia::render('Announcements/Create', [
            'branches' => $branches,
            'classes'  => $classes,
        ]);
    }

    public function edit(Announcement $announcement)
    {
        $branches = Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get();
        $classes = Classes::active()->ordered()->select('id', 'class_name')->get();

        return Inertia::render('Announcements/Edit', [
            'announcement' => [
                'id'                => $announcement->id,
                'branch_id'         => $announcement->branch_id,
                'title'             => $announcement->title,
                'content'           => $announcement->content,
                'image'             => $announcement->image,
                'target_audience'   => $announcement->target_audience,
                'target_class_id'   => $announcement->target_class_id,
                'priority'          => $announcement->priority,
                'start_date'        => $announcement->start_date?->format('Y-m-d'),
                'end_date'          => $announcement->end_date?->format('Y-m-d'),
                'show_on_dashboard' => $announcement->show_on_dashboard,
                'send_notification' => $announcement->send_notification,
                'send_sms'          => $announcement->send_sms,
                'send_email'        => $announcement->send_email,
                'status'            => $announcement->status,
            ],
            'branches' => $branches,
            'classes'  => $classes,
        ]);
    }

    private function getMobileAnnouncements(Request $request)
    {
        $query = Announcement::with(['branch', 'targetClass', 'createdBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 10);
        $announcements = $query->latest()->paginate($perPage);

        return response()->json($announcements);
    }

    private function getDataTablesAnnouncements(Request $request)
    {
        $query = Announcement::with(['branch', 'targetClass', 'createdBy']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'title', 'branch_id', 'priority', 'start_date', 'status'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start         = $request->input('start', 0);
        $length        = $request->input('length', 10);
        $announcements = $query->skip($start)->take($length)->get();

        $data = $announcements->map(function ($ann, $index) use ($start) {
            $statusClass = match($ann->status) {
                'published' => 'bg-green-100 text-green-800',
                'draft'     => 'bg-yellow-100 text-yellow-800',
                'expired'   => 'bg-gray-100 text-gray-800',
                default     => 'bg-gray-100 text-gray-800',
            };

            $priorityClass = match($ann->priority) {
                'high'   => 'bg-red-100 text-red-800',
                'medium' => 'bg-yellow-100 text-yellow-800',
                'low'    => 'bg-blue-100 text-blue-800',
                default  => 'bg-gray-100 text-gray-800',
            };

            return [
                'DT_RowIndex'     => $start + $index + 1,
                'id'              => $ann->id,
                'title'           => \Illuminate\Support\Str::limit($ann->title, 40),
                'branch_name'     => $ann->branch?->branch_name ?? 'All',
                'target_audience' => ucfirst($ann->target_audience ?? 'all'),
                'priority'        => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $priorityClass . '">' . ucfirst($ann->priority ?? 'normal') . '</span>',
                'start_date'      => $ann->start_date?->format('d M, Y') ?? '-',
                'views_count'     => $ann->views_count ?? 0,
                'status'          => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($ann->status) . '</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editAnnouncement(' . json_encode(['id' => $ann->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <button onclick="deleteAnnouncement(' . $ann->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'branch_id'         => 'nullable|exists:branches,id',
            'title'             => 'required|string|max:255',
            'content'           => 'required|string',
            'image'             => 'nullable|string',
            'target_audience'   => 'nullable|string|max:100',
            'target_class_id'   => 'nullable|exists:classes,id',
            'priority'          => 'nullable|string|in:low,medium,high',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'show_on_dashboard' => 'boolean',
            'send_notification' => 'boolean',
            'send_sms'          => 'boolean',
            'send_email'        => 'boolean',
            'status'            => 'nullable|string|in:draft,published',
        ]);

        $validated['created_by'] = auth()->id();

        Announcement::create($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'branch_id'         => 'nullable|exists:branches,id',
            'title'             => 'required|string|max:255',
            'content'           => 'required|string',
            'image'             => 'nullable|string',
            'target_audience'   => 'nullable|string|max:100',
            'target_class_id'   => 'nullable|exists:classes,id',
            'priority'          => 'nullable|string|in:low,medium,high',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'show_on_dashboard' => 'boolean',
            'send_notification' => 'boolean',
            'send_sms'          => 'boolean',
            'send_email'        => 'boolean',
            'status'            => 'nullable|string|in:draft,published',
        ]);

        $announcement->update($validated);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('success', 'Announcement deleted successfully!');
    }
}
