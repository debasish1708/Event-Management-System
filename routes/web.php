<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use Illuminate\Support\Facades\Auth;

Auth::routes();

// Main Page Route
Route::get('/', [RegisterController::class, 'login'])->name('auth.login');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// authentication
// Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::post('/register', [RegisterController::class, 'register'])->name('register.store');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.show');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::middleware('auth')->group(function () {
    // Route::resource('/users', UserController::class);
    Route::get('/dashboard', function () {
        return view('content.pages.pages-home');
    })->name('dashboard');
});