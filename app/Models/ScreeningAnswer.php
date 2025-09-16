<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'screening_id',
        'question_id',
        'answer',
    ];

    /**
     * Get the screening that owns the answer
     */
    public function screening()
    {
        return $this->belongsTo(WellBeingScreening::class, 'screening_id');
    }

    /**
     * Get the question that owns the answer
     */
    public function question()
    {
        return $this->belongsTo(ScreeningQuestion::class, 'question_id');
    }
}
