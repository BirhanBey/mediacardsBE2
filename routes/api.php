<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// public routes

// new user registeration
Route::post('/register', [AuthController::class, 'register']);
// user login and token creation
Route::post('/login', [AuthController::class, 'login']);
// get all users
Route::get('/users', [AuthController::class, 'index']);
// get specified user
Route::get('/users/{id}', [AuthController::class, 'show']);
// search user by name
Route::get('/users/search/{name}', [AuthController::class, 'search']);

// protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    // create new url list
    Route::post('/users/{id}', [AuthController::class, 'url_post']);
    // change the informations of user by id
    Route::put('/users/{id}', [AuthController::class, 'update']);
    // change the informations of url table by id
    Route::put('/users/{id}/urls/{url_id}', [AuthController::class, 'url_update']);
    // delete item from url table
    Route::delete('/users/{id}/urls/{url_id}', [AuthController::class, 'url_destroy']);
    // delete item from users table
    Route::delete('/users/{id}', [AuthController::class, 'destroy']);
    // user logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // // sent a new record to accounts table
    // Route::post('/accounts', [AccountController::class, 'store']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
