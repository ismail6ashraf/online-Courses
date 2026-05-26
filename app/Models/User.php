<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\AttendanceLog;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone',
        'avatar', 'specialty', 'is_active', 'bio',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isInstructor(): bool { return $this->role === 'instructor'; }
    public function isStudent(): bool  { return $this->role === 'student'; }

    public function coursesAsInstructor() { return $this->hasMany(Course::class, 'instructor_id'); }
    public function enrolledCourses()     { return $this->belongsToMany(Course::class, 'course_students', 'student_id'); }
    public function classSessionsAsInstructor() { return $this->hasMany(ClassSession::class, 'instructor_id'); }
    public function tasks()               { return $this->hasMany(InstructorTask::class, 'instructor_id'); }
    public function alerts()              { return $this->hasMany(Alert::class, 'target_user_id'); }
    public function speechLogs()          { return $this->hasMany(SpeechLog::class, 'speaker_id'); }
    public function behaviorIncidents()   { return $this->hasMany(BehaviorIncident::class); }
    public function dataLeakageIncidents(){ return $this->hasMany(DataLeakageIncident::class); }
    public function reports()             { return $this->hasMany(Report::class, 'generated_by'); }
    public function attendances(){ return $this->hasMany(AttendanceLog::class, 'user_id'); }
}
