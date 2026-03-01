<?php
namespace App\CRMDV\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin;

class GiaoDich extends Model
{
    protected $table = 'giao_dich';

    protected $fillable = [
        'transaction_date' , 'transaction_number' , 'transaction_content', 'amount', 'tk_doi_ung_ten', 'tk_doi_ung', 'status', 'note', 'bill_receipts_check'
    ];
    public function billReceipts()
    {
        return $this->hasMany(
            \App\Modules\HBBill\Models\BillReceipts::class,
            'giao_dich_id'
        );
    }

}
