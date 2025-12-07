<?php

use App\Http\Controllers\Docs\SwaggerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs', [SwaggerController::class, 'index'])->name('swagger.ui');
Route::get('/docs/openapi.yaml', [SwaggerController::class, 'spec'])->name('swagger.spec');

Route::fallback(function () {
    return response()->json([
        'message' => 'Route not exist',
    ], 404);
});
