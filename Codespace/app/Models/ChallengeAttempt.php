<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeAttempt extends Model
{
    use HasFactory;


    protected $fillable = [
        'challenge_id',
        'participant_id',       
        'score',
        'deducted_marks',        
        'time_taken',
        'completed',             
        
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}



