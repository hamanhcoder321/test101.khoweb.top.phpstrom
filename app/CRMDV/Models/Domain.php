<?php

namespace App\CRMDV\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $table = 'domains';

    protected $fillable = [
        'domain',
        'tld',
        'status',
        'registered_at',
        'expired_at',
        'duration_months',
        'note',
        'project_name',
    ];

    protected $dates = ['registered_at', 'expired_at'];
}
