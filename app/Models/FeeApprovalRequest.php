<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeApprovalRequest extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'fee_approval_requests';

    protected $fillable = [
        'request_type',
        'student_enrollment_id',
        'voucher_id',
        'requested_amount',
        'requested_percent',
        'current_amount',
        'reason',
        'supporting_notes',
        'requested_by',
        'requested_at',
        'urgency',
        'status',
        'reviewed_by',
        'reviewed_at',
        'reviewer_remarks',
        'action_reference_type',
        'action_reference_id',
        'action_taken_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'action_taken_at' => 'datetime',
        'requested_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'requested_percent' => 'decimal:2',
    ];

    public function studentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function voucher()
    {
        return $this->belongsTo(FeeVoucher::class, 'voucher_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
