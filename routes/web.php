<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('debug-routes', function () {
    return response()->json([
        'base_path' => base_path(),
        'api_file_exists' => file_exists(base_path('routes/api.php')),
        'api_realpath' => realpath(base_path('routes/api.php')),
        'routes_loaded_count' => count(app('router')->getRoutes()),
    ]);
});

Route::get('test', [AuthController::class, 'test']);