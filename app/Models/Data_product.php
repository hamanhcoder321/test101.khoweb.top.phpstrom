<?php

namespace App\Models ;

use App\CRMDV\Models\Exp;
use App\CRMDV\Models\Post;
use App\CRMDV\Models\User;
use Illuminate\Database\Eloquent\Model;

class Data_product extends Model
{

    protected $table = 'data_products';

    protected $fillable = [
        'name' , 'category' , 'nhom_sp' , 'name_product' , 'ma_vach' , 'dvt' , 'price', 'link_bhx','da_tim_bhx','avatar'
    ];


}
