<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileNotifications($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesNotifications($request);
        }

        return Inertia::render('Notifications/Index');
    }

    public function create()
    {
        return Inertia::render('Notifications/Create');
    }

    public function edit(Notification $notification)
    {
        return Inertia::render('Notifications/Edit', [
            'notification' => [
                'id'         => $notification->id,
                'user_id'    => $notification->user_id,
                'branch_id'  => $notification->branch_id,
                'title'      => $notification->title,
                'message'    => $notification->message,
                'type'       => $notification->type,
                'priority'   => $notification->priority,
                'action_url' => $notification->action_url,
                'expires_at' => $notification->expires_at?->format('Y-m-d H:i'),
            ]
        ]);
    }

    private function getMobileNotifications(Request $request)
    {
        $query = Notification::with(['user', 'branch']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        $perPage = $request->get('per_page', 10);
        $notifications = $query->latest()->paginate($perPage);

        return response()->json($notifications);
    }

    private function getDataTablesNotifications(Request $request)
    {
        $query = Notification::with(['user', 'branch']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'title', 'user_id', 'type', 'priority', 'is_read', 'created_at'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start         = $request->input('start', 0);
        $length        = $request->input('length', 10);
        $notifications = $query->skip($start)->take($length)->get();

        $data = $notifications->map(function ($notif, $index) use ($start) {
            $priorityClass = match($notif->priority) {
                'urgent' => 'bg-red-100 text-red-800',
                'high'   => 'bg-orange-100 text-orange-800',
                'normal' => 'bg-blue-100 text-blue-800',
                'low'    => 'bg-gray-100 text-gray-800',
                default  => 'bg-gray-100 text-gray-800',
            };

            return [
                'DT_RowIndex' => $start + $index + 1,
                'id'          => $notif->id,
                'title'       => \Illuminate\Support\Str::limit($notif->title, 40),
                'user_name'   => $notif->user?->name ?? '-',
                'type'        => ucfirst($notif->type ?? '-'),
                'priority'    => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $priorityClass . '">' . ucfirst($notif->priority ?? 'normal') . '</span>',
                'is_read'     => $notif->is_read
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Read</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Unread</span>',
                'created_at'  => $notif->created_at?->format('d M, Y H:i') ?? '-',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editNotification(' . json_encode(['id' => $notif->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <button onclick="deleteNotification(' . $notif->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'user_id'    => 'required|exists:users,id',
            'branch_id'  => 'nullable|exists:branches,id',
            'title'      => 'required|string|max:255',
            'message'    => 'required|string',
            'type'       => 'nullable|string|max:100',
            'priority'   => 'nullable|string|in:low,normal,high,urgent',
            'action_url' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date',
        ]);

        $validated['sent_at'] = now();

        Notification::create($validated);

        return redirect()->route('notifications.index')
            ->with('success', 'Notification created successfully!');
    }

    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'branch_id'  => 'nullable|exists:branches,id',
            'title'      => 'required|string|max:255',
            'message'    => 'required|string',
            'type'       => 'nullable|string|max:100',
            'priority'   => 'nullable|string|in:low,normal,high,urgent',
            'action_url' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date',
        ]);

        $notification->update($validated);

        return redirect()->route('notifications.index')
            ->with('success', 'Notification updated successfully!');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return back()->with('success', 'Notification deleted successfully!');
    }

    public function markAsRead(Notification $notification)
    {
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', auth()->id())
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'All notifications marked as read!');
    }
}
