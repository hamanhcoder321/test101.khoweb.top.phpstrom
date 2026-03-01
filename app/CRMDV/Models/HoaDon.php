<?php
namespace App\CRMDV\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin;
use App\CRMDV\Models\Service;

class HoaDon extends Model
{
   

    protected $table = 'hoa_don';

    protected $fillable = [
        'cty_name',
        'cty_mst',
        'so_hoa_don',
        'ky_hieu',
        'tien_hang',
        'tien_thue_gtgt',
        'tong_tien',
        'status_CQT',
        'ngay_ky',
        'status',
    ];


}
