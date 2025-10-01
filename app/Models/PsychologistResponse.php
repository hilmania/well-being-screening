<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PsychologistResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'screening_id',
        'psychologist_id',
        'diagnosis',
        'recommendation',
        'attachment',
    ];

    /**
     * Get the screening that owns the response
     */
    public function screening()
    {
        return $this->belongsTo(WellBeingScreening::class, 'screening_id');
    }

    /**
     * Get the psychologist that owns the response
     */
    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }
}
