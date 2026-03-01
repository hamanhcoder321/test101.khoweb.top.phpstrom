<?php
namespace App\CRMDV\Models;

use App\CRMDV\Models\Bill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin;
use App\CRMDV\Models\Tag;
class BillReceipts extends Model
{
    use SoftDeletes;

    protected $table = 'bill_receipts';

    protected $fillable = [
        'bill_id' , 'price' , 'date', 'admin_id','saler_id', 'note', 'status', 'receiving_account','employees','image','invite_by','type','so_hoa_don'
    ];
//    public function tags()
//    {
//        return $this->hasOne(Tag::class, 'receiving_account','id');
//    }
    public function bill() {
        return $this->belongsTo(Bill::class, 'bill_id', 'id');
    }


    public function admin() {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function saler() {
        return $this->belongsTo(Admin::class, 'saler_id', 'id');
    }

    public function receivingAccount() {
        return $this->belongsTo(Tag::class, 'receiving_account', 'id');
    }
}
