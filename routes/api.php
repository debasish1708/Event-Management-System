<?php

use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\TicketController;
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

        Route::get('/events', [EventController::class,'index']);
        Route::get('/events/{event}', [EventController::class,'show']);

        // Organizer-only event management
        Route::middleware('role:organizer,admin')->group(function(){
          Route::post('/events', [EventController::class,'store']);
          Route::put('/events/{id}', [EventController::class,'update']);
          Route::delete('/events/{id}', [EventController::class,'destroy']);

          Route::post('/events/{event_id}/tickets', [TicketController::class,'store']);
          Route::put('/tickets/{id}', [TicketController::class,'update']);
          Route::delete('/tickets/{id}', [TicketController::class,'destroy']);

        });

        // Bookings (customer)
        Route::middleware('role:customer,admin')->group(function(){
          Route::post('/tickets/{id}/bookings', [BookingController::class,'store'])->middleware('prevent.double.booking');
          Route::get('/bookings', [BookingController::class,'index']);
          Route::put('/bookings/{id}/cancel', [BookingController::class,'cancel']);
          Route::post('/bookings/{id}/payment', [PaymentController::class,'pay']);
          Route::get('/payments/{id}', [PaymentController::class,'show']);
        });
    });
});
