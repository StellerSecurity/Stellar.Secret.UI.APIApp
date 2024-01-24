<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('v1')->group(function () {

    Route::prefix('secretcontroller')->group(function () {
        Route::controller(\App\Http\Controllers\V1\SecretController::class)->group(function () {
            Route::post('add', 'add');
            Route::get('secret', 'find');
            Route::delete('delete', 'delete');
        });
    });

});
