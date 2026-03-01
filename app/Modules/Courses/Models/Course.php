<?php

namespace App\Modules\Courses\Models;

use Illuminate\Database\Eloquent\Model;
use App\CRMDV\Models\Room;
class Course extends Model
{

    protected $table = 'courses';
    protected $casts = [
        'create_at' => 'date:Y-m-d',
    ];
    protected $fillable = [
        'name', 'link', 'level', 'order_no', 'admin_id', 'multi_cat','room_id',
    ];
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'course_id');
    }

    public function room_id(){
        return $this->belongsTo(Room::class, 'room_id');
    }
}
