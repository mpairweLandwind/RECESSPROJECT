<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'district',
        'registration_number',
        'email_of_representative',
        'email',
        'representative_name',
        'validated',
    ];


    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function representative()
    {
        return $this->belongsTo(User::class, 'representative_id');
    }
}
