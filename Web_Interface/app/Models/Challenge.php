<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;


    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'duration',
        'number_of_questions',
        'status',
    ];

    public function administrator()
    {
        return $this->belongsTo(User::class, 'administrator_id')->where('role', 'admin');
    }


    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'challenge_participants')
            ->withTimestamps();
    }

    public function challengeAttempts()
    {
        return $this->hasMany(ChallengeAttempt::class);
    }
}
