<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeePayment extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'receipt_no', 'voucher_id', 'student_enrollment_id',
        'paid_amount', 'payment_date', 'payment_method',
        'bank_name', 'transaction_ref',
        'received_by', 'is_advance', 'notes',
    ];

    protected $casts = [
        'paid_amount'  => 'decimal:2',
        'payment_date' => 'date',
        'is_advance'   => 'boolean',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(FeeVoucher::class, 'voucher_id');
    }

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function refund(): HasOne
    {
        return $this->hasOne(FeeRefund::class, 'payment_id');
    }

    public function advanceAdjustments(): HasMany
    {
        return $this->hasMany(FeeAdvanceAdjustment::class, 'from_payment_id');
    }

    public function onlinePaymentProof(): HasOne
    {
        return $this->hasOne(OnlinePaymentProof::class, 'fee_payment_id');
    }

    public function installmentSchedule(): HasOne
    {
        return $this->hasOne(InstallmentSchedule::class, 'payment_id');
    }

    public function chequeTracking(): HasOne
    {
        return $this->hasOne(ChequeTracking::class, 'payment_id');
    }

    public function scopeAdvance($query)
    {
        return $query->where('is_advance', true);
    }
}
