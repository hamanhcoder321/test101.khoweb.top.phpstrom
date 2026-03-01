<?php

namespace App\Modules\Courses\Models;
use Illuminate\Database\Eloquent\Model;

class UserLessonProgress extends Model
{
    protected $table = 'user_lesson_progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'completed',
        'progress_time',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
}
