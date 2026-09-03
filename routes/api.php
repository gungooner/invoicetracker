<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('clients', ClientController::class);
    Route::apiResource('invoices', InvoiceController::class);

    Route::post('/invoices/{invoice}/items', [InvoiceItemController::class, 'store']);
    Route::put('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'update']);
    Route::delete('/invoice-items/{invoiceItem}', [InvoiceItemController::class, 'destroy']);
});