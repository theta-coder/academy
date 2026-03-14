<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeVoucher extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'voucher_no', 'student_enrollment_id', 'fee_type_id', 'academic_year_id',
        'month', 'year', 'generated_for',
        'original_amount', 'discount_amount', 'fine_amount',
        'net_amount', 'paid_amount', 'remaining_amount',
        'due_date', 'status', 'notes', 'generated_by',
    ];

    protected $casts = [
        'month'           => 'integer',
        'original_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fine_amount'     => 'decimal:2',
        'net_amount'      => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'remaining_amount'=> 'decimal:2',
        'due_date'        => 'date',
    ];

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'voucher_id');
    }

    public function fines(): HasMany
    {
        return $this->hasMany(FeeVoucherFine::class, 'voucher_id');
    }

    public function waiver(): HasOne
    {
        return $this->hasOne(FeeWaiver::class, 'voucher_id');
    }

    public function discountBreakdowns(): HasMany
    {
        return $this->hasMany(VoucherDiscountBreakdown::class, 'voucher_id');
    }

    public function onlinePaymentProofs(): HasMany
    {
        return $this->hasMany(OnlinePaymentProof::class, 'voucher_id');
    }

    public function installmentAssignment(): HasOne
    {
        return $this->hasOne(StudentInstallmentAssignment::class, 'fee_voucher_id');
    }

    public function editHistory(): HasMany
    {
        return $this->hasMany(FeeVoucherEditHistory::class, 'voucher_id');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(FeeApprovalRequest::class, 'voucher_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
