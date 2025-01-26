<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'API is alive!';
});
Route::get('/server', [MainController::class, 'server']);
Route::prefix('/inbounds')->group(function () {
    Route::get('/', [InboundController::class, 'list']);
    Route::get('/traffic/{id}', [InboundController::class, 'traffic']);
});
Route::prefix('/clients')->group(function () {
    Route::get('/inbound/{id}', [ClientController::class, 'inbound']);
    Route::get('/edit/{uid}', [ClientController::class, 'edit']);
    Route::get('/delete/{uid}', [ClientController::class, 'delete']);
    Route::get('/multi', [ClientController::class, 'multi']);
    Route::get('/create', [ClientController::class, 'create']);
    Route::get('/traffic/{uid}/{inb}', [ClientController::class, 'traffic']);
    Route::get('/enable/{uid}/{inb}', [ClientController::class, 'enable']);
    Route::get('/disable/{uid}/{inb}', [ClientController::class, 'disable']);
});
