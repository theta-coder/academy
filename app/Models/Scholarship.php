<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scholarship extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'scholarship_name', 'criteria', 'discount_type', 'discount_value',
        'applies_to', 'applicable_fee_type_id', 'max_recipients',
        'is_renewable', 'description', 'is_active', 'created_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'is_active'      => 'boolean',
        'is_renewable'   => 'boolean',
        'max_recipients' => 'integer',
    ];

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class, 'applicable_fee_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function studentScholarships(): HasMany
    {
        return $this->hasMany(StudentScholarship::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
