<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChangeController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClassicAuth;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('change/{type}', [ChangeController::class, 'getChangeType']);


Route::prefix('auth')->group(function () {
    Route::get('register', [ClassicAuth::class, 'register']);
    Route::get('login_new', [ClassicAuth::class, 'login_new']);
    Route::get('login_old', [ClassicAuth::class, 'login_old']);
    Route::get('logout/{user_id}', [ClassicAuth::class, 'logout']);
});

Route::prefix('{user_id}')->group(function () {
    Route::get('wallet', [WalletController::class, 'getWallet']);
    Route::prefix('transaction')->group(function (){
        Route::get('/{transaction_type}', [TransactionController::class, 'addTransaction']);
        Route::post('/update', [TransactionController::class, 'updateTransaction']);
        Route::prefix('{account_id}')->group(function (){
            Route::get('get', [TransactionController::class, 'getAll']);
            Route::get('get_by_month/{month}', [TransactionController::class, 'getByMonth']);
        }); 
    });
    Route::get('report/{account_id}/{month}', [TransactionController::class, 'monthlyReport']);
    Route::prefix('profile')->group(function (){
        Route::get('get', [ProfileController::class, 'getProfile']);
        Route::get('edit', [ProfileController::class, 'editProfile']);
    });
});

