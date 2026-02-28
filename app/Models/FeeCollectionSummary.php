<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeCollectionSummary extends Model
{
    use HasFactory;

    protected $table = 'fee_collection_summary';

    protected $fillable = [
        'branch_id', 'academic_year_id', 'summary_month', 'summary_year',
        'total_students', 'total_billed', 'total_discount', 'total_fine',
        'total_net', 'total_collected', 'total_pending', 'total_waived',
        'vouchers_paid', 'vouchers_partial', 'vouchers_pending', 'generated_at',
    ];

    protected $casts = [
        'summary_month'   => 'integer',
        'total_billed'    => 'decimal:2',
        'total_discount'  => 'decimal:2',
        'total_fine'      => 'decimal:2',
        'total_net'       => 'decimal:2',
        'total_collected' => 'decimal:2',
        'total_pending'   => 'decimal:2',
        'total_waived'    => 'decimal:2',
        'generated_at'    => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
