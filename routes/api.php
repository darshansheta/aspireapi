<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoansController;
use App\Models\Loan;

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


Route::post('login', [AuthController::class, 'login'])->name('auth.login');
Route::post('register', [AuthController::class, 'register'])->name('auth.register');

Route::middleware([
    'auth:sanctum',
    'dbtransaction'
])->group(function () {
    Route::post('loans',                [LoansController::class, 'store'])->name('loans.store');
    Route::get('loans',                 [LoansController::class, 'index'])->name('loans.index');
    Route::get('loans/{loan}',          [LoansController::class, 'show'])->name('loans.show')->can('view', 'loan');
    Route::put('loans/{loan}/approve',  [LoansController::class, 'approve'])->name('loans.approve')->middleware('admin');
    Route::post('loans/{loan}/repay',   [LoansController::class, 'repay'])->name('loans.repay')->can('update', 'loan');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
