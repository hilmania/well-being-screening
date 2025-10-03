<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'question_type',
        'placeholder',
        'group_name',
        'is_active',
        'order',
    ];

    protected $casts = [
        'question_type' => 'string',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the answers for the question
     */
    public function answers()
    {
        return $this->hasMany(ScreeningAnswer::class, 'question_id');
    }

    /**
     * Check if question is likert scale type
     */
    public function isLikertType()
    {
        return $this->question_type === 'likert';
    }

    /**
     * Check if question is text input type
     */
    public function isTextType()
    {
        return $this->question_type === 'text';
    }

    /**
     * Scope to get only active questions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get questions by group
     */
    public function scopeByGroup($query, $groupName)
    {
        return $query->where('group_name', $groupName);
    }

    /**
     * Scope to get active questions ordered
     */
    public function scopeActiveOrdered($query)
    {
        return $query->where('is_active', true)->orderBy('order')->orderBy('id');
    }
}
