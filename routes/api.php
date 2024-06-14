<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/transactions/summary', [PaymentController::class, 'getTransactionDataSummary']);
    Route::post('/transactions', [PaymentController::class, 'createTransaction']);
    Route::get('/users/{userId}/transactions', [PaymentController::class, 'getUserTransactions']);
});