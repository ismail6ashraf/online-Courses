<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentField extends Model
{
    protected $fillable = ['assessment_id', 'label', 'type', 'options', 'required', 'sort_order'];

    protected function casts(): array
    {
        return [
            'options'  => 'array',
            'required' => 'boolean',
        ];
    }

    public function assessment() { return $this->belongsTo(Assessment::class); }
}
