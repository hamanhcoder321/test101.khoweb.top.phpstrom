<?php

namespace App\Modules\Courses\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{

    protected $table = 'courses_lesson';

//    protected $fillable = [
//        'name', 'link', 'level', 'order_no', 'admin_id', 'multi_cat'
//    ];
//    public function admin()
//    {
//        return $this->belongsTo(Admin::class, 'admin_id');
//    }
}
