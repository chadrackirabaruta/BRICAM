<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController; // ✅ Make sure this matches the controller path

Route::prefix('locations')->group(function () {
    Route::get('/provinces', [LocationController::class, 'getProvinces']);
    Route::get('/districts/{province}', [LocationController::class, 'getDistricts']);
    Route::get('/sectors/{district}', [LocationController::class, 'getSectors']);
    Route::get('/cells/{sector}', [LocationController::class, 'getCells']);
    Route::get('/villages/{cell}', [LocationController::class, 'getVillages']);
});