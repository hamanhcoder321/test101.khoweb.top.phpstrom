<?php
namespace App\Modules\HBBill\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin;

class Bill extends Model
{
    use SoftDeletes;

    protected $table = 'bills';

    protected $fillable = [
        'service_id','receipt_method' , 'user_gender', 'date' , 'coupon_code' , 'note' , 'status' , 'total_price' , 'customer_id', 'user_tel', 'user_name', 'user_email', 'user_address', 'user_wards', 'user_city_id', 'product_or_service','mst','image',
        'receipt_method2','expiry_date','coupon_code2','customer_legal_id','invite_more_services','web_lock','web_lock_date','customer_email2','customer_address2','customer_city_id2','customer_district_id2','customer_gender2','customer_ward_id2','group_no',
        'domain','exp_price','auto_extend','registration_date','retention_time','guarantee','handover_landingpage2','bill_parent','service_name2','handover_wp2','curator_ids','staff_care','saler_id','marketer_id','customer_note','update_to_codes','contacted_log_last','contract_time',
        'total_price_contract','price_month','price_period','total_received','dich_vu_ban_tiep','account_note','link_hd','hd_luu_tru','bbtl_luu_tru','product_or_service'
    ];


    public function sale()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'saler_id', 'id');
    }
    public function staffCare()
    {
        return $this->belongsTo(Admin::class, 'staff_care', 'id');
    }
    public function legal_representative()
    {
        return $this->belongsTo(\App\Models\Admin::class, 'customer_legal_id', 'id');
    }
    public function admin() {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function customer() {
        return $this->belongsTo(Lead::class, 'customer_id', 'id');
    }

    public function customer_legal() {
        return $this->belongsTo(User::class, 'customer_legal_id', 'id');
    }

    public function saler() {
        return $this->belongsTo(Admin::class, 'saler_id', 'id');
    }
    public function marketer() {
        return $this->belongsTo(\App\Models\Admin::class, 'marketer_id', 'id');
    }

    public function orders() {
        return $this->hasMany(Order::class, 'order_id', 'id');
    }

    public function service() {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function ldp() {
        return $this->hasOne(Landingpage::class, 'bill_id', 'id');
    }

    public function bill_finance() {
        return $this->hasOne(BillFinance::class, 'bill_id', 'id');
    }

    public function bill_progress() {
        return $this->hasOne(BillProgress::class, 'bill_id', 'id');
    }
}
