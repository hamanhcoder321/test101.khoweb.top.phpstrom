<?php

namespace App\CRMDV\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
use App\CRMDV\Models\LeadContactedLog;
class Lead extends Model
{
    protected $table = 'leads';

    protected $fillable = [
        'tel',
        'email',
        'rate',
        'dating',
        'profile',
        'need',
        'created_at',
        'updated_at',
        'name',
        'status',
        'saler_ids',
        'marketer_ids',
        'service',
        'admin_id',
        'project',
        'terms',
        'contacted_log_last',
        'discount',
        'product',
        'received_date',
        'reason_refusal',
        'advise_suggest',
        'source',
        'tinh',
        'telesale_id',
        'partner',
        'company',
        'tax_code',
        'address',
        'founded_date',
        'tags',
        'image',
        'topic',
        'saler_id',
        'marketer_id',
        'staff_care'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function contactedLogs() {
        return $this->hasMany(LeadContactedLog::class, 'lead_id');
    }

}
