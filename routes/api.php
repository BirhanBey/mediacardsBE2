<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// public routes
// get all records
Route::get('/accounts', [AccountController::class, 'index']);
// get specified account
Route::get('/accounts/{id}', [AccountController::class, 'show']);
// search account by name
Route::get('/accounts/search/{name}', [AccountController::class, 'search']);
// new user registeration
Route::post('/register', [AuthController::class, 'register']);
// user login and token creation
Route::post('/login', [AuthController::class, 'login']);

// protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    // sent a new record to accounts table
    Route::post('/accounts', [AccountController::class, 'store']);
    // change the informations of accounts table by id
    Route::put('/accounts/{id}', [AccountController::class, 'update']);
    // delete item from accounts table
    Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);
    // create new url list
    Route::post('/accounts/{id}', [AccountController::class, 'url_post']);
    // change the informations of url table by id
    Route::put('/accounts/{id}/urls/{url_id}', [AccountController::class, 'url_update']);
    // delete item from url table
    Route::delete('/accounts/{id}/urls/{url_id}', [AccountController::class, 'url_destroy']);
    // user logout
    Route::post('/logout', [AuthController::class, 'logout']);

});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
