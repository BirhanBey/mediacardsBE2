<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
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
// Route::resource('accounts', AccountController::class);

// public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/accounts', [AccountController::class, 'index']);
Route::get('/accounts/{id}', [AccountController::class, 'show']);
Route::get('/accounts/search/{name}', [AccountController::class, 'search']);
Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);


// protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::put('/accounts/{id}', [AccountController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/accounts/{id}/{url_id}', [AccountController::class, 'url_update']);
    Route::delete('/accounts/{id}/urls/{url_id}', [AccountController::class, 'url_destroy']);

});

// Route::get('/accounts', [AccountController::class, 'index']);
// Route::post('/accounts', [AccountController::class, 'store']);
// Route::put('/accounts', [AccountController::class, ])



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
