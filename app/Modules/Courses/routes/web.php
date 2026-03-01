<?php
Route::get('dao-tao-noi-bo/bai-can-thi', '\App\Modules\Courses\Controllers\Frontend\CoursesController@baiCanThi')->name('dao-tao-noi-bo.bai-can-thi')->middleware(['guest:admin', 'get_permissions']);
Route::get('dao-tao-noi-bo/lo-trinh-hoc', '\App\Modules\Courses\Controllers\Frontend\CoursesController@loTrinhHoc')->name('dao-tao-noi-bo.lo-trinh-hoc')->middleware(['guest:admin', 'get_permissions']);

Route::get('dao-tao-noi-bo/{id?}', '\App\Modules\Courses\Controllers\Frontend\CoursesController@list')->name('dao-tao-noi-bo')->middleware(['guest:admin', 'get_permissions']);;
Route::get('dao-tao-noi-bo/detail/{id}', '\App\Modules\Courses\Controllers\Frontend\CoursesController@detail')->name('dao-tao-noi-bo.detail')->middleware(['guest:admin', 'get_permissions']);
Route::get('dao-tao-noi-bo/detail/{id}/courses_lesson/{lesson_id}', '\App\Modules\Courses\Controllers\Frontend\CoursesController@lessonVideo')->name('dao-tao-noi-bo.detailLesson')->middleware(['guest:admin', 'get_permissions']);
//admin đào tạo nội bộ
Route::group(['prefix' => 'course'], function () {
    Route::get('view', '\App\Modules\Courses\Controllers\Admin\CourseController@getView');
});
Route::group(['prefix' => 'course'], function () {
    Route::get('view/{category_id}', '\App\Modules\Courses\Controllers\Admin\CourseController@view')->middleware('permission:course_view');
    Route::get('publish', '\App\Modules\Courses\Controllers\Admin\CourseController@getPublish')->name('course.publish')->middleware('permission:course');
    Route::get('view/{category_id}/publish', '\App\Modules\Courses\Controllers\Admin\CourseController@getPublish')->name('course.publish')->middleware('permission:course');
    Route::match(array('GET', 'POST'), 'add', '\App\Modules\Courses\Controllers\Admin\CourseController@add')->middleware('permission:course');
    Route::get('delete/{id}', '\App\Modules\Courses\Controllers\Admin\CourseController@delete')->middleware('permission:course');
    Route::post('multi-delete', '\App\Modules\Courses\Controllers\Admin\CourseController@multiDelete')->middleware('permission:course');
    Route::get('search-for-select2', '\App\Modules\Courses\Controllers\Admin\CourseController@searchForSelect2')->name('course.search_for_select2')->middleware('permission:course_view');
    Route::get('edit/{id}', '\App\Modules\Courses\Controllers\Admin\CourseController@update');
    Route::post('edit/{id}', '\App\Modules\Courses\Controllers\Admin\CourseController@update')->middleware('permission:course');
    Route::get('{id}/duplicate', '\App\Modules\Courses\Controllers\Admin\CourseController@duplicate')->middleware('permission:course');
});
Route::group(['prefix' => 'category_course'], function () {
    Route::get('', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@getIndex')->middleware('permission:course');
    Route::get('publish', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@getPublish')->name('category_course.publish')->middleware('permission:course');
    Route::match(array('GET', 'POST'), 'add', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@add')->middleware('permission:course');
    Route::get('delete/{id}', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@delete')->middleware('permission:course');
    Route::post('multi-delete', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@multiDelete')->middleware('permission:course');
    Route::get('search-for-select2', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@searchForSelect2')->name('category_course.search_for_select2')->middleware('permission:course');
    Route::get('edit/{id}', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@update')->middleware('permission:course');
    Route::post('edit/{id}', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@update')->middleware('permission:course');
    Route::get('{id}/duplicate', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@duplicate')->middleware('permission:course');
});
//Route::group(['prefix' => 'course'], function () {
//    Route::get('view', '\App\Modules\Courses\Controllers\Admin\CourseController@getView')->middleware('permission:course_view');
//});
//Route::group(['prefix' => 'course'], function () {
//    Route::get('view/{category_id}', '\App\Modules\Courses\Controllers\Admin\CourseController@view')->middleware('permission:course_view');
//    Route::get('publish', '\App\Modules\Courses\Controllers\Admin\CourseController@getPublish')->name('course.publish')->middleware('permission:course');
//    Route::get('view/{category_id}/publish', '\App\Modules\Courses\Controllers\Admin\CourseController@getPublish')->name('course.publish')->middleware('permission:course');
//    Route::match(array('GET', 'POST'), 'add', '\App\Modules\Courses\Controllers\Admin\CourseController@add')->middleware('permission:course');
//    Route::get('delete/{id}', '\App\Modules\Courses\Controllers\Admin\CourseController@delete')->middleware('permission:course');
//    Route::post('multi-delete', '\App\Modules\Courses\Controllers\Admin\CourseController@multiDelete')->middleware('permission:course');
//    Route::get('search-for-select2', '\App\Modules\Courses\Controllers\Admin\CourseController@searchForSelect2')->name('course.search_for_select2')->middleware('permission:course_view');
//    Route::get('edit/{id}', '\App\Modules\Courses\Controllers\Admin\CourseController@update');
//    Route::post('edit/{id}', '\App\Modules\Courses\Controllers\Admin\CourseController@update')->middleware('permission:course');
//    Route::get('{id}/duplicate', '\App\Modules\Courses\Controllers\Admin\CourseController@duplicate')->middleware('permission:course');
//});
//Route::group(['prefix' => 'category_course'], function () {
//    Route::get('', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@getIndex')->middleware('permission:course');
//    Route::get('publish', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@getPublish')->name('category_course.publish')->middleware('permission:course');
//    Route::match(array('GET', 'POST'), 'add', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@add')->middleware('permission:course');
//    Route::get('delete/{id}', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@delete')->middleware('permission:course');
//    Route::post('multi-delete', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@multiDelete')->middleware('permission:course');
//    Route::get('search-for-select2', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@searchForSelect2')->name('category_course.search_for_select2')->middleware('permission:course');
//    Route::get('edit/{id}', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@update')->middleware('permission:course');
//    Route::post('edit/{id}', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@update')->middleware('permission:course');
//    Route::get('{id}/duplicate', '\App\Modules\Courses\Controllers\Admin\CategoryCourseController@duplicate')->middleware('permission:course');
//});
