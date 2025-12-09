<?php

use App\Http\Controllers\V2\SecretController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('throttle:15,1')->group(function () {
    Route::prefix('v1')->group(function () {

        Route::prefix('secretcontroller')->group(function () {
            Route::controller(\App\Http\Controllers\V1\SecretController::class)->group(function () {
                Route::post('add', 'add');
                Route::get('secret', 'view'); // remove this in the future.
                Route::post('secret', 'view');
                Route::delete('delete', 'delete');
            });
        });

    });

    Route::prefix('v2')->group(function () {
        Route::prefix('secretcontroller')->group(function () {

            Route::controller(SecretController::class)->group(function () {
                Route::post('add', 'add');
            });
        });
    });
});


