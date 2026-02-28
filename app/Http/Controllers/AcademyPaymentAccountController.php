<?php

namespace App\Http\Controllers;

use App\Models\AcademyPaymentAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademyPaymentAccountController extends Controller
{
    /**
     * Display a listing of payment accounts
     */
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileAccounts($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesAccounts($request);
        }

        return Inertia::render('AcademyPaymentAccounts/Index');
    }

    public function create()
    {
        return Inertia::render('AcademyPaymentAccounts/Create');
    }

    public function edit(AcademyPaymentAccount $academyPaymentAccount)
    {
        return Inertia::render('AcademyPaymentAccounts/Edit', [
            'account' => [
                'id'             => $academyPaymentAccount->id,
                'account_title'  => $academyPaymentAccount->account_title,
                'payment_method' => $academyPaymentAccount->payment_method,
                'account_number' => $academyPaymentAccount->account_number,
                'bank_name'      => $academyPaymentAccount->bank_name,
                'branch_name'    => $academyPaymentAccount->branch_name,
                'iban'           => $academyPaymentAccount->iban,
                'instructions'   => $academyPaymentAccount->instructions,
                'is_active'      => $academyPaymentAccount->is_active,
            ]
        ]);
    }

    private function getMobileAccounts(Request $request)
    {
        $query = AcademyPaymentAccount::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_title', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $perPage = $request->get('per_page', 10);
        $accounts = $query->latest()->paginate($perPage);

        return response()->json($accounts);
    }

    private function getDataTablesAccounts(Request $request)
    {
        $query = AcademyPaymentAccount::query();

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('account_title', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 1);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'account_title', 'payment_method', 'account_number', 'bank_name', 'is_active'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start    = $request->input('start', 0);
        $length   = $request->input('length', 10);
        $accounts = $query->skip($start)->take($length)->get();

        $data = $accounts->map(function ($account, $index) use ($start) {
            return [
                'DT_RowIndex'    => $start + $index + 1,
                'id'             => $account->id,
                'account_title'  => $account->account_title,
                'payment_method' => $account->payment_method,
                'account_number' => $account->account_number,
                'bank_name'      => $account->bank_name ?? '-',
                'is_active'      => $account->is_active
                    ? '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>'
                    : '<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editAccount(' . json_encode(['id' => $account->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteAccount(' . $account->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
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
            'account_title'  => 'required|string|max:255',
            'payment_method' => 'required|string|max:100',
            'account_number' => 'required|string|max:100',
            'bank_name'      => 'nullable|string|max:255',
            'branch_name'    => 'nullable|string|max:255',
            'iban'           => 'nullable|string|max:100',
            'instructions'   => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        AcademyPaymentAccount::create($validated);

        return redirect()->route('academy-payment-accounts.index')
            ->with('success', 'Payment account created successfully!');
    }

    public function update(Request $request, AcademyPaymentAccount $academyPaymentAccount)
    {
        $validated = $request->validate([
            'account_title'  => 'required|string|max:255',
            'payment_method' => 'required|string|max:100',
            'account_number' => 'required|string|max:100',
            'bank_name'      => 'nullable|string|max:255',
            'branch_name'    => 'nullable|string|max:255',
            'iban'           => 'nullable|string|max:100',
            'instructions'   => 'nullable|string',
            'is_active'      => 'boolean',
        ]);

        $academyPaymentAccount->update($validated);

        return redirect()->route('academy-payment-accounts.index')
            ->with('success', 'Payment account updated successfully!');
    }

    public function destroy(AcademyPaymentAccount $academyPaymentAccount)
    {
        if ($academyPaymentAccount->onlinePaymentProofs()->count() > 0) {
            return back()->with('error', 'Cannot delete account with existing payment proofs!');
        }

        $academyPaymentAccount->delete();

        return back()->with('success', 'Payment account deleted successfully!');
    }

    public function dropdown()
    {
        $accounts = AcademyPaymentAccount::active()
            ->select('id', 'account_title', 'payment_method', 'bank_name')
            ->orderBy('account_title')
            ->get();

        return response()->json($accounts);
    }
}
