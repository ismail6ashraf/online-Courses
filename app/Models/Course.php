<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ClassSession;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'instructor_id',
        'level',
        'language',
        'duration_hours',
        'status',
        'cover_image',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_students', 'course_id', 'student_id')
            ->withPivot(['status', 'enrolled_at'])
            ->withTimestamps();
    }
    public function sessions()
    {
        return $this->hasMany(ClassSession::class);
    }
    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
    public function materials()
    {
        return $this->hasMany(CourseMaterial::class);
    }
    public function assignments()
    {
        return $this->hasMany(CourseAssignment::class);
    }
}
