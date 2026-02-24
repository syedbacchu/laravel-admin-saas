<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\Post\BlogCommentController;
use App\Http\Controllers\Api\Post\BlogController;
use App\Http\Controllers\Api\User\ProfileController;
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

Route::group(['prefix' => 'auth', 'as' => 'apiAuth.'], function () {
    Route::get('test-connection', [AuthController::class, 'test'])->name('test');
    Route::post('login', [AuthController::class, 'apiLogin'])->name('login');
});

Route::group(['prefix' => 'blogs', 'as' => 'apiBlog.'], function () {
    Route::get('/', [BlogController::class, 'index'])->name('list');
    Route::get('{identifier}/comments', [BlogCommentController::class, 'index'])->name('comments');
    Route::post('{identifier}/comments', [BlogCommentController::class, 'store'])->name('commentStore');
    Route::get('{identifier}', [BlogController::class, 'show'])->name('details');
});

Route::group(['middleware' => ['auth:api'],'prefix' => 'user', 'as' => 'apiUser.', ], function () {
    Route::get('profile', [ProfileController::class, 'profile'])->name('profile');
});
