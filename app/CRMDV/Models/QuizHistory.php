<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizHistory extends Model
{
    use HasFactory;

    protected $table = 'quiz_history';

    protected $fillable = [
        'admin_id',
        'quiz_id',
        'scores',
        'test_data',
    ];

    protected $casts = [
        'test_data' => 'array', // hoặc 'object' nếu bạn muốn object
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Quan hệ với User (giả sử người làm bài là User/Admin)
    public function user()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Quan hệ với Quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    // Scope tìm theo điểm
    public function scopeByScore($query, $score)
    {
        return $query->where('scores', $score);
    }

    // Scope tìm theo người dùng
    public function scopeByUser($query, $userId)
    {
        return $query->where('admin_id', $userId);
    }
}