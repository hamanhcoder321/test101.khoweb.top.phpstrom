<?php

namespace App\Modules\Courses\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{

    protected $table = 'courses_chapter';
    protected $fillable = [
        'name', 'order_no', 'course_id', 'created_at', 'updated_at',
    ];
    public function lessons(){
        return $this->hasMany(Lesson::class, 'course_chapter_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

//    protected $fillable = [
//        'name', 'link', 'level', 'order_no', 'admin_id', 'multi_cat'
//    ];
//    public function admin()
//    {
//        return $this->belongsTo(Admin::class, 'admin_id');
//    }
}
