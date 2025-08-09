<?php

use App\Http\Controllers\PersonController;
use App\Http\Controllers\SyncDataController;
use App\Http\Controllers\SyncLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('home');
});


Route::get('/api/get-sync-link', [SyncLinkController::class, 'getSyncLink']);
Route::post('/api/set-sync-link', [SyncLinkController::class, 'setSyncLink']);

// CRUD
Route::post('/api/create', [PersonController::class, 'store']);
Route::get('/api/read/{person}', [PersonController::class, 'show']);
Route::put('/api/update/{person}', [PersonController::class, 'update']);
Route::delete('/api/delete/{person}', [PersonController::class, 'destroy']);

// List
Route::get('/api/get-data', [PersonController::class, 'getData']);
Route::delete('/api/clear-database', [PersonController::class, 'clearDatabase']);
Route::post('/api/generate-data-in-database', [PersonController::class, 'generateDataInDatabase']);

Route::post('/api/clear-google-spreadsheet', [SyncDataController::class, 'clearGoogleSpreadsheet']);

// Fetch
Route::get('/api/fetch/{count?}', [SyncDataController::class, 'fetch']);
Route::get('/fetch/{count?}', [SyncDataController::class, 'fetchFormat']);
