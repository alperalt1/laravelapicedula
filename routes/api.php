<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Consultar;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MailTestController;
use App\Http\Controllers\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('/registrar', [AuthController::class, 'registrar']);
Route::post('/iniciarsesion', [AuthController::class, 'iniciarsesion']);
Route::post('/enviarcorreocontrasena', [AuthController::class, 'enviarcorreocontrasena']);
Route::post('/recuperarcontrasena', [AuthController::class, 'recuperarcontrasena']);
Route::post('/consultar', [Consultar::class, 'consultar']);
Route::post('/payphone/webhook', [Pago::class, 'payphoneWebhook']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/consultar', [Consultar::class, 'consultar']);
    Route::get('/informacionsuscripcion',[Consultar::class, 'informacionsuscripcion']);
    Route::get('/historialconsulta',[Consultar::class, 'historialconsulta']);
    Route::get('/exportar',[ExcelController::class, 'exportar']);
    Route::post('/generarorden',[Pago::class, 'generarorden']);
});