<?php

Route::group(['prefix' => 'admin', 'middleware' => ['guest:admin', 'get_permissions']], function () {
    //  Thống kê
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('', '\App\Modules\HBDashboard\Controllers\Admin\DashboardController@dashboardSoftware');
        Route::get('ds-ky-thuat', '\App\Modules\HBDashboard\Controllers\Admin\DashboardController@dsKyThuat');
    });

});