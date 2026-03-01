<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AI\Controllers\LeadAIController;

Route::prefix('api')
    ->middleware('api')
    ->group(function () {
        Route::post('/ai/lead/edit',  '\App\Modules/AI\Controllers\LeadAIController@ask');
    });
