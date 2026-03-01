<?php

namespace App\CRMDV\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Quiz extends Model
{
    protected $table = 'quiz'; // bảng tên là quiz (không theo chuẩn Laravel)

    public $timestamps = true; // có created_at, updated_at

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'name',
        'ngay_thuc_hien',
        'courses_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'ngay_thuc_hien' => 'integer',
        'courses_id' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    // Một quiz có nhiều câu hỏi
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id', 'id');
    }

    // Một quiz thuộc về một khóa học (nếu có)
    public function course()
    {
        return $this->belongsTo(\App\CRMDV\Models\Course::class, 'courses_id', 'id'); // điều chỉnh namespace nếu cần
    }

    // Lịch sử làm bài của quiz này
    public function histories()
    {
        return $this->hasMany(QuizHistory::class, 'quiz_id', 'id');
    }

    // ==================== ACCESSORS ====================

    // Format ngày tạo đẹp hơn
    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y H:i') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value && $value !== '0000-00-00 00:00:00'
            ? Carbon::parse($value)->format('d/m/Y H:i')
            : null;
    }

    // Lấy tên ngày thực hiện (ví dụ: Ngày thứ 5)
    public function getNgayThucHienTextAttribute()
    {
        return "Ngày thứ " . $this->ngay_thuc_hien;
    }
}