<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeAttempt extends Model
{
    use HasFactory;


    protected $fillable = [
        'participant_id',
        'challenge_id',
        'time_taken',
        'attempts',
        'correct_answers',
        'total_score',
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



