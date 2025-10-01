<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteersResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'screening_id',
        'volunteer_id',
        'notes',
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
     * Get the volunteer that owns the response
     */
    public function volunteer()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }
}
