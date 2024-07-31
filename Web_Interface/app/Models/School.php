<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function participants():HasMany
    {
        return $this->hasMany(Participant::class);
    }
       
    public function representative()
    {
        return $this->belongsTo(User::class, 'representative_id');
    }
}
