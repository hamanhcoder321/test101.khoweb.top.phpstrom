<?php

use Illuminate\Support\Facades\Route;
use App\Modules\HBDashboard\Controllers\Api\DashboardController;
use App\CRMDV\Controllers\Api\RoomController;
use App\CRMDV\Controllers\Api\QuizController;


Route::post('admin/login', 'App\Http\Controllers\Api\Admin\AdminController@login');
Route::get('admin/service', 'App\CRMDV\Controllers\Api\DHBillController@getService');
Route::get('/banks', 'App\CRMDV\Controllers\Api\TagController@getBanks');
Route::get('/goi-dich-vu', 'App\CRMDV\Controllers\Api\BillControllers@goiDichVu');
Route::get('/web_lock', 'App\CRMDV\Controllers\Api\BillController@web_lock');
Route::get('/a',function (){
    return response()->json(['message'=>'https://docs.google.com/document/d/1ppVYJkxo51zq0Hk1islLUo-hc4kd5LTUCMUnYferXm8/edit?tab=t.0   https://docs.google.com/document/d/1i6Hl0IOPUzSPg-LIWIuAXERtp25JdPYxysbGrwtJCjI/edit?tab=t.0#heading=h.s91ykt6gmgu0     https://docs.google.com/document/d/12YkC-Az9xvvYIL1QNGN-1XUBh5-yt68wdThmNgcUE5I/edit?usp=drivesdk ']);
});
Route::group(['prefix' => 'admin', 'middleware' => ['jwt.signature']], function () {
    Route::get('/services', 'App\CRMDV\Controllers\Api\LeadController@serviceType');

    // Employee routes
         Route::get('/xep-hang-nhan-vien', 'App\CRMDV\Controllers\Api\HrAdminController@top10NhanVienQuanTam');
         Route::get('/ds-nhan-vien', 'App\CRMDV\Controllers\Api\HrAdminController@getAll');
         Route::post('/store', 'App\CRMDV\Controllers\Api\HrAdminController@store');
         Route::put('/update/{id}', 'App\CRMDV\Controllers\Api\HrAdminController@update');
         Route::get('/sinh-nhat-nhan-su', 'App\CRMDV\Controllers\Api\HrAdminController@sinhNhatNhanSu');

//         Route::put('/{id}', 'App\CRMDV\Controllers\Api\HrAdminController@update');
//         Route::delete('/{id}', 'App\CRMDV\Controllers\Api\HrAdminController@destroy');
         //Leader routespro
    Route::group(['prefix' => 'lead' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\LeadController@getAll');
        Route::get('/tha-noi', 'App\CRMDV\Controllers\Api\LeadController@getAll');
//        Route::get('/{id}', 'App\CRMDV\Controllers\Api\LeadController@show');
        Route::get('/detail/{id}', 'App\CRMDV\Controllers\Api\LeadController@show');
        Route::get('/{id}', 'App\CRMDV\Controllers\Api\LeadController@add');
        Route::match(['post', 'put'], '/add', 'App\CRMDV\Controllers\Api\LeadController@add');
//        Route::put('/update/{id}', 'App\CRMDV\Controllers\Api\LeadController@add');
        Route::put('/{id}', 'App\CRMDV\Controllers\Api\LeadController@updateLead');
        Route::put('/list/{id}', 'App\CRMDV\Controllers\Api\LeadController@updateList');
        Route::get('/list/by-sale', 'App\CRMDV\Controllers\Api\LeadController@getBySaleName');
      
//        Route::get('/search','App\CRMDV\Controllers\Api\LeadController@search');
    });
    Route::group(['prefix' => 'user' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\HrAdminController@getAllUser');
//      Route::post('/store', 'App\CRMDV\Controllers\Api\HrAdminController@store');
        Route::match(['get','post', 'put'], '/{id?}', 'App\CRMDV\Controllers\Api\HrAdminController@detailOrUpdate');

    });
    Route::group(['prefix' => 'quiz' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\QuizQuestionController@getAll');
        Route::get('/{quiz_id}/start', 'App\CRMDV\Controllers\Api\QuizController@start');
        Route::post('/submit', 'App\CRMDV\Controllers\Api\QuizController@submit');
        Route::get('/result/{history_id}','App\CRMDV\Controllers\Api\QuizController@result');
        Route::get('/history', 'App\CRMDV\Controllers\Api\QuizController@history');
    });
    Route::group(['prefix' => 'roles' ], function () {
        Route::get('/', 'App\CRMDV\Controllers\Api\RoleController@index');
        Route::get('/{id}', 'App\CRMDV\Controllers\Api\RoleController@show');
        Route::post('/add', 'App\CRMDV\Controllers\Api\RoleController@store');
        Route::put('/{id}', 'App\CRMDV\Controllers\Api\RoleController@update');
        Route::delete('/{id}', 'App\CRMDV\Controllers\Api\RoleController@destroy');

    });
    Route::group(['prefix' => 'tasks' ], function () {
        Route::get('/', 'App\CRMDV\Controllers\Api\TaskController@getAll');
        Route::post('/add', 'App\CRMDV\Controllers\Api\TaskController@createTask');
        Route::get('/{id}', 'App\CRMDV\Controllers\Api\TaskController@getTask');
        Route::put('/{id}', 'App\CRMDV\Controllers\Api\TaskController@updateTask');
        Route::delete('/{id}', 'App\CRMDV\Controllers\Api\TaskController@deleteTask');
        Route::post('/tasks/{id}/update-status', 'App\CRMDV\Controllers\Api\TaskController@updateStatus');
        Route::post('/tasks/{id}/update-progress', 'App\CRMDV\Controllers\Api\TaskController@updateProgress');

    });
    Route::group(['prefix' => 'cham-cong' ], function () {
        Route::post('checkin',   'App\CRMDV\Controllers\Api\AttendanceController@checkIn');
        Route::post('checkout',  'App\CRMDV\Controllers\Api\AttendanceController@checkOut');
        Route::get('my-history', 'App\CRMDV\Controllers\Api\AttendanceController@myHistory');
        Route::get('my-history/{id}', 'App\CRMDV\Controllers\Api\AttendanceController@myHistoryId');
        Route::post('my-history/{id}', 'App\CRMDV\Controllers\Api\AttendanceController@storeReason');
        Route::get('my-summary', 'App\CRMDV\Controllers\Api\AttendanceController@mySummary');
        Route::get('all/{id?}',  'App\CRMDV\Controllers\Api\AttendanceController@adminAll');
        Route::get('detail/{id}', 'App\CRMDV\Controllers\Api\AttendanceController@detail');
        Route::get('late-reasons', 'App\CRMDV\Controllers\Api\AttendanceController@lateReasons');
        Route::post('admin/update-location/{id}','App\CRMDV\Controllers\Api\AttendanceController@updateLocation');
        Route::post('admin/approve-reason/{id}', 'App\CRMDV\Controllers\Api\AttendanceController@approveReason');

    });
    Route::get('/profile','App\Http\Controllers\Api\Admin\AdminController@profile');
    Route::match(['post', 'put','get'], '/profile/edit', 'App\Http\Controllers\Api\Admin\AdminController@updateProfile');
    // routes/api.php
    Route::group(['prefix' => 'rooms' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\RoomController@list');
        Route::post('/add', 'App\CRMDV\Controllers\Api\RoomController@add');
        Route::get('/{id}', 'App\CRMDV\Controllers\Api\RoomController@show');
        Route::put('/{id}', 'App\CRMDV\Controllers\Api\RoomController@update');
        Route::delete('/{id}', 'App\CRMDV\Controllers\Api\RoomController@destroy');
    });


    Route::group(['prefix' => 'hradmin' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\HrAdminController@getAll');
        Route::get('/list-np', 'App\CRMDV\Controllers\Api\HrAdminController@getAll1');
        Route::match(['get','post'], '/{id?}', 'App\CRMDV\Controllers\Api\HrAdminController@detailOrUpdate');
    });

    Route::group(['prefix' => 'course' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\CourseController@getAll');
        Route::get('/{id}', 'App\CRMDV\Controllers\Api\CourseController@show');

    });
    Route::group(['prefix' => 'receipt-payment' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\ReceiptPaymentController@list');
        Route::match(['get', 'post','put'], '{id}', 'App\CRMDV\Controllers\Api\ReceiptPaymentController@detailOrUpdate');
        Route::delete('/{id}', 'App\CRMDV\Controllers\Api\ReceiptPaymentController@destroy');
        Route::post('/update/{id}', 'App\CRMDV\Controllers\Api\ReceiptPaymentController@updateStatus');
    });
    //Bill routes
    Route::group(['prefix' => 'dh-bill' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\DHBillController@getAll');
        Route::get('/{id}', 'App\CRMDV\Controllers\Api\DHBillController@getDetail');
        Route::post('/store', 'App\CRMDV\Controllers\Api\DHBillController@store');
        Route::put('/update/{id}', 'App\CRMDV\Controllers\Api\DHBillController@update');

    });
    Route::group(['prefix' => 'bill' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\BillController@getAll');
        Route::get('/list1', 'App\CRMDV\Controllers\Api\BillController@getAll1');
//        Route::get('/{id}', 'App\CRMDV\Controllers\Api\BillController@show');
//        Route::put('/update/{id}', 'App\CRMDV\Controllers\Api\BillController@update');
        Route::post('/add', 'App\CRMDV\Controllers\Api\BillController@add');
//        Route::match(['get', 'post','put'], '{id}', 'App\CRMDV\Controllers\Api\BillController@showOrUpdate');
        Route::post('/', 'App\CRMDV\Controllers\Api\BillController@store');
        Route::get('{id}', 'App\CRMDV\Controllers\Api\BillController@show');
        Route::post('{id}', 'App\CRMDV\Controllers\Api\BillController@update');
        Route::post('{id}/receipt', 'App\CRMDV\Controllers\Api\BillController@createReceipt');
        Route::post('{id}/receipt/{receipt_id}', 'App\CRMDV\Controllers\Api\BillController@updateReceipt');


    });
    //dashboard routes
    Route::group(['prefix' => 'dashboard' ], function () {
        Route::get('/index', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@apiThongKeTongHop');
        Route::get('/hop-dong-moi', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@hopDongMoi');
        Route::get('/hop-dong-ky-moi', 'App\CRMDV\Controllers\Api\BillController@hopDongMoi4Thang');
        Route::get('/tong-quan-chi-phi', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@tongQuanChiPhi');
//        Route::post('/upload','App\CRMDV\Controllers\Api\DashboardController@ajax_up_file');
//        Route::get('/location/{table}','App\CRMDV\Controllers\Api\DashboardController@getDataLocation');
    });
    Route::group(['prefix' => 'report' ], function () {
        Route::get('/top-dich-vu', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@topDichVu');
        Route::get('/top3-sales', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@top3Sale');
        Route::get('/top3-maketing', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@top3maketing');
        Route::get('/top3-ky-thuat', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@top3kythuat');
        Route::get('/sp-trong-ki', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@sanPhamBanTrongKy');
    Route::get('/bao-cao-san-pham', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@baoCaoSanPham');
    Route::get('/bao-cao-kh', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@baoCaoKhachHang');
    Route::get('/bao-cao-tinh-trang-kh', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@getLeadStatus');
    Route::get('/bao-cao-tang-truong', 'App\Modules\HBDashboard\Controllers\Api\DashboardController@baoCao4ThangGanNhat');
    });
    Route::group(['prefix' => 'phongban' ], function () {
        Route::get('/list', 'App\CRMDV\Controllers\Api\TagController@getRoom');
        Route::get('/nhansu', 'App\CRMDV\Controllers\Api\HrAdminController@getNhansu');
    });
});
