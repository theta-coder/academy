<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileRoles($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesRoles($request); }
        return Inertia::render('Roles/Index');
    }

    public function create() { return Inertia::render('Roles/Create'); }

    public function edit(Role $role)
    {
        $role->load('permissions');
        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id, 'role_name' => $role->role_name, 'display_name' => $role->display_name,
                'description' => $role->description, 'is_active' => $role->is_active,
                'permissions' => $role->permissions->pluck('id'),
            ]
        ]);
    }

    private function getMobileRoles(Request $request)
    {
        $query = Role::withCount(['users', 'permissions']);
        if ($request->filled('search')) { $query->where('role_name', 'like', "%{$request->search}%")->orWhere('display_name', 'like', "%{$request->search}%"); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesRoles(Request $request)
    {
        $query = Role::withCount(['users', 'permissions']);
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('role_name', 'like', "%{$search}%")->orWhere('display_name', 'like', "%{$search}%"); }); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }

        $totalData = $query->count();
        $columns = ['id', 'role_name', 'display_name', 'is_active'];
        $orderColumn = $request->input('order.0.column', 1); $orderDir = $request->input('order.0.dir', 'asc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $roles = $query->skip($start)->take($length)->get();

        $data = $roles->map(function ($role, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $role->id,
                'role_name' => $role->role_name, 'display_name' => $role->display_name ?? '-',
                'users_count' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . $role->users_count . ' users</span>',
                'permissions_count' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">' . $role->permissions_count . ' permissions</span>',
                'is_active' => $role->is_active ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>' : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'editRole(' . json_encode(['id' => $role->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="deleteRole(' . $role->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:100|unique:roles,role_name',
            'display_name' => 'nullable|string|max:255', 'description' => 'nullable|string', 'is_active' => 'boolean',
            'permissions' => 'nullable|array', 'permissions.*' => 'exists:permissions,id',
        ]);
        $role = Role::create(\Illuminate\Support\Arr::except($validated, ['permissions']));
        if (!empty($validated['permissions'])) { $role->permissions()->sync($validated['permissions']); }
        return redirect()->route('roles.index')->with('success', 'Role created successfully!');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'role_name' => 'required|string|max:100|unique:roles,role_name,' . $role->id,
            'display_name' => 'nullable|string|max:255', 'description' => 'nullable|string', 'is_active' => 'boolean',
            'permissions' => 'nullable|array', 'permissions.*' => 'exists:permissions,id',
        ]);
        $role->update(\Illuminate\Support\Arr::except($validated, ['permissions']));
        if (isset($validated['permissions'])) { $role->permissions()->sync($validated['permissions']); }
        return redirect()->route('roles.index')->with('success', 'Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) { return back()->with('error', 'Cannot delete role with assigned users!'); }
        $role->permissions()->detach();
        $role->delete();
        return back()->with('success', 'Role deleted successfully!');
    }

    public function dropdown()
    {
        return response()->json(Role::active()->select('id', 'role_name', 'display_name')->orderBy('role_name')->get());
    }
}
