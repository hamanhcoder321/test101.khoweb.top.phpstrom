<?php

use App\Models\Setting;

Route::get('/', function () {
    /*$bills = \Modules\WebBill\Models\Bill::all();
    foreach ($bills as $bill) {
        $bill->registration_date = $bill->created_at;
        $bill->save();
    }
    die('ok');*/
    return redirect('/admin');
});


Route::get('aircaresolutionshk-send-mail', function (\Illuminate\Http\Request $r) {
    print "Bắt đầu gửi mail\n";

    //  Lấy template gửi mail
    $camp = App\Models\MarketingMail::find(17);

    //  Lấy thông tin người nhận là email đầu tiên trong mảng
    $user = (object)[
        'email' => 'webhobasoft@gmail.com',
        'name' => 'Admin',
        'id' => null
    ];

    $html = '<table>';

    foreach ($r->all() as $key => $value) {
        $html .= '<tr>';
        $html .= '<td>'.$key.':</td>';
        $html .= '<td>'.$value.'</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';

    $data = [
        'sender_account' => $camp->email_account,
        'user' => $user,
        'subject' => '[ aircaresolutionshk.com ] Notification of new customer contact',
        'content' => $html,
        'cc' => [
            'hoanghung.developer@gmail.com',
        ]
    ];

    \Mail::to($data['user'])->send(new App\Mail\MailServer($data));
    die('Gửi mail thành công!');
});

Route::get('data-cty', function (\Illuminate\Http\Request $r) {
    $cty_data = $r->data;
//    dd($data);
    $cty_data = (array) json_decode($cty_data);

    $cty_exist = App\CRMDV\Models\CompanyProfile::where(function ($query) use ($cty_data) {
        if (isset($cty_data['mst']) && $cty_data['mst'] != null && $cty_data['mst'] != '') {
            $query->orWhere('mst', @$cty_data['mst']);    //  mst
        }
        $query->orWhere('crawl_link', @$cty_data['crawl_link']);    //  mst
    })->first();

    if (is_object($cty_exist)) {
        echo 'đã tồn tại - tiến hành update';
    } else {
        echo 'chưa có - tiến hành tạo mới';
        $cty_exist = new App\CRMDV\Models\CompanyProfile();
    }

    //  ngành nghề
    if (isset($cty_data['nganh_nghe']) && $cty_data['nganh_nghe'] != '') {
        $career = App\CRMDV\Models\CompanyCategory::where('name', trim($cty_data['nganh_nghe']))->first();
        if (!is_object($career)) {
            $career = new App\CRMDV\Models\CompanyCategory();
            $career->name = trim($cty_data['nganh_nghe']);
            $career->save();
        }
        $cty_data['career_id'] = $career->id;
    }


    foreach ($cty_data as $k => $v) {
        if($v != '' && $v != null) {
            $cty_exist->{$k} = $v;
        }
    }
    $cty_exist->save();
    if ($cty_exist) {
        print "        + đã tạo cty " . @$cty_data['name'] . "\n";
    }
});
Route::get('kt-cty', function (\Illuminate\Http\Request $r) {
    return App\CRMDV\Models\CompanyProfile::where('crawl_link', $r->crawl_link)->count();
});
Route::get('page-cty', function (\Illuminate\Http\Request $r) {
    $companyprofile = App\CRMDV\Models\CompanyProfile::orderBy('page_id', 'desc');
    if ($r->has('province_id')) {
        $companyprofile = $companyprofile->where('province_id', $r->province_id);
    }
    return $companyprofile->first()->page_id;
});


Route::group(['prefix' => 'admin', 'middleware' => ['guest:admin', 'get_permissions']], function () {
    Route::group(['prefix' => 'bill'], function () {
        Route::get('gia-han', '\App\Custom\Controllers\Admin\BillController@giaHan');
        Route::get('ko-gia-han', '\App\Custom\Controllers\Admin\BillController@koGiaHan');
        Route::get('update-tk-hd', '\App\Custom\Controllers\Admin\BillController@updateTkHd');
    });

    Route::group(['prefix' => 'landingpage'], function () {
        Route::get('', '\App\Custom\Controllers\Admin\LandingpageController@getIndex')->name('landingpage')->middleware('permission:landingpage_view');
        Route::get('publish', '\App\Custom\Controllers\Admin\LandingpageController@getPublish')->name('landingpage.publish')->middleware('permission:landingpage_publish');
        Route::match(array('GET', 'POST'), 'add', '\App\Custom\Controllers\Admin\LandingpageController@add')->middleware('permission:landingpage_add');
        Route::get('delete/{id}', '\App\Custom\Controllers\Admin\LandingpageController@delete')->middleware('permission:landingpage_delete');
        Route::post('multi-delete', '\App\Custom\Controllers\Admin\LandingpageController@multiDelete')->middleware('permission:landingpage_delete');
        Route::get('search-for-select2', '\App\Custom\Controllers\Admin\LandingpageController@searchForSelect2')->name('landingpage.search_for_select2')->middleware('permission:landingpage_view');
        Route::get('{id}/duplicate', '\App\Custom\Controllers\Admin\LandingpageController@duplicate')->middleware('permission:landingpage_add');

        Route::get('{id}/ban-giao', '\App\Custom\Controllers\Admin\LandingpageController@banGiao');

        Route::get('update-to-bill', '\App\Custom\Controllers\Admin\LandingpageController@updateToBill')->middleware('permission:bill_add');

        Route::get('get-gg-form-fields', '\App\Custom\Controllers\Admin\LandingpageController@getGGFormFields');

        Route::get('update-link-ldp', function () {
            $landingpages = \Modules\LandingPage\Models\Landingpage::where('ladi_link', 'like', '%ladi.demopage.me%')->get();
            foreach ($landingpages as $ldp) {
                $ldp->ladi_link = str_replace('http://ladi.demopage.me/', 'http://preview.pagedemo.me/', $ldp->ladi_link);
//                dd($ldp->ladi_link);
                $ldp->save();
            }
            die('xong!');
        });

        Route::get('edit/{id}', '\App\Custom\Controllers\Admin\LandingpageController@update')->middleware('permission:landingpage_view');
        Route::post('edit/{id}', '\App\Custom\Controllers\Admin\LandingpageController@update')->middleware('permission:landingpage_edit');
    });

    //  Admin
    Route::group(['prefix' => 'admin'], function () {
        Route::get('ajax-get-info', '\App\CRMDV\Controllers\Admin\AdminController@ajaxGetInfo');
    });

    //  quản lý công ty
    Route::group(['prefix' => 'company'], function () {
        Route::get('', '\App\Custom\Controllers\Admin\CompanyController@getIndex')->name('company');
        Route::match(array('GET', 'POST'), 'add', '\App\Custom\Controllers\Admin\CompanyController@add')->middleware('permission:lead_view');
        Route::get('edit/{id}', '\App\Custom\Controllers\Admin\CompanyController@update');
        Route::post('edit/{id}', '\App\Custom\Controllers\Admin\CompanyController@update')->middleware('permission:lead_view');
        Route::get('delete/{id}', '\App\Custom\Controllers\Admin\CompanyController@delete')/*->middleware('permission:super_admin')*/;
        Route::post('multi-delete', '\App\Custom\Controllers\Admin\CompanyController@multiDelete')/*->middleware('permission:super_admin')*/;
        Route::get('publish', '\App\Custom\Controllers\Admin\CompanyController@getPublish')->name('company.publish')->middleware('permission:lead_view');
    });

    //  ngành nghề
    Route::group(['prefix' => 'company_category'], function () {
        Route::get('', '\App\Custom\Controllers\Admin\CompanyCategoryController@getIndex')->name('company_category')->middleware('permission:lead_view');
        Route::match(array('GET', 'POST'), 'add', '\App\Custom\Controllers\Admin\CompanyCategoryController@add')->middleware('permission:lead_view');
        Route::get('edit/{id}', '\App\Custom\Controllers\Admin\CompanyCategoryController@update')->middleware('permission:lead_view');
        Route::post('edit/{id}', '\App\Custom\Controllers\Admin\CompanyCategoryController@update')->middleware('permission:lead_view');
        Route::get('delete/{id}', '\App\Custom\Controllers\Admin\CompanyCategoryController@delete')->middleware('permission:super_admin');
        Route::post('multi-delete', '\App\Custom\Controllers\Admin\CompanyCategoryController@multiDelete')->middleware('permission:super_admin');
        Route::get('search-for-select2', '\App\Custom\Controllers\Admin\CompanyCategoryController@searchForSelect2')->name('company_category.search_for_select2')->middleware('permission:lead_view');
    });
});

Route::group(['prefix' => 'admin'], function () {
    Route::group(['prefix' => 'landingpage'], function () {
        Route::get('down-load-file/{bill_id}/{ldp_id}', '\App\Custom\Controllers\Admin\LandingpageController@downLoadFile');
    });
});
