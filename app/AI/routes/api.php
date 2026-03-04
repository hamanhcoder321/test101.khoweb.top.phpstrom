<?php

use Illuminate\Support\Facades\Route;
use App\AI\Controllers\LeadAIController;

Route::prefix('api')
    ->middleware('api')
    ->group(function () {
        Route::post('/ai/lead/edit', '\App\AI\Controllers\LeadAIController@ask');
        Route::post('/ai/phone',     '\App\AI\Controllers\LeadAIController@askByPhone');
        Route::post('/ai/ask',       '\App\AI\Controllers\LeadAIController@askGeneral');
    });
