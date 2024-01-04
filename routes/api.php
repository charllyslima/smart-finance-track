<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FinancialTransactionsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rotas para registro, login e logout
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Grupo de rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/financial-transactions', [FinancialTransactionsController::class, 'index']);
    Route::post('/financial-transactions', [FinancialTransactionsController::class,'store']);
    Route::put('/financial-transactions/{id}', [FinancialTransactionsController::class,'update'])->where('id', '[0-9]+');
    Route::delete('/financial-transactions/{id}', [FinancialTransactionsController::class,'destroy'])->where('id', '[0-9]+');
    Route::get('/financial-transactions/{id}', [FinancialTransactionsController::class,'show'])->where('id', '[0-9]+');
    Route::put('/update/profile', [UserController::class, 'update']);
    Route::delete('/delete/profile', [UserController::class, 'destroy']);
});


