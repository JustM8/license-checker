<?php

use App\Http\Controllers\LicenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\License;


Route::post('/check', [LicenseController::class, 'check']);
//    ->middleware('log.site');
