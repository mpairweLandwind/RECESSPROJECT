<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_text',
        'marks',
    ];
    public function administrator()
    {
        return $this->belongsTo(User::class, 'administrator_id')->where('role', 'admin');
    }
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
