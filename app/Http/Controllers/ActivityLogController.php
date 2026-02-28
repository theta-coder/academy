<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    /**
     * Read-only controller — Activity logs are system-generated
     */
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileLogs($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesLogs($request); }
        return Inertia::render('ActivityLogs/Index');
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load(['user', 'branch']);
        return Inertia::render('ActivityLogs/Show', ['log' => $activityLog]);
    }

    private function getMobileLogs(Request $request)
    {
        $query = ActivityLog::with(['user', 'branch']);
        if ($request->filled('search')) { $query->where(function ($q) use ($request) { $q->where('action', 'like', "%{$request->search}%")->orWhere('module', 'like', "%{$request->search}%")->orWhere('description', 'like', "%{$request->search}%"); }); }
        if ($request->filled('module')) { $query->where('module', $request->module); }
        if ($request->filled('user_id')) { $query->where('user_id', $request->user_id); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesLogs(Request $request)
    {
        $query = ActivityLog::with(['user', 'branch']);
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('action', 'like', "%{$search}%")->orWhere('module', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"); }); }
        if ($request->filled('module')) { $query->where('module', $request->module); }
        if ($request->filled('user_id')) { $query->where('user_id', $request->user_id); }

        $totalData = $query->count();
        $columns = ['id', 'user_id', 'module', 'action', 'created_at'];
        $orderColumn = $request->input('order.0.column', 0); $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $logs = $query->skip($start)->take($length)->get();

        $data = $logs->map(function ($log, $index) use ($start) {
            $actionClass = match($log->action) {
                'created' => 'bg-green-100 text-green-800', 'updated' => 'bg-blue-100 text-blue-800',
                'deleted' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-800',
            };
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $log->id,
                'user_name' => $log->user?->name ?? 'System',
                'module' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">' . ucfirst($log->module) . '</span>',
                'action' => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $actionClass . '">' . ucfirst($log->action) . '</span>',
                'description' => \Illuminate\Support\Str::limit($log->description, 50),
                'ip_address' => $log->ip_address ?? '-',
                'created_at' => $log->created_at?->format('d M, Y H:i') ?? '-',
                'action_buttons' => '<div class="flex items-center justify-center"><button onclick=\'viewLog(' . json_encode(['id' => $log->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>View</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }
}
