<?php

namespace App\Http\Controllers;

use App\Models\VoucherDiscountBreakdown;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherDiscountBreakdownController extends Controller
{
    /**
     * Read-only controller — discount breakdowns are system-generated
     */
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) { return $this->getMobileBreakdowns($request); }
        if ($request->ajax() && $request->has('draw')) { return $this->getDataTablesBreakdowns($request); }
        return Inertia::render('VoucherDiscountBreakdowns/Index');
    }

    public function show(VoucherDiscountBreakdown $voucherDiscountBreakdown)
    {
        $voucherDiscountBreakdown->load(['voucher', 'appliedBy']);
        return Inertia::render('VoucherDiscountBreakdowns/Show', ['breakdown' => $voucherDiscountBreakdown]);
    }

    private function getMobileBreakdowns(Request $request)
    {
        $query = VoucherDiscountBreakdown::with(['voucher', 'appliedBy']);
        if ($request->filled('search')) { $query->where(function ($q) use ($request) { $q->where('discount_source', 'like', "%{$request->search}%")->orWhere('discount_amount', 'like', "%{$request->search}%"); }); }
        if ($request->filled('voucher_id')) { $query->where('voucher_id', $request->voucher_id); }
        return response()->json($query->latest()->paginate($request->get('per_page', 10)));
    }

    private function getDataTablesBreakdowns(Request $request)
    {
        $query = VoucherDiscountBreakdown::with(['voucher', 'appliedBy']);
        if ($request->filled('search.value')) { $search = $request->input('search.value'); $query->where(function ($q) use ($search) { $q->where('discount_source', 'like', "%{$search}%")->orWhere('discount_amount', 'like', "%{$search}%"); }); }
        if ($request->filled('voucher_id')) { $query->where('voucher_id', $request->voucher_id); }

        $totalData = $query->count();
        $columns = ['id', 'voucher_id', 'discount_source', 'discount_amount', 'created_at'];
        $orderColumn = $request->input('order.0.column', 0); $orderDir = $request->input('order.0.dir', 'desc');
        if (isset($columns[$orderColumn])) { $query->orderBy($columns[$orderColumn], $orderDir); }

        $start = $request->input('start', 0); $length = $request->input('length', 10);
        $breakdowns = $query->skip($start)->take($length)->get();

        $data = $breakdowns->map(function ($b, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1, 'id' => $b->id,
                'voucher_no' => $b->voucher?->voucher_no ?? '-',
                'discount_source' => '<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">' . ucfirst($b->discount_source ?? '-') . '</span>',
                'source_id' => $b->source_id ?? '-',
                'discount_type' => ucfirst($b->discount_type ?? '-'),
                'discount_value' => $b->discount_type === 'percentage' ? ($b->discount_value ?? 0) . '%' : number_format($b->discount_value ?? 0, 2),
                'discount_amount' => number_format($b->discount_amount, 2),
                'applied_by' => $b->appliedBy?->name ?? 'System',
                'created_at' => $b->created_at?->format('d M, Y H:i') ?? '-',
                'action' => '<div class="flex items-center justify-center"><button onclick=\'viewBreakdown(' . json_encode(['id' => $b->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"><svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>View</button></div>'
            ];
        });
        return response()->json(['draw' => intval($request->input('draw')), 'recordsTotal' => $totalData, 'recordsFiltered' => $totalData, 'data' => $data]);
    }
}
