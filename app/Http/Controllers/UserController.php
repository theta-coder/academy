<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileUsers($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesUsers($request); }
        return Inertia::render('Users/Index');
    }

    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::active()->select('id', 'role_name', 'display_name')->orderBy('role_name')->get(),
            'branches' => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
                'role_id' => $user->role_id, 'branch_id' => $user->branch_id, 'is_active' => $user->is_active,
            ],
            'roles' => Role::active()->select('id', 'role_name', 'display_name')->orderBy('role_name')->get(),
            'branches' => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
        ]);
    }

    private function getMobileUsers(Request $request)
    {
        $query = User::with(['role', 'branch']);
        if ($request->filled('search')) { $query->where(function ($q) use ($request) { $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"); }); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }
        if ($request->filled('role_id')) { $query->where('role_id', $request->role_id); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesUsers(Request $request)
    {
        $query = User::with(['role', 'branch']);
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"); }); }
        if ($request->filled('is_active')) { $query->where('is_active', $request->is_active); }
        if ($request->filled('role_id')) { $query->where('role_id', $request->role_id); }

        $totalData = $query->count();
        $columns = ['id', 'name', 'email', 'role_id', 'branch_id', 'is_active'];
        $orderColumn = $request->input('order.0.column', 1); $orderDir = $request->input('order.0.dir', 'asc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $users = $query->skip($start)->take($length)->get();

        $data = $users->map(function ($user, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $user->id,
                'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone ?? '-',
                'role_name' => $user->role?->display_name ?? $user->role?->role_name ?? '-',
                'branch_name' => $user->branch?->branch_name ?? '-',
                'is_active' => $user->is_active ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>' : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'editUser(' . json_encode(['id' => $user->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="deleteUser(' . $user->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20', 'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id', 'branch_id' => 'nullable|exists:branches,id', 'is_active' => 'boolean',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);
        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20', 'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id', 'branch_id' => 'nullable|exists:branches,id', 'is_active' => 'boolean',
        ]);
        if (!empty($validated['password'])) { $validated['password'] = Hash::make($validated['password']); } else { unset($validated['password']); }
        $user->update($validated);
        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) { return back()->with('error', 'You cannot delete your own account!'); }
        $user->delete();
        return back()->with('success', 'User deleted successfully!');
    }

    public function dropdown()
    {
        return response()->json(User::where('is_active', true)->select('id', 'name', 'email')->orderBy('name')->get());
    }
}
