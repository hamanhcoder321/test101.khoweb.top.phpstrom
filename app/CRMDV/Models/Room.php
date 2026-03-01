<?php

namespace App\CRMDV\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'parent_id', 'name', 'code', 'manager_id', 'employee_count',
        'address', 'established_date', 'description', 'status'
    ];
    protected $casts = [
        'established_date' => 'date',
        'status' => 'boolean',
    ];

    // Quan hệ cha - con
    public function parent()
    {
        return $this->belongsTo(Room::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Room::class, 'parent_id');
    }
    // Trưởng phòng
    public function manager()
    {
        return $this->belongsTo(Admin::class, 'manager_id');
    }
}