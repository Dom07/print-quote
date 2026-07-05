<?php

use App\Http\Controllers\EstimateSandboxController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/estimate-sandbox', [EstimateSandboxController::class, 'create']);
Route::post('/estimate-sandbox', [EstimateSandboxController::class, 'store']);
