<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptedQuestion extends Model
{
    use HasFactory;


    protected $fillable = [
        'participant_id',
        'challenge_attempt_id',
        'question_id',
        'given_answer',
        'marks_awarded',
        'time_spent',  // Time taken to answer the question in seconds (rounded up)
        'is_repeated',
        'created_at',
        'updated_at',
    ];


    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
    
}
