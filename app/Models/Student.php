<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'admission_no', 'parent_id', 'student_name', 'date_of_birth', 'gender',
        'photo', 'whatsapp_number', 'b_form_no', 'blood_group', 'religion',
        'is_hafiz', 'student_type', 'previous_school', 'medical_condition', 'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active'     => 'boolean',
        'is_hafiz'      => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function activeEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSchool($query)
    {
        return $query->whereIn('student_type', ['school', 'both']);
    }

    public function scopeAcademy($query)
    {
        return $query->whereIn('student_type', ['academy', 'both']);
    }
}
