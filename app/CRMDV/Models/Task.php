<?php
namespace App\CRMDV\Models;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'task';
    protected $fillable = [
    'bill_id', 'name', 'description', 'admin_id', 'status',
    'priority', 'progress', 'deadline', 'started_at', 'completed_at'
    ];
    public $timestamps = true;
    public function bill()
    {
    return $this->belongsTo(\App\CRMDV\Models\Bill::class, 'bill_id');
    }

    public function admin()
    {
    return $this->belongsTo(\App\Models\Admin::class, 'admin_id');
    }
    public function createdBy(){
        return $this->belongsTo(\App\Models\Admin::class, 'created_by');
    }

//    public function getStatusLabelAttribute()
//    {
//        return [
//            'chua_bat_dau' => 'Chưa bắt đầu',
//            'dang_lam'     => 'Đang làm',
//            'tam_dung'     => 'Tạm dừng',
//            'hoan_thanh'   => 'Hoàn thành',
//            'huy'          => 'Hủy'
//            ][$this->status] ?? $this->status;
//    }
//
//    public function getPriorityLabelAttribute()
//    {
//        return [
//        'cao'       => 'Cao',
//        'trung_binh'=> 'Trung bình',
//        'thap'      => 'Thấp'
//        ][$this->priority] ?? $this->priority;
//        }
    }