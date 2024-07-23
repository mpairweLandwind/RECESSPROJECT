<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectedParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'username',
        'firstname',
        'lastname',
        'school_id',
        'reason',
        'email',
        'date_of_birth',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
