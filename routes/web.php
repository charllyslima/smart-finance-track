<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/documentation', function () {
    return view('swagger-ui');
});


Route::get('/swagger/{path}', function ($path) {
    $filePath = base_path("swagger/{$path}");
    if (!File::exists($filePath)) {
        abort(404, 'File not found');
    }
    return Response::file($filePath);
})->where('path', '.*');