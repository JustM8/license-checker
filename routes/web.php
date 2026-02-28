<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
    Route::get('/licenses/create', [LicenseController::class, 'create'])->name('licenses.create');
    Route::post('/licenses', [LicenseController::class, 'store'])->name('licenses.store');

    Route::put('/licenses/{license}', [LicenseController::class, 'update']);

    Route::get('/licenses/{license}/generate-js', [\App\Http\Controllers\LicenseController::class, 'generateJs'])
        ->name('licenses.generate-js');
    Route::get('/site-requests', [\App\Http\Controllers\SiteRequestController::class, 'index']);
    Route::post('/licenses/{license}/reactivate', [LicenseController::class, 'reactivate'])
        ->name('licenses.reactivate');
});
