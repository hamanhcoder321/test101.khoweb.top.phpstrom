<?php
namespace App\Modules\HBBill\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin;

class BillReceipts extends Model
{
    use SoftDeletes;

    protected $table = 'bill_receipts';

    protected $fillable = [
        'bill_id' , 'date' , 'price', 'admin_id' , 'saler_id' , 'note','so_hoa_don','receiving_account
    '
    ];

    public function bill() {
        return $this->belongsTo(Bill::class, 'bill_id', 'id');
    }

    public function admin() {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function saler() {
        return $this->belongsTo(Admin::class, 'saler_id', 'id');
    }
}
