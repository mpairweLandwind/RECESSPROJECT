<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'school_id',
        'challenge_id',
        'attempts_left',
        'total_score',
        'completed',
        'time_taken',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'participant_id');
    }
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    public function challenge()
    {
        return $this->belongsTo(Challenge::class, 'challenge_id');
    }

    public function attemptedQuestions()
    {
        return $this->hasMany(AttemptedQuestion::class);
    }

    public function challengeAttempts()
    {
        return $this->hasMany(ChallengeAttempt::class);
    }
    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }
    public function challengeParticipants()
    {
        return $this->hasMany(ChallengeParticipant::class);
    }
}
