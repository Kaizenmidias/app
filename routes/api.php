<?php
use App\Http\Controllers\ProspectingController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;
Route::middleware(['web','auth','active','verified','frontend.csrf'])->prefix('prospecting')->group(function () { Route::get('/jobs',[ProspectingController::class,'index']); Route::post('/jobs',[ProspectingController::class,'store']); Route::get('/jobs/{job}',[ProspectingController::class,'show']); });
Route::middleware(['web','auth','active','verified'])->group(function () { Route::apiResource('leads', LeadController::class)->only(['index','show','update','destroy']); });
Route::middleware(['web','auth','active','verified'])->group(function () { Route::post('leads/{lead}/notes',[NoteController::class,'store']); Route::put('notes/{note}',[NoteController::class,'update']); Route::delete('notes/{note}',[NoteController::class,'destroy']); });
Route::middleware(['web','auth','active','verified'])->group(function () { Route::get('exports',[ExportController::class,'index']); Route::get('prospecting/jobs/{job}/export',[ExportController::class,'download']); });
Route::middleware(['web','auth','active','verified','frontend.csrf'])->prefix('v1/jobs')->group(function () { Route::get('/csrf-token', fn (\Illuminate\Http\Request $request) => response()->json(['token' => csrf_token()])->header('Cache-Control', 'no-store, private')); Route::get('/',[ProspectingController::class,'legacyIndex']); Route::post('/',[ProspectingController::class,'legacyStore']); Route::get('/{job}',[ProspectingController::class,'legacyStatus']); Route::get('/{job}/download',[ProspectingController::class,'legacyDownload']); Route::delete('/{job}',[ProspectingController::class,'legacyDestroy']); });
