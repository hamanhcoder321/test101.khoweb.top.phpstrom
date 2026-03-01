<?php

namespace App\CRMDV\Models;
use Illuminate\Database\Eloquent\Model;
class AttendanceLog extends Model
{

    protected $table = 'attendance_logs';
    protected $fillable = [
        'user_id',
        'check_in',
        'check_out',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'check_in_address',
        'check_out_address',
        'is_late',
        'is_offsite',
        'reason',
        'reason_approved',
        'reason_approved_by',
        'reason_approved_at',
        'updated_by_name',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];
    public function user(){
        return $this->belongsTo(Admin::class, 'user_id');
    }


}
