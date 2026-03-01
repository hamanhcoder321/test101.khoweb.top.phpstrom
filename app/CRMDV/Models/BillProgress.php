<?php
namespace App\CRMDV\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

class BillProgress extends Model
{

    protected $table = 'bill_progress';
    
    protected $fillable = [
        'bill_id' , 'status' , 'yctk' , 'dh_id', 'kt_id', 'kh_xong_image', 'reminder_customer', 'rate', 'rate_content','tk_id','dd_id'
    ];

    public function bill() {
        return $this->belongsTo(Bill::class, 'bill_id', 'id');
    }

    public function dieu_hanh() {
        return $this->belongsTo(Admin::class, 'dh_id', 'id');
    }

    public function ky_thuat() {
        return $this->belongsTo(Admin::class, 'kt_id', 'id');
    }
    public function trien_khai() {
        return $this->belongsTo(Admin::class, 'tk_id', 'id');
    }

    public function dai_dien() {
        return $this->belongsTo(Admin::class, 'dd_id', 'id');
    }
}
