<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentSchedule extends Model
{
    use HasFactory;

    protected $table = 'installment_schedule';

    protected $fillable = [
        'assignment_id', 'kist_number', 'kist_amount',
        'due_date', 'paid_amount', 'payment_date', 'status', 'payment_id', 'notes',
    ];

    protected $casts = [
        'kist_amount'  => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_date'     => 'date',
        'payment_date' => 'date',
        'kist_number'  => 'integer',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(StudentInstallmentAssignment::class, 'assignment_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'payment_id');
    }
}
