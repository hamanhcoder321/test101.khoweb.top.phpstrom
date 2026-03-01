<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // <— cần import
use App\Models\Setting;
use Illuminate\Http\Request;

class ModulesToggleController extends Controller
{
    // KHÔNG dùng "array" type-hint ở đây để tránh lỗi trên PHP < 7.4
    protected $valid = ['crm','bill','dhbill','document','timekeeper','course'];

    public function form()
    {
        $current = json_decode(Setting::getValue('modules_active','[]'), true) ?: [];
        return view('admin.modules.form', [
            'current' => $current,
            'valid'   => $this->valid,
        ]);
    }

    public function save(Request $r)
    {
        $modules = array_values(array_intersect($r->input('modules', []), $this->valid));
        Setting::setValue('modules_active', json_encode($modules, JSON_UNESCAPED_UNICODE));
        return back()->with('ok', 'Đã lưu: ' . implode(', ', $modules));
    }
}
