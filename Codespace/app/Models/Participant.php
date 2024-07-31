<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->belongsTo(School::class,'id');
    }
    public function challenges() {
        return $this->belongsToMany(Challenge::class, 'challenge_participants');
    }
    
    public function attemptedQuestions(): HasMany
    {
        return $this->hasMany(AttemptedQuestion::class);
    }

    public function challengeAttempts(): HasMany
    {
        return $this->hasMany(ChallengeAttempt::class);
    }
    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }
    public function challengeParticipants(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class);
    }
}
