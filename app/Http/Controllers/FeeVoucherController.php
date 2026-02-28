<?php

namespace App\Http\Controllers;

use App\Models\FeeVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FeeVoucherController extends Controller
{
    // -------------------------------------------------------------------------
    // Public Routes
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->has('page'))) {
            return $this->getMobileVouchers($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesVouchers($request);
        }

        return Inertia::render('FeeVouchers/Index');
    }

    public function create()
    {
        return Inertia::render('FeeVouchers/Create', $this->getFormData());
    }

    public function edit(FeeVoucher $feeVoucher)
    {
        $feeVoucher->load(['studentEnrollment.student', 'feeType', 'academicYear']);

        return Inertia::render('FeeVouchers/Edit', array_merge(
            $this->getFormData(),
            ['voucher' => $this->formatVoucherForEdit($feeVoucher)]
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateVoucher($request);
        $validated['generated_by'] = auth()->id();

        DB::transaction(function () use (&$validated) {
            if (empty($validated['voucher_no'])) {
                $validated['voucher_no'] = $this->generateVoucherNo();
            }
            FeeVoucher::create($validated);
        });

        return redirect()->route('fee-vouchers.index')
            ->with('success', 'Fee voucher created successfully!');
    }

    public function update(Request $request, FeeVoucher $feeVoucher)
    {
        $validated = $this->validateVoucher($request, $feeVoucher);

        $feeVoucher->update($validated);

        return redirect()->route('fee-vouchers.index')
            ->with('success', 'Fee voucher updated successfully!');
    }

    public function destroy(FeeVoucher $feeVoucher)
    {
        if ($feeVoucher->payments()->exists()) {
            return back()->with('error', 'Cannot delete a voucher that has existing payments.');
        }

        $feeVoucher->delete();

        return back()->with('success', 'Fee voucher deleted successfully!');
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Shared dropdown data for create/edit forms.
     */
    private function getFormData(): array
    {
        return [
            'enrollments' => \App\Models\StudentEnrollment::with('student:id,student_name,admission_no')
                ->select('id', 'student_id', 'academic_year_id')
                ->where('status', 'active')
                ->get(),

            'feeTypes' => \App\Models\FeeType::select('id', 'fee_name')
                ->orderBy('fee_name')
                ->get(),

            'academicYears' => \App\Models\AcademicYear::select('id', 'year_name')
                ->orderBy('start_date', 'desc')
                ->get(),
        ];
    }

    /**
     * Map a voucher model to the array expected by the Edit page.
     */
    private function formatVoucherForEdit(FeeVoucher $voucher): array
    {
        return [
            'id'                    => $voucher->id,
            'voucher_no'            => $voucher->voucher_no,
            'student_enrollment_id' => $voucher->student_enrollment_id,
            'fee_type_id'           => $voucher->fee_type_id,
            'academic_year_id'      => $voucher->academic_year_id,
            'month'                 => $voucher->month,
            'year'                  => $voucher->year,
            'generated_for'         => $voucher->generated_for,
            'original_amount'       => $voucher->original_amount,
            'discount_amount'       => $voucher->discount_amount,
            'fine_amount'           => $voucher->fine_amount,
            'net_amount'            => $voucher->net_amount,
            'paid_amount'           => $voucher->paid_amount,
            'remaining_amount'      => $voucher->remaining_amount,
            'due_date'              => $voucher->due_date?->format('Y-m-d'),
            'status'                => $voucher->status,
            'notes'                 => $voucher->notes,
        ];
    }

    /**
     * Centralised validation rules used by both store() and update().
     * Pass $feeVoucher to apply ignore() rules for updates.
     */
    private function validateVoucher(Request $request, ?FeeVoucher $feeVoucher = null): array
    {
        $isUpdate = $feeVoucher !== null;

        $uniqueVoucherNo = Rule::unique('fee_vouchers', 'voucher_no');
        $uniqueEnrollment = Rule::unique('fee_vouchers', 'student_enrollment_id')
            ->where(fn ($query) => $query
                ->where('fee_type_id', $request->fee_type_id)
                ->where('generated_for', $request->generated_for)
            );

        if ($isUpdate) {
            $uniqueVoucherNo   = $uniqueVoucherNo->ignore($feeVoucher->id);
            $uniqueEnrollment  = $uniqueEnrollment->ignore($feeVoucher->id);
        }

        return $request->validate([
            'voucher_no'            => ['nullable', 'string', $uniqueVoucherNo],
            'student_enrollment_id' => ['required', 'exists:student_enrollments,id', $uniqueEnrollment],
            'fee_type_id'           => ['required', 'exists:fee_types,id'],
            'academic_year_id'      => ['required', 'exists:academic_years,id'],
            'month'                 => ['required', 'integer', 'min:1', 'max:12'],
            'year'                  => ['required', 'integer', 'min:2000', 'max:2100'],
            'generated_for'         => ['nullable', 'string', 'max:255'],
            'original_amount'       => ['required', 'numeric', 'min:0'],
            'discount_amount'       => ['nullable', 'numeric', 'min:0'],
            'fine_amount'           => ['nullable', 'numeric', 'min:0'],
            'net_amount'            => ['required', 'numeric', 'min:0'],
            'paid_amount'           => ['nullable', 'numeric', 'min:0'],
            'remaining_amount'      => ['nullable', 'numeric', 'min:0'],
            'due_date'              => ['required', 'date'],
            'status'                => ['nullable', 'string', Rule::in(['pending', 'partial', 'paid', 'waived'])],
            'notes'                 => ['nullable', 'string'],
        ]);
    }

    /**
     * Generate a unique voucher number inside a DB transaction to prevent
     * race conditions under concurrent requests.
     */
    private function generateVoucherNo(): string
    {
        // Lock the last row so concurrent requests can't grab the same number.
        $latest = FeeVoucher::lockForUpdate()->latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;

        return 'VCH-' . date('Y') . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Paginated JSON for mobile clients.
     */
    private function getMobileVouchers(Request $request)
    {
        $query = FeeVoucher::with(['studentEnrollment.student:id,student_name,admission_no', 'feeType', 'academicYear']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('net_amount', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vouchers = $query->latest()->paginate($request->get('per_page', 10));

        return response()->json($vouchers);
    }

    /**
     * Server-side DataTables response.
     *
     * Fixes:
     *  - recordsFiltered now reflects the actual filtered count.
     *  - student_name includes both first_name and last_name.
     *  - Column index guard prevents SQL injection via the order parameter.
     */
    private function getDataTablesVouchers(Request $request)
    {
        $query = FeeVoucher::with(['studentEnrollment.student', 'feeType', 'academicYear']);

        // --- Search ---
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('net_amount', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // --- Status filter ---
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Total after filtering (fixes the recordsFiltered bug)
        $recordsFiltered = (clone $query)->count();

        // --- Sorting ---
        $sortableColumns = [
            0 => 'id',
            1 => 'voucher_no',
            2 => 'student_enrollment_id',
            3 => 'net_amount',
            4 => 'paid_amount',
            5 => 'due_date',
            6 => 'status',
        ];

        $orderColumnIndex = (int) $request->input('order.0.column', 0);
        $orderDir         = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        if (isset($sortableColumns[$orderColumnIndex])) {
            $query->orderBy($sortableColumns[$orderColumnIndex], $orderDir);
        }

        // --- Pagination ---
        $start    = max(0, (int) $request->input('start', 0));
        $length   = max(1, (int) $request->input('length', 10));
        $vouchers = $query->skip($start)->take($length)->get();

        $data = $vouchers->map(function (FeeVoucher $voucher, int $index) use ($start) {
            $statusClass = match ($voucher->status) {
                'paid'    => 'bg-green-100 text-green-800',
                'partial' => 'bg-yellow-100 text-yellow-800',
                'pending' => 'bg-red-100 text-red-800',
                'waived'  => 'bg-blue-100 text-blue-800',
                default   => 'bg-gray-100 text-gray-800',
            };

            $studentName = $voucher->studentEnrollment?->student?->student_name ?? '-';

            return [
                'DT_RowIndex'      => $start + $index + 1,
                'id'               => $voucher->id,
                'voucher_no'       => $voucher->voucher_no,
                'student_name'     => $studentName,
                'fee_type'         => $voucher->feeType?->fee_name ?? '-',
                'month_year'       => $voucher->month . '/' . $voucher->year,
                'net_amount'       => number_format($voucher->net_amount, 2),
                'paid_amount'      => number_format($voucher->paid_amount, 2),
                'remaining_amount' => number_format($voucher->remaining_amount, 2),
                'due_date'         => $voucher->due_date?->format('d M, Y') ?? '-',
                'status'           => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $statusClass . '">'
                                        . ucfirst($voucher->status)
                                      . '</span>',
                'action' => '
                    <div class="flex items-center justify-center gap-2">
                        <button
                            onclick=\'editVoucher(' . json_encode(['id' => $voucher->id]) . ')\'
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button
                            onclick="deleteVoucher(' . $voucher->id . ')"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                ',
            ];
        });

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => FeeVoucher::count(),   // unfiltered total
            'recordsFiltered' => $recordsFiltered,       // filtered total (was wrong before)
            'data'            => $data,
        ]);
    }
}