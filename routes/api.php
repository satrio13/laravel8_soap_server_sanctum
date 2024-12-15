<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// SOAP Routes
Route::prefix('soap')->group(function () 
{
    Route::get('auth/wsdl', [AuthController::class, 'wsdl']);
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::middleware('auth.sanctum')->group(function () 
    {
        Route::get('product/wsdl', [ProductController::class, 'wsdl']);
        Route::post('product/read', [ProductController::class, 'read']);
        Route::post('product/create', [ProductController::class, 'create']);
        Route::post('product/update', [ProductController::class, 'update']);
        Route::post('product/delete', [ProductController::class, 'delete']);
    });
});