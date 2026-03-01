<?php

/**
 * Admin Model
 *
 * Admin Model manages Admin operation.
 *
 * @category   Admin
 * @package    vRent
 * @author     Techvillage Dev Team
 * @copyright  2017 Techvillage
 * @license
 * @version    1.3
 * @link       http://techvill.net
 * @since      Version 1.3
 * @deprecated None
 */

namespace App\CRMDV\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\CRMDV\Models\Roles;
use App\CRMDV\Models\Department;
class Admin extends Model
{
    use SoftDeletes;
    protected $table = 'admin';

    protected $fillable = [
       'cccd','password', 'name', 'email', 'password', 'address', 'tel', 'image', 'gender', 'birthday', 'password_md5', 'facebook', 'role_id','zalo','skype','may_cham_cong_id','address','gmail','image','note','intro','date_start_work','room_id','code','work_time','fcm'
    ];

    protected $hidden = ['password', 'remember_token'];

    public function roles()
    {
        return $this->belongsToMany(\App\Models\Roles::class, 'role_admin', 'admin_id', 'role_id');
    }
    public function roles1()
    {
        return $this->belongsToMany(Roles::class, 'role_admin', 'admin_id', 'role_id', 'admin_id', 'id');
    }
    public function roleNames(){
        return $this->belongsToMany(\App\Models\Roles::class, 'roles', 'admin_id', 'id');
    }

    public function saler() {
        return $this->belongsTo($this, 'saler_id', 'id');
    }

    public function invite_by() {
        return $this->belongsTo($this, 'invite_by', 'id');
    }
    public function invite() {
        return $this->belongsTo($this, 'invite_by', 'id');
    }

    public function room() {
        return $this->belongsTo(\App\CRMDV\Models\Room::class, 'room_id', 'id');
    }


    public function managingDepartments() {
        return $this->hasOne(Department::class, 'admin_id', 'id');
    }
   
    
}
