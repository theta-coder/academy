<?php

namespace App\Http\Controllers;

use App\Models\FeeReminder;
use App\Models\StudentEnrollment;
use App\Models\FeeVoucher;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FeeReminderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('mobile') || ($request->ajax() && $request->get('page'))) {
            return $this->getMobileReminders($request);
        }

        if ($request->ajax() && $request->has('draw')) {
            return $this->getDataTablesReminders($request);
        }

        return Inertia::render('FeeReminders/Index');
    }

    public function create()
    {
        $students = Student::select('id', 'student_name', 'admission_no')
            ->orderBy('student_name')
            ->get();

        return Inertia::render('FeeReminders/Create', [
            'students' => $students,
        ]);
    }

    public function edit(FeeReminder $feeReminder)
    {
        $feeReminder->load(['studentEnrollment.student', 'sentBy']);

        $students = Student::select('id', 'student_name', 'admission_no')
            ->orderBy('student_name')
            ->get();

        return Inertia::render('FeeReminders/Edit', [
            'reminder' => [
                'id'                    => $feeReminder->id,
                'student_enrollment_id' => $feeReminder->student_enrollment_id,
                'voucher_ids'           => $feeReminder->voucher_ids,
                'total_amount_reminded' => $feeReminder->total_amount_reminded,
                'months_reminded'       => $feeReminder->months_reminded,
                'channel'               => $feeReminder->channel,
                'message_content'       => $feeReminder->message_content,
                'contact_number_used'   => $feeReminder->contact_number_used,
                'sent_at'               => $feeReminder->sent_at?->format('Y-m-d H:i'),
                'response'              => $feeReminder->response,
                'promised_pay_date'     => $feeReminder->promised_pay_date?->format('Y-m-d'),
                'follow_up_date'        => $feeReminder->follow_up_date?->format('Y-m-d'),
                'outcome'               => $feeReminder->outcome,
                'outcome_date'          => $feeReminder->outcome_date?->format('Y-m-d'),
                'notes'                 => $feeReminder->notes,
            ],
            'students' => $students,
        ]);
    }

    private function getMobileReminders(Request $request)
    {
        $query = FeeReminder::with(['studentEnrollment.student', 'sentBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhere('channel', 'like', "%{$search}%")
                  ->orWhere('outcome', 'like', "%{$search}%");
            });
        }

        if ($request->filled('student_enrollment_id')) {
            $query->where('student_enrollment_id', $request->student_enrollment_id);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        $perPage = $request->get('per_page', 10);
        $reminders = $query->latest()->paginate($perPage);

        return response()->json($reminders);
    }

    private function getDataTablesReminders(Request $request)
    {
        $query = FeeReminder::with(['studentEnrollment.student', 'sentBy']);

        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhere('channel', 'like', "%{$search}%")
                  ->orWhere('outcome', 'like', "%{$search}%");
            });
        }

        if ($request->filled('student_enrollment_id')) {
            $query->where('student_enrollment_id', $request->student_enrollment_id);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        $totalData = $query->count();

        $orderColumn = $request->input('order.0.column', 0);
        $orderDir    = $request->input('order.0.dir', 'desc');
        $columns     = ['id', 'student_enrollment_id', 'total_amount_reminded', 'channel', 'sent_at', 'outcome'];

        if (isset($columns[$orderColumn])) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        $start     = $request->input('start', 0);
        $length    = $request->input('length', 10);
        $reminders = $query->skip($start)->take($length)->get();

        $data = $reminders->map(function ($reminder, $index) use ($start) {
            $channelClass = match ($reminder->channel) {
                'sms'     => 'bg-blue-100 text-blue-800',
                'email'   => 'bg-purple-100 text-purple-800',
                'whatsapp'=> 'bg-green-100 text-green-800',
                'call'    => 'bg-orange-100 text-orange-800',
                default   => 'bg-gray-100 text-gray-800',
            };

            $outcomeClass = match ($reminder->outcome) {
                'promised_to_pay' => 'bg-green-100 text-green-800',
                'no_response'     => 'bg-red-100 text-red-800',
                'follow_up'       => 'bg-yellow-100 text-yellow-800',
                'paid'            => 'bg-blue-100 text-blue-800',
                default           => 'bg-gray-100 text-gray-800',
            };

            return [
                'DT_RowIndex'         => $start + $index + 1,
                'id'                  => $reminder->id,
                'student_name'        => $reminder->studentEnrollment?->student?->student_name ?? '-',
                'admission_no'        => $reminder->studentEnrollment?->student?->admission_no ?? '-',
                'total_amount'        => number_format($reminder->total_amount_reminded, 2),
                'months_reminded'     => $reminder->months_reminded ?? '-',
                'channel'             => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $channelClass . '">' . ucfirst($reminder->channel) . '</span>',
                'contact_number'      => $reminder->contact_number_used ?? '-',
                'sent_at'             => $reminder->sent_at?->format('d M, Y h:i A') ?? '-',
                'promised_date'       => $reminder->promised_pay_date?->format('d M, Y') ?? '-',
                'follow_up_date'      => $reminder->follow_up_date?->format('d M, Y') ?? '-',
                'outcome'             => '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $outcomeClass . '">' . str_replace('_', ' ', ucfirst($reminder->outcome ?? '-')) . '</span>',
                'notes'               => \Illuminate\Support\Str::limit($reminder->notes ?? '-', 30),
                'action'              => '
                    <div class="flex items-center justify-center gap-2">
                        <button onclick=\'editReminder(' . json_encode(['id' => $reminder->id]) . ')\' class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </button>
                        <button onclick="deleteReminder(' . $reminder->id . ')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                ',
            ];
        });

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalData,
            'data'            => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'voucher_ids'           => 'nullable|string|max:500',
            'total_amount_reminded' => 'required|numeric|min:0',
            'months_reminded'       => 'nullable|string|max:255',
            'channel'               => 'required|string|in:sms,email,whatsapp,call',
            'message_content'       => 'nullable|string',
            'contact_number_used'   => 'nullable|string|max:20',
            'sent_at'               => 'nullable|date',
            'response'              => 'nullable|string',
            'promised_pay_date'     => 'nullable|date',
            'follow_up_date'        => 'nullable|date|after_or_equal:promised_pay_date',
            'outcome'               => 'nullable|string|in:promised_to_pay,no_response,follow_up,paid',
            'outcome_date'          => 'nullable|date',
            'notes'                 => 'nullable|string',
        ]);

        $validated['sent_by'] = auth()->id() ?? 1;
        $validated['sent_at'] = $validated['sent_at'] ?? now();

        FeeReminder::create($validated);

        return redirect()->route('fee-reminders.index')
            ->with('success', 'Fee reminder created successfully!');
    }

    public function update(Request $request, FeeReminder $feeReminder)
    {
        $validated = $request->validate([
            'student_enrollment_id' => 'required|exists:student_enrollments,id',
            'voucher_ids'           => 'nullable|string|max:500',
            'total_amount_reminded' => 'required|numeric|min:0',
            'months_reminded'       => 'nullable|string|max:255',
            'channel'               => 'required|string|in:sms,email,whatsapp,call',
            'message_content'       => 'nullable|string',
            'contact_number_used'   => 'nullable|string|max:20',
            'sent_at'               => 'nullable|date',
            'response'              => 'nullable|string',
            'promised_pay_date'     => 'nullable|date',
            'follow_up_date'        => 'nullable|date|after_or_equal:promised_pay_date',
            'outcome'               => 'nullable|string|in:promised_to_pay,no_response,follow_up,paid',
            'outcome_date'          => 'nullable|date',
            'notes'                 => 'nullable|string',
        ]);

        $feeReminder->update($validated);

        return redirect()->route('fee-reminders.index')
            ->with('success', 'Fee reminder updated successfully!');
    }

    public function destroy(FeeReminder $feeReminder)
    {
        $feeReminder->delete();

        return back()->with('success', 'Fee reminder deleted successfully!');
    }

    public function getEnrollmentsByStudent($studentId)
    {
        $enrollments = StudentEnrollment::where('student_id', $studentId)
            ->with(['academicYear:id,year_name', 'classSection.branchClass.class', 'classSection.section'])
            ->get()
            ->map(fn($e) => [
                'id'            => $e->id,
                'class_name'    => $e->classSection?->branchClass?->class?->class_name ?? '-',
                'section_name'  => $e->classSection?->section?->section_name ?? '-',
                'academic_year' => $e->academicYear?->year_name ?? '-',
            ]);

        return response()->json($enrollments);
    }

    public function getUnpaidVouchers($enrollmentId)
    {
        $vouchers = FeeVoucher::where('student_enrollment_id', $enrollmentId)
            ->whereIn('status', ['pending', 'partial'])
            ->select('id', 'voucher_no', 'net_amount', 'paid_amount', 'remaining_amount', 'month', 'year', 'due_date')
            ->orderBy('due_date')
            ->get();

        return response()->json($vouchers);
    }
}
