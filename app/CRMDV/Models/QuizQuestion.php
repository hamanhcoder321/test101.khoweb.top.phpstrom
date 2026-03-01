<?php

namespace App\CRMDV\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';
    protected $fillable = [
        'quiz_id','question','a','b','c','d','answer',
        'explain_the_answer','quiz_question_group_id'
    ];
}