<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChequeTracking extends Model
{
    protected $table = 'cheque_tracking';

    protected $fillable = [
        'payment_id',
        'student_enrollment_id',
        'cheque_no',
        'cheque_date',
        'bank_name',
        'branch_name',
        'account_title',
        'amount',
        'received_date',
        'expected_clearance_date',
        'status',
        'cleared_on',
        'cleared_confirmed_by',
        'bounced_on',
        'bounce_reason',
        'bounce_reason_detail',
        'bounce_bank_penalty',
        'bounce_student_penalty',
        'bounce_penalty_voucher_id',
        'reversal_done',
        'reversal_done_by',
        'reversal_done_at',
        'resolved_at',
        'notes',
    ];

    protected $casts = [
        'cheque_date' => 'date',
        'received_date' => 'date',
        'expected_clearance_date' => 'date',
        'cleared_on' => 'date',
        'bounced_on' => 'date',
        'reversal_done' => 'boolean',
        'reversal_done_at' => 'datetime',
        'resolved_at' => 'datetime',
        'amount' => 'decimal:2',
        'bounce_bank_penalty' => 'decimal:2',
        'bounce_student_penalty' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(FeePayment::class, 'payment_id');
    }

    public function studentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function clearedConfirmedBy()
    {
        return $this->belongsTo(User::class, 'cleared_confirmed_by');
    }

    public function reversalDoneBy()
    {
        return $this->belongsTo(User::class, 'reversal_done_by');
    }

    public function penaltyVoucher()
    {
        return $this->belongsTo(FeeVoucher::class, 'bounce_penalty_voucher_id');
    }
}
