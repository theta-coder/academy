<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentScholarship extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'student_enrollment_id', 'scholarship_id', 'academic_year_id',
        'awarded_on', 'valid_from', 'valid_to',
        'position_achieved', 'marks_percentage', 'awarded_by',
        'status', 'revoke_reason', 'notes',
    ];

    protected $casts = [
        'awarded_on'       => 'date',
        'valid_from'       => 'date',
        'valid_to'         => 'date',
        'marks_percentage' => 'decimal:2',
    ];

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }
}
