<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructureChangeLog extends Model
{
    protected $table = 'fee_structure_change_log';

    public $timestamps = false; // Based on schema

    protected $fillable = [
        'fee_structure_id',
        'branch_id',
        'class_id',
        'fee_type_id',
        'academic_year_id',
        'old_amount',
        'new_amount',
        'old_due_day',
        'new_due_day',
        'change_reason',
        'effective_from',
        'affects_existing_vouchers',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'changed_at' => 'datetime',
        'affects_existing_vouchers' => 'boolean',
        'old_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
