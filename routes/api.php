<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CashMovementController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProviderInvoiceController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TransferWineryController;
use App\Http\Controllers\WineryController;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::group(['middleware' => 'auth:sanctum'], function () {

        //CUSTOMERS
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

        //PROVIDERS
        Route::get('/providers', [ProviderController::class, 'index']);
        Route::post('/providers', [ProviderController::class, 'store']);
        Route::put('/providers/{id}', [ProviderController::class, 'update']);
        Route::get('/providers/{id}', [ProviderController::class, 'show']);
        Route::delete('/providers/{id}', [ProviderController::class, 'destroy']);

        /* INVENTARIO */

        //WINERIES
        Route::get('/wineries', [WineryController::class, 'index']);
        Route::post('/wineries', [WineryController::class, 'store']);
        Route::put('/wineries/{id}', [WineryController::class, 'update']);
        Route::post('wineries/{id}', [WineryController::class, 'default']);
        Route::get('/wineries/{id}', [WineryController::class, 'show']);
        Route::delete('/wineries/{id}', [WineryController::class, 'destroy']);

        //ITEMS
        Route::get('/items', [ItemController::class, 'index']);
        Route::get('/items/search', [ItemController::class, 'search']);
        Route::post('/items', [ItemController::class, 'store']);
        Route::put('/items/{id}', [ItemController::class, 'update']);
        Route::post('/items/{id}/imagen', [ItemController::class, 'uploadImage']);
        Route::get('/items/{id}', [ItemController::class, 'show']);
        Route::delete('/items/{id}', [ItemController::class, 'destroy']);

        //LOTS
        Route::get('/lots', [LotController::class, 'index']);
        Route::delete('/lots/{id}', [LotController::class, 'destroy']);

        //MOVEMENTS
        Route::get('movements', [MovementController::class, 'index']);
        Route::get('movements/{id}', [MovementController::class, 'show']);
        Route::post('movements', [MovementController::class, 'store']);

        //TRANSFERS
        Route::get('transfers', [TransferWineryController::class, 'index']);
        Route::get('transfers/{id}', [TransferWineryController::class, 'show']);
        Route::post('transfers', [TransferWineryController::class, 'store']);

        //CASHES
        Route::get('/cashes', [CashController::class, 'index']);
        Route::post('/cashes', [CashController::class, 'store']);
        Route::put('/cashes/{id}', [CashController::class, 'update']);
        Route::post('cashes/{id}/open', [CashController::class, 'open']);
        Route::post('cashes/{id}/close', [CashController::class, 'close']);
        Route::get('/cashes/{id}', [CashController::class, 'show']);

        //CASH MOVEMENTS
        Route::get('/cashes/movements', [CashMovementController::class, 'index']);
        Route::post('/cashes/{id}/movements', [CashMovementController::class, 'store']);

        //PROVIDER INVOICES
        Route::get('/provider-invoices', [ProviderInvoiceController::class, 'index']);
        Route::get('/provider-invoices/{id}', [ProviderInvoiceController::class, 'show']);
        Route::post('/provider-invoices', [ProviderInvoiceController::class, 'store']);

        //SALES
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{id}', [SaleController::class, 'show']);
        Route::get('/sales/pdf/{id}', [SaleController::class, 'generatePDF']);
        Route::post('/sales', [SaleController::class, 'store']);

        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
