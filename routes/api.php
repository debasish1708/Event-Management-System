<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('api')->prefix('v1')->group(function () {
    Route::group(['prefix' => 'api'], function () {
        Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register'])->name('api.register');
        Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login'])->name('api.login');
        Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout'])->name('api.logout')->middleware('auth:sanctum');
        Route::get('/me',function(Request $request){
            return response()->json($request->user());
        })->middleware('auth:sanctum')->name('api.me');
    });

    Route::middleware('auth:sanctum')->group(function () {

        // Organizer-only event management
        Route::middleware('role:organizer,admin')->group(function(){
            
        });

        // Bookings (customer)
        Route::middleware('role:customer,admin')->group(function(){

        });
    });
});
