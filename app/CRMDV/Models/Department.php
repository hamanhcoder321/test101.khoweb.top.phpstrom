<?php

namespace App\CRMDV\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\CRMDV\Models\Admin;
class Department extends Model
{
    use SoftDeletes;
    protected $table = 'department';
    protected $fillable = ['name','created_at','address','description','admin_id','deleted_at'];
    public function manager()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function admins()
    {
        return $this->hasMany(Admin::class, 'room_id', 'id');
    }
}