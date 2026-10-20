<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:registration');
});
Route::get('/forgot-password', [PasswordController::class,'requestForm'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', [PasswordController::class,'email'])->middleware(['guest','throttle:password'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordController::class,'resetForm'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', [PasswordController::class,'reset'])->middleware('guest')->name('password.update');
Route::get('/email/verify', [VerificationController::class, 'notice'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['auth','signed','throttle:6,1'])->name('verification.verify');
Route::post('/email/verification-notification', [VerificationController::class, 'send'])->middleware(['auth','throttle:6,1'])->name('verification.send');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware(['auth','active','verified'])->group(function () { Route::view('/dashboard','dashboard')->name('dashboard'); });
Route::get('/prospecting', fn () => response()->file(resource_path('views/prospecting.html'))->withCookie(cookie('XSRF-TOKEN', csrf_token(), 120, '/', null, app()->environment('production'), false, false, 'lax')))->middleware(['auth','active','verified'])->name('prospecting');
Route::middleware(['auth','active','verified','admin'])->prefix('admin')->name('admin.')->group(function () { Route::get('/',[AdminController::class,'dashboard'])->name('dashboard'); Route::get('/users',[AdminController::class,'users'])->name('users'); Route::get('/users/create',[AdminController::class,'create'])->name('users.create'); Route::post('/users',[AdminController::class,'store'])->name('users.store'); Route::get('/users/{user}/edit',[AdminController::class,'edit'])->name('users.edit'); Route::put('/users/{user}',[AdminController::class,'update'])->name('users.update'); Route::post('/users/{user}/toggle',[AdminController::class,'toggle'])->name('users.toggle'); Route::post('/users/{user}/verification',[AdminController::class,'resendVerification'])->name('users.verification'); Route::post('/users/{user}/password-reset',[AdminController::class,'resetPassword'])->name('users.password-reset'); });
