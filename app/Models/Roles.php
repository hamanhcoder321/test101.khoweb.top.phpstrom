<?php

/**
 * Roles Model
 *
 * Roles Model manages Roles operation. 
 *
 * @category   Roles
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

use Illuminate\Database\Eloquent\Model;
use DB;

class Roles extends Model

{
    protected $table = 'roles';
    protected $fillable = ['name','display_name','description'];
    public static function permission_role($id)
    {
        return DB::table('permission_role')->where('role_id', $id)->pluck('permission_id');
    }
    public static function role_admin($id)
    {
        return DB::table('role_admin')->where('admin_id', $id)->first();
    }
    public function admin(){
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function admins()
    {
        return $this->belongsToMany(
            Admin::class,        // model liên kết
            'role_admin',        // bảng trung gian
            'role_id',           // khóa ngoại trỏ về roles
            'admin_id',          // khóa ngoại trỏ về admin
            'id',                // khóa chính roles
            'id'           // khóa chính admin
        )->select('admin.admin_id', 'admin.name');
    }
}
