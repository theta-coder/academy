<?php

namespace App\Http\Controllers;

use App\Models\OnlinePaymentProof;
use App\Models\AcademyPaymentAccount;
use App\Models\FeeVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnlinePaymentProofController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileProofs($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesProofs($request); }
        return Inertia::render('OnlinePaymentProofs/Index');
    }

    public function create() { return Inertia::render('OnlinePaymentProofs/Create'); }

    public function edit(OnlinePaymentProof $onlinePaymentProof)
    {
        $onlinePaymentProof->load(['voucher', 'studentEnrollment.student', 'academyAccount']);
        return Inertia::render('OnlinePaymentProofs/Edit', [
            'proof' => [
                'id' => $onlinePaymentProof->id, 'voucher_id' => $onlinePaymentProof->voucher_id,
                'student_enrollment_id' => $onlinePaymentProof->student_enrollment_id,
                'academy_account_id' => $onlinePaymentProof->academy_account_id,
                'payment_method' => $onlinePaymentProof->payment_method,
                'sender_name' => $onlinePaymentProof->sender_name, 'sender_number' => $onlinePaymentProof->sender_number,
                'transaction_id' => $onlinePaymentProof->transaction_id,
                'amount_sent' => $onlinePaymentProof->amount_sent,
                'payment_datetime' => $onlinePaymentProof->payment_datetime?->format('Y-m-d\TH:i'),
                'screenshot_path' => $onlinePaymentProof->screenshot_path,
                'submission_notes' => $onlinePaymentProof->submission_notes,
                'verification_status' => $onlinePaymentProof->verification_status,
                'rejection_reason' => $onlinePaymentProof->rejection_reason,
            ]
        ]);
    }

    private function getMobileProofs(Request $request)
    {
        $query = OnlinePaymentProof::with(['voucher', 'studentEnrollment.student', 'academyAccount', 'verifiedBy']);
        if ($request->filled('search')) { $query->where(function ($q) use ($request) { $q->where('transaction_id', 'like', "%{$request->search}%")->orWhere('sender_name', 'like', "%{$request->search}%"); }); }
        if ($request->filled('verification_status')) { $query->where('verification_status', $request->verification_status); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesProofs(Request $request)
    {
        $query = OnlinePaymentProof::with(['voucher', 'studentEnrollment.student', 'academyAccount', 'verifiedBy']);
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('transaction_id', 'like', "%{$search}%")->orWhere('sender_name', 'like', "%{$search}%"); }); }
        if ($request->filled('verification_status')) { $query->where('verification_status', $request->verification_status); }

        $totalData = $query->count();
        $columns = ['id', 'voucher_id', 'student_enrollment_id', 'amount_sent', 'payment_datetime', 'verification_status'];
        $orderColumn = $request->input('order.0.column', 0); $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $proofs = $query->skip($start)->take($length)->get();

        $data = $proofs->map(function ($p, $index) use ($start) {
            $statusClass = match($p->verification_status) { 'verified' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'rejected' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-800' };
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $p->id,
                'voucher_no' => $p->voucher?->voucher_no ?? '-',
                'student_name' => $p->studentEnrollment?->student?->student_name ?? '-',
                'account' => $p->academyAccount?->account_title ?? '-',
                'payment_method' => $p->payment_method,
                'amount_sent' => number_format($p->amount_sent, 2),
                'transaction_id' => $p->transaction_id ?? '-',
                'payment_datetime' => $p->payment_datetime?->format('d M, Y H:i') ?? '-',
                'verified_by' => $p->verifiedBy?->name ?? '-',
                'verification_status' => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">' . ucfirst($p->verification_status) . '</span>',
                'action' => '<div class="flex items-center justify-center gap-2"><button onclick=\'editProof(' . json_encode(['id' => $p->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button><button onclick="deleteProof(' . $p->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Delete</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_id' => 'required|exists:fee_vouchers,id', 'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'academy_account_id' => 'required|exists:academy_payment_accounts,id',
            'payment_method' => 'required|string|in:jazzcash,easypaisa,bank_transfer,raast',
            'sender_name' => 'nullable|string|max:150', 'sender_number' => 'nullable|string|max:20',
            'transaction_id' => 'nullable|string|max:100', 'amount_sent' => 'required|numeric|min:0.01',
            'payment_datetime' => 'required|date', 'screenshot_path' => 'nullable|string',
            'submission_notes' => 'nullable|string',
        ]);
        $validated['submitted_by'] = auth()->id();
        OnlinePaymentProof::create($validated);
        return redirect()->route('online-payment-proofs.index')->with('success', 'Payment proof submitted successfully!');
    }

    public function update(Request $request, OnlinePaymentProof $onlinePaymentProof)
    {
        $validated = $request->validate([
            'verification_status' => 'required|string|in:pending,verified,rejected',
            'rejection_reason' => 'nullable|string', 'submission_notes' => 'nullable|string',
        ]);
        if ($validated['verification_status'] !== 'pending') { $validated['verified_by'] = auth()->id(); $validated['verified_at'] = now(); }
        $onlinePaymentProof->update($validated);
        return redirect()->route('online-payment-proofs.index')->with('success', 'Payment proof updated successfully!');
    }

    public function destroy(OnlinePaymentProof $onlinePaymentProof)
    {
        $onlinePaymentProof->delete();
        return back()->with('success', 'Payment proof deleted successfully!');
    }
}
