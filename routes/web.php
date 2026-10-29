<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\LeadPageController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ModulePlaceholderController;
use App\Http\Controllers\ProspectingController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'))->name('home');
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
Route::middleware(['auth','active','verified'])->group(function () { Route::post('/minha-conta/foto', [AccountController::class, 'updatePhoto'])->name('account.photo.update'); Route::delete('/minha-conta/foto', [AccountController::class, 'removePhoto'])->name('account.photo.remove'); Route::get('/minha-conta/foto', [AccountController::class, 'photo'])->name('account.photo'); });
Route::middleware(['auth','active','verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('module:dashboard')->name('dashboard');
    Route::middleware('module:leads')->group(function () {
        Route::get('/leads', LeadPageController::class)->name('leads');
        Route::get('/leads/resultado/{job}', [LeadPageController::class, 'results'])->name('leads.results');
        Route::post('/leads', [LeadPageController::class, 'store'])->name('leads.store');
        Route::put('/leads/{lead}', [LeadPageController::class, 'update'])->name('leads.update');
    });
    Route::get('/minha-conta',[AccountController::class,'edit'])->name('account.edit');
    Route::put('/minha-conta',[AccountController::class,'update'])->name('account.update');
    Route::put('/minha-conta/senha',[AccountController::class,'updatePassword'])->name('account.password');
    Route::middleware('module:pipeline')->group(function () {
        Route::get('/pipeline',[PipelineController::class,'index'])->name('pipeline');
        Route::post('/pipeline/stages',[PipelineController::class,'storeStage'])->name('pipeline.stages.store');
        Route::post('/pipeline/leads',[PipelineController::class,'sendMany'])->name('pipeline.send-many');
        Route::post('/pipeline/leads/{lead}',[PipelineController::class,'send'])->name('pipeline.send');
        Route::patch('/pipeline/opportunities/{opportunity}',[PipelineController::class,'move'])->name('pipeline.move');
    });
});
Route::middleware(['auth','active','verified'])->group(function () { foreach (['chats','numeros-conectados','disparar','email-marketing','automacoes','agenda','briefings'] as $module) { Route::get('/'.$module, ModulePlaceholderController::class)->defaults('module', $module)->name('module.'.$module); } });
Route::get('/prospecting', ProspectingController::class)->middleware(['auth','active','verified','module:prospecting'])->name('prospecting');
Route::post('/leads/lists',[LeadPageController::class,'storeList'])->middleware(['auth','active','verified','module:leads'])->name('leads.lists.store');
Route::post('/leads/labels',[LeadPageController::class,'storeLabel'])->middleware(['auth','active','verified','module:leads'])->name('leads.labels.store');
Route::post('/leads/apply-label',[LeadPageController::class,'applyLabel'])->middleware(['auth','active','verified','module:leads'])->name('leads.apply-label');
Route::post('/leads/assign-list',[LeadPageController::class,'assignList'])->middleware(['auth','active','verified','module:leads'])->name('leads.assign-list');
Route::middleware(['auth','active','verified','module:pipeline'])->group(function () { Route::patch('/pipeline/stages/{stage}',[PipelineController::class,'updateStage'])->name('pipeline.stages.update'); Route::post('/pipeline/stages/reorder',[PipelineController::class,'reorder'])->name('pipeline.stages.reorder'); Route::delete('/pipeline/stages/{stage}',[PipelineController::class,'destroyStage'])->name('pipeline.stages.destroy'); });
Route::get('/painel', DashboardController::class)->middleware(['auth','active','verified','module:dashboard'])->name('painel');
Route::middleware(['auth','active','verified','admin'])->prefix('admin')->name('admin.')->group(function () { Route::get('/',[AdminController::class,'dashboard'])->name('dashboard'); Route::get('/users',[AdminController::class,'users'])->name('users'); Route::get('/users/create',[AdminController::class,'create'])->name('users.create'); Route::post('/users',[AdminController::class,'store'])->name('users.store'); Route::get('/users/{user}/edit',[AdminController::class,'edit'])->name('users.edit'); Route::put('/users/{user}',[AdminController::class,'update'])->name('users.update'); Route::get('/users/{user}/photo',[AdminController::class,'photo'])->name('users.photo'); Route::post('/users/{user}/toggle',[AdminController::class,'toggle'])->name('users.toggle'); Route::post('/users/{user}/verification',[AdminController::class,'resendVerification'])->name('users.verification'); Route::post('/users/{user}/password-reset',[AdminController::class,'resetPassword'])->name('users.password-reset'); });
