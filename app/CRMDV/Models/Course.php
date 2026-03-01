<?php

namespace App\CRMDV\Models;

use App\Modules\Courses\Models\Chapter;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'link',
        'level',
        'image',
        'order_no',
        'admin_id',
        'multi_cat',
        'created_at',
        'updated_at',
        'status',
        'author_name',
        'exam_ role_ids',
        'exam_days_take_this',
        'note',
        'content',
        'short_content',
        'type',
        'ngay_thuc_hien',
        'room_id'
    ];

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'course_id');
    }


    // Quan hệ: 1 course có admin tạo ra (nếu bạn có model Admin)
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

}
