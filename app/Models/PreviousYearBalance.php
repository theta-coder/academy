<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreviousYearBalance extends Model
{
    protected $table = 'previous_year_balances';

    protected $fillable = [
        'student_enrollment_id',
        'from_academic_year_id',
        'to_academic_year_id',
        'original_outstanding',
        'breakup',
        'recovered_amount',
        'remaining_amount',
        'carry_forward_date',
        'carry_forward_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'original_outstanding' => 'decimal:2',
        'recovered_amount'     => 'decimal:2',
        'remaining_amount'     => 'decimal:2',
        'carry_forward_date'   => 'date',
        'breakup'              => 'array',
    ];

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class, 'student_enrollment_id');
    }

    public function fromAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'from_academic_year_id');
    }

    public function toAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_year_id');
    }

    public function carriedForwardBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'carry_forward_by');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }
}
