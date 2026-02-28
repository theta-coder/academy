<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileSettings($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesSettings($request); }
        return Inertia::render('Settings/Index');
    }

    public function create()
    {
        return Inertia::render('Settings/Create', [
            'branches' => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
        ]);
    }

    public function edit(Setting $setting)
    {
        return Inertia::render('Settings/Edit', [
            'setting' => [
                'id' => $setting->id, 'branch_id' => $setting->branch_id, 'category' => $setting->category,
                'key' => $setting->key, 'value' => $setting->value, 'data_type' => $setting->data_type,
                'description' => $setting->description, 'is_system' => $setting->is_system,
            ],
            'branches' => Branch::active()->select('id', 'branch_name')->orderBy('branch_name')->get(),
        ]);
    }

    private function getMobileSettings(Request $request)
    {
        $query = Setting::with('branch');
        if ($request->filled('search')) { $query->where(function ($q) use ($request) { $q->where('key', 'like', "%{$request->search}%")->orWhere('category', 'like', "%{$request->search}%"); }); }
        if ($request->filled('category')) { $query->where('category', $request->category); }
        return response()->json($query->orderBy('category')->orderBy('key')->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesSettings(Request $request)
    {
        $query = Setting::with('branch');
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('key', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%")->orWhere('value', 'like', "%{$search}%"); }); }
        if ($request->filled('category')) { $query->where('category', $request->category); }

        $totalData = $query->count();
        $columns = ['id', 'category', 'key', 'value', 'data_type', 'branch_id'];
        $orderColumn = $request->input('order.0.column', 1); $orderDir = $request->input('order.0.dir', 'asc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $settings = $query->skip($start)->take($length)->get();

        $data = $settings->map(function ($s, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $s->id,
                'category' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . ucfirst($s->category) . '</span>',
                'key' => $s->key, 'value' => \Illuminate\Support\Str::limit($s->value, 50),
                'data_type' => $s->data_type ?? '-', 'branch_name' => $s->branch?->branch_name ?? 'Global',
                'is_system' => $s->is_system ? '<span class="text-xs text-red-600">System</span>' : '<span class="text-xs text-gray-500">Custom</span>',
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'editSetting(' . json_encode(['id' => $s->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="deleteSetting(' . $s->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id', 'category' => 'required|string|max:100',
            'key' => 'required|string|max:255', 'value' => 'nullable|string', 'data_type' => 'nullable|string|max:50',
            'description' => 'nullable|string', 'is_system' => 'boolean',
        ]);
        Setting::create($validated);
        return redirect()->route('settings.index')->with('success', 'Setting created successfully!');
    }

    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id', 'category' => 'required|string|max:100',
            'key' => 'required|string|max:255', 'value' => 'nullable|string', 'data_type' => 'nullable|string|max:50',
            'description' => 'nullable|string', 'is_system' => 'boolean',
        ]);
        $setting->update($validated);
        return redirect()->route('settings.index')->with('success', 'Setting updated successfully!');
    }

    public function destroy(Setting $setting)
    {
        if ($setting->is_system) { return back()->with('error', 'Cannot delete system settings!'); }
        $setting->delete();
        return back()->with('success', 'Setting deleted successfully!');
    }
}
