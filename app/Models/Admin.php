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

namespace App\Models;

use App\Http\Helpers\CommonHelper;
use DB;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;
    use SoftDeletes;

    protected $table = 'admin';

    protected $fillable = [
        'name', 'email', 'password', 'address', 'tel', 'image', 'gender', 'birthday', 'api_token', 'password_md5', 'fb_id', 'role_id','room_id','work_time','image','ID_card_photo_on_the_front','ID_card_photo_on_the_back','short_name','status','gender','fcm'
    ];
//    protected $fillable = [
//        'cccd','password', 'name', 'email', 'password', 'address', 'tel', 'image', 'gender', 'birthday', 'password_md5', 'facebook', 'role_id','zalo','skype','may_cham_cong_id','address','gmail','image','note','intro','date_start_work','room_id','code','work_time'
//    ];

    protected $hidden = ['password', 'remember_token'];

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'role_admin', 'admin_id', 'role_id');
    }

    public function recharges()
    {
        return $this->hasMany(Recharges::class, 'admin_id', 'id');
    }
    public function admin() {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function saler() {
        return $this->belongsTo(Admin::class, 'saler_id'); // 1 sale
    }

    public function marketer() {
        return $this->belongsTo(Admin::class, 'marketer_id'); // 1 marketer
    }

    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function phong_ban() {
        return $this->belongsTo(Tag::class, 'room_id');
    }

    public function service() {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function admin_log()
    {
        return $this->hasMany(Website::class, 'admin_id', 'id');
    }

    public function generateToken()
    {
        $this->api_token = str_random(60) . time();
        $this->save();

        return $this->api_token;
    }
    public static function checkMaxActiveAdmins()
    {
        $maxAdmins = env('MAX_ACCOUNT_ADMIN');
        $activeAdminsCount = self::where('status', 1)->count();
        if ($activeAdminsCount >= $maxAdmins) {
            throw new \Exception('Bạn chỉ được phép tạo tối đa ' . $maxAdmins . ' tài khoản Admin', 999);
        }
    }
}
