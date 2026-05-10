<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'created_by', 'course_id',
        'class_session_id', 'type', 'status',
    ];

    public function creator()      { return $this->belongsTo(User::class, 'created_by'); }
    public function course()       { return $this->belongsTo(Course::class); }
    public function classSession() { return $this->belongsTo(ClassSession::class); }
    public function fields()       { return $this->hasMany(AssessmentField::class)->orderBy('sort_order'); }
    public function responses()    { return $this->hasMany(AssessmentResponse::class); }
}
