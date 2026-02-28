<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEnrollment extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'student_id', 'academic_year_id', 'branch_id', 'class_section_id',
        'roll_number', 'admission_date', 'leaving_date',
        'enrollment_type', 'sibling_order', 'status', 'leaving_reason', 'remarks', 'created_by',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'leaving_date'   => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function feeVouchers(): HasMany
    {
        return $this->hasMany(FeeVoucher::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function feeConcessions(): HasMany
    {
        return $this->hasMany(StudentFeeConcession::class);
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(StudentLedger::class);
    }

    public function advanceBalance(): HasOne
    {
        return $this->hasOne(StudentAdvanceBalance::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(FeeRefund::class);
    }

    public function installmentAssignments(): HasMany
    {
        return $this->hasMany(StudentInstallmentAssignment::class);
    }

    public function onlinePaymentProofs(): HasMany
    {
        return $this->hasMany(OnlinePaymentProof::class);
    }

    public function studentScholarships(): HasMany
    {
        return $this->hasMany(StudentScholarship::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
