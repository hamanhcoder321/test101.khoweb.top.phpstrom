<?php

namespace App\Modules\HBDashboard\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\CommonHelper;
use App\Models\Setting;
use Auth;
use DB;
use Illuminate\Http\Request;
use Mail;
use App\Modules\HBDashboard\Controllers\Helpers\CRMDVHelper;

class DashboardController extends Controller
{

    public function dashboardSoftware()
    {
        $data['page_title'] = 'Thống kê';
        $data['page_type'] = 'list';

        if (!in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['truong_phong_sale', 'super_admin', 'sale'])) {
            return redirect('/admin/profile');
        }

        if (CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name') == 'customer') {
            return view('HBDashboard.dashboard.dashboard_customer', $data);
        } elseif (in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['super_admin', 'giam_doc_kinh_doanh', 'tro_ly_giam_doc'])) {
            return view('HBDashboard.dashboard.dashboard', $data);
        } elseif (in_array(CommonHelper::getRoleName(\Auth::guard('admin')->user()->id, 'name'), ['sale', 'marketing', 'truong_phong_sale', 'telesale'])) {
            return view('HBDashboard.dashboard.dashboard_sale', $data);
        } else {
            return redirect('admin/profile');
        }
    }

    public function dsKyThuat() {
        $data['page_title'] = 'Danh sách kỹ thuật';
        $data['page_type'] = 'list';

        return view('HBDashboard.dashboard.ds_ky_thuat', $data);
    }
}
