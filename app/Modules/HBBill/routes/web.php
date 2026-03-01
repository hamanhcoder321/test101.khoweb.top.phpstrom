<?php

Route::group(['prefix' => 'admin', 'middleware' => ['guest:admin', 'get_permissions']], function () {

    Route::get('cancel-extension', '\App\Modules\HBBill\Controllers\Admin\BillController@cancelExtension')->name('dashboard.cancel_extension')->middleware('permission:bill_edit');
    Route::group(['prefix' => 'bill'], function () {
        Route::get('test', '\App\Modules\HBBill\Controllers\Admin\BillController@test');
        Route::get('ko-duy-tri', '\App\Modules\HBBill\Controllers\Admin\BillController@koDuyTri');
        Route::get('tooltip-info', '\App\Modules\HBBill\Controllers\Admin\BillController@tooltipInfo');
        Route::get('', '\App\Modules\HBBill\Controllers\Admin\BillController@getIndex')->name('bill')->middleware('permission:bill_view');
        Route::get('publish', '\App\Modules\HBBill\Controllers\Admin\BillController@getPublish')->name('bill.publish')->middleware('permission:bill_publish');
        Route::match(array('GET', 'POST'), 'add', '\App\Modules\HBBill\Controllers\Admin\BillController@add')->middleware('permission:bill_add');
        Route::get('delete/{id}', '\App\Modules\HBBill\Controllers\Admin\BillController@delete')->middleware('permission:bill_delete');
        Route::post('multi-delete', '\App\Modules\HBBill\Controllers\Admin\BillController@multiDelete')->middleware('permission:bill_delete');
        Route::get('search-for-select2', '\App\Modules\HBBill\Controllers\Admin\BillController@searchForSelect2')->name('bill.search_for_select2')->middleware('permission:bill_view');
        Route::get('edit/{id}', '\App\Modules\HBBill\Controllers\Admin\BillController@update')->middleware('permission:bill_view');
        Route::post('edit/{id}', '\App\Modules\HBBill\Controllers\Admin\BillController@update')->middleware('permission:bill_edit');
    });

    Route::get('del', '\App\Modules\HBBill\Controllers\Admin\BillController@del')->middleware('permission:bill_delete');
    
});