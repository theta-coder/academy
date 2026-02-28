<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileMessages($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesMessages($request);
        }

        return Inertia::render('Messages/Index');
    }

    public function create()
    {
        return Inertia::render('Messages/Create');
    }

    public function show(Message $message)
    {
        $message->load(['sender', 'receiver', 'replies.sender']);
        $message->markAsRead();

        return Inertia::render('Messages/Show', [
            'message' => $message
        ]);
    }

    private function getMobileMessages(Request $request)
    {
        $userId = auth()->id();
        $query = Message::with(['sender', 'receiver']);

        $query->where(function ($q) use ($userId) {
            $q->where('receiver_id', $userId)->where('is_deleted_by_receiver', false)
              ->orWhere(function ($q2) use ($userId) {
                  $q2->where('sender_id', $userId)->where('is_deleted_by_sender', false);
              });
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        $perPage = $request->get('per_page', 10);
        $messages = $query->latest()->paginate($perPage);

        return response()->json($messages);
    }

    private function getDataTablesMessages(Request $request)
    {
        $userId = auth()->id();
        $query = Message::with(['sender', 'receiver']);

        $query->where(function ($q) use ($userId) {
            $q->where('receiver_id', $userId)->where('is_deleted_by_receiver', false)
              ->orWhere(function ($q2) use ($userId) {
                  $q2->where('sender_id', $userId)->where('is_deleted_by_sender', false);
              });
        });

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'sender_id', 'receiver_id', 'subject', 'is_read', 'created_at'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start    = $request->input('start', 0);
        $length   = $request->input('length', 10);
        $messages = $query->skip($start)->take($length)->get();

        $data = $messages->map(function ($msg, $index) use ($start) {
            return [
                'DT_RowIndex'   => $start + $index + 1,
                'id'            => $msg->id,
                'sender_name'   => $msg->sender?->name ?? '-',
                'receiver_name' => $msg->receiver?->name ?? '-',
                'subject'       => \Illuminate\Support\Str::limit($msg->subject, 40),
                'is_read'       => $msg->is_read
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Read</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Unread</span>',
                'is_starred'    => $msg->is_starred ? '⭐' : '',
                'created_at'    => $msg->created_at?->format('d M, Y H:i') ?? '-',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'viewMessage(' . json_encode(['id' => $msg->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            View
                        </button>
                        <button onclick="deleteMessage(' . $msg->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
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
            'receiver_id'       => 'required|exists:users,id',
            'subject'           => 'required|string|max:255',
            'message'           => 'required|string',
            'attachments'       => 'nullable|array',
            'parent_message_id' => 'nullable|exists:messages,id',
        ]);

        $validated['sender_id'] = auth()->id();

        Message::create($validated);

        return redirect()->route('messages.index')
            ->with('success', 'Message sent successfully!');
    }

    public function destroy(Message $message)
    {
        $userId = auth()->id();

        if ($message->sender_id === $userId) {
            $message->update(['is_deleted_by_sender' => true]);
        }

        if ($message->receiver_id === $userId) {
            $message->update(['is_deleted_by_receiver' => true]);
        }

        return back()->with('success', 'Message deleted successfully!');
    }

    public function toggleStar(Message $message)
    {
        $message->toggleStar();

        return back()->with('success', 'Message star toggled!');
    }

    public function markAsRead(Message $message)
    {
        $message->markAsRead();

        return response()->json(['success' => true]);
    }
}
