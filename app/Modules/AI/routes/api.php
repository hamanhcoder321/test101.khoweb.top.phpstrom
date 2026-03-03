<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AI\Controllers\LeadAIController;

Route::prefix('api')
    ->middleware('api')
    ->group(function () {
        Route::post('/ai/lead/edit',  '\App\Modules/AI\Controllers\LeadAIController@ask');
        Route::post('/ai/phone',      '\App\Modules\AI\Controllers\LeadAIController@askByPhone');
        Route::post('/ai/ask',        '\App\Modules\AI\Controllers\LeadAIController@askGeneral');
    });
