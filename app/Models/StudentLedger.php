<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLedger extends Model
{
    use HasFactory;

    protected $table = 'student_ledger';

    protected $fillable = [
        'student_enrollment_id', 'transaction_type', 'amount',
        'description', 'reference_type', 'reference_id',
        'balance_after', 'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDebits($query)
    {
        return $query->where('transaction_type', 'debit');
    }

    public function scopeCredits($query)
    {
        return $query->where('transaction_type', 'credit');
    }
}
