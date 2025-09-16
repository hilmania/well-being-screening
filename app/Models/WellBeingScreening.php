<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WellBeingScreening extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'screening_date',
        'score',
        'result',
    ];

    protected $casts = [
        'screening_date' => 'date',
    ];

    /**
     * Get the user that owns the screening
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answers for the screening
     */
    public function answers()
    {
        return $this->hasMany(ScreeningAnswer::class, 'screening_id');
    }

    /**
     * Get the volunteer responses for the screening
     */
    public function volunteerResponses()
    {
        return $this->hasMany(VolunteersResponse::class, 'screening_id');
    }

    /**
     * Get the psychologist responses for the screening
     */
    public function psychologistResponses()
    {
        return $this->hasMany(PsychologistResponse::class, 'screening_id');
    }
}
