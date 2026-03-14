<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeVoucherEditHistory extends Model
{
    protected $table = 'fee_voucher_edit_history';

    protected $fillable = [
        'voucher_id',
        'student_enrollment_id',
        'edit_reason',
        'changes',
        'edited_by',
        'edited_at',
        'requires_approval',
        'approval_request_id',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
        'requires_approval' => 'boolean',
        // Depending on your DB format, 'changes' might be json
        // 'changes' => 'array',
    ];

    // Disable auto-timestamps if they are not in the table
    // looking at the schema, it doesn't have created_at/updated_at by default laravel style maybe?
    // Actually it doesn't have created_at updated_at in standard way, so:
    public $timestamps = false;

    public function voucher()
    {
        return $this->belongsTo(FeeVoucher::class, 'voucher_id');
    }

    public function studentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    // If there is an ApprovalRequest model
    // public function approvalRequest()
    // {
    //     return $this->belongsTo(FeeApprovalRequest::class, 'approval_request_id');
    // }
}
