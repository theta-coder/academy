<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAdvanceBalance extends Model
{
    use HasFactory;

    protected $table = 'student_advance_balance';

    public $timestamps = false;

    protected $fillable = [
        'student_enrollment_id', 'balance', 'last_transaction_id', 'last_updated',
    ];

    protected $casts = [
        'balance'      => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function lastTransaction(): BelongsTo
    {
        return $this->belongsTo(StudentLedger::class, 'last_transaction_id');
    }
}
