<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
    ];

    /**
     * Get the answers for the question
     */
    public function answers()
    {
        return $this->hasMany(ScreeningAnswer::class, 'question_id');
    }
}
