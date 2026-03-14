<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeReminder extends Model
{
    protected $table = 'fee_reminders';

    protected $fillable = [
        'student_enrollment_id',
        'voucher_ids',
        'total_amount_reminded',
        'months_reminded',
        'channel',
        'message_content',
        'contact_number_used',
        'sent_at',
        'sent_by',
        'response',
        'promised_pay_date',
        'follow_up_date',
        'outcome',
        'outcome_date',
        'notes',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'promised_pay_date' => 'date',
        'follow_up_date' => 'date',
        'outcome_date' => 'date',
        'total_amount_reminded' => 'decimal:2',
    ];

    public function studentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
