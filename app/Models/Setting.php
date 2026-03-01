<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model {
    protected $table = 'settings';
    public $timestamps = false;
    protected $fillable = ['name','value'];

    public static function getValue(string $name, $default=null) {
        $row = static::where('name',$name)->first();
        return $row ? $row->value : $default;
    }
    public static function setValue(string $name, $value) {
        return static::updateOrCreate(['name'=>$name], ['value'=>$value]);
    }
}
