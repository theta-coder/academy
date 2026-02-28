<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentInstallmentAssignment extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'student_enrollment_id', 'installment_plan_id', 'fee_voucher_id',
        'total_amount', 'amount_paid', 'remaining_amount',
        'status', 'approved_by', 'notes',
    ];

    protected $casts = [
        'total_amount'     => 'decimal:2',
        'amount_paid'      => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function installmentPlan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class);
    }

    public function feeVoucher(): BelongsTo
    {
        return $this->belongsTo(FeeVoucher::class, 'fee_voucher_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(InstallmentSchedule::class, 'assignment_id');
    }
}
