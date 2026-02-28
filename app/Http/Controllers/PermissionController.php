<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobilePermissions($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesPermissions($request); }
        return Inertia::render('Permissions/Index');
    }

    public function create() { return Inertia::render('Permissions/Create'); }

    public function edit(Permission $permission)
    {
        return Inertia::render('Permissions/Edit', [
            'permission' => [
                'id' => $permission->id, 'module' => $permission->module, 'permission_key' => $permission->permission_key,
                'display_name' => $permission->display_name, 'description' => $permission->description,
            ]
        ]);
    }

    private function getMobilePermissions(Request $request)
    {
        $query = Permission::query();
        if ($request->filled('search')) { $query->where('display_name', 'like', "%{$request->search}%")->orWhere('module', 'like', "%{$request->search}%"); }
        if ($request->filled('module')) { $query->where('module', $request->module); }
        return response()->json($query->orderBy('module')->orderBy('display_name')->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesPermissions(Request $request)
    {
        $query = Permission::query();
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('display_name', 'like', "%{$search}%")->orWhere('module', 'like', "%{$search}%")->orWhere('permission_key', 'like', "%{$search}%"); }); }
        if ($request->filled('module')) { $query->where('module', $request->module); }

        $totalData = $query->count();
        $columns = ['id', 'module', 'permission_key', 'display_name'];
        $orderColumn = $request->input('order.0.column', 1); $orderDir = $request->input('order.0.dir', 'asc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $permissions = $query->skip($start)->take($length)->get();

        $data = $permissions->map(function ($perm, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $perm->id,
                'module' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . ucfirst($perm->module) . '</span>',
                'permission_key' => $perm->permission_key, 'display_name' => $perm->display_name ?? '-',
                'description' => \Illuminate\Support\Str::limit($perm->description, 40),
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'editPermission(' . json_encode(['id' => $perm->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="deletePermission(' . $perm->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:100', 'permission_key' => 'required|string|max:255|unique:permissions,permission_key',
            'display_name' => 'nullable|string|max:255', 'description' => 'nullable|string',
        ]);
        Permission::create($validated);
        return redirect()->route('permissions.index')->with('success', 'Permission created successfully!');
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'module' => 'required|string|max:100', 'permission_key' => 'required|string|max:255|unique:permissions,permission_key,' . $permission->id,
            'display_name' => 'nullable|string|max:255', 'description' => 'nullable|string',
        ]);
        $permission->update($validated);
        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully!');
    }

    public function destroy(Permission $permission)
    {
        if ($permission->roles()->count() > 0) { return back()->with('error', 'Cannot delete permission assigned to roles!'); }
        $permission->delete();
        return back()->with('success', 'Permission deleted successfully!');
    }

    public function dropdown()
    {
        return response()->json(Permission::select('id', 'module', 'permission_key', 'display_name')->orderBy('module')->orderBy('display_name')->get());
    }
}
