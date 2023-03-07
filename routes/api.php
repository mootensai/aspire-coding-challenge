<?php

use App\Http\Controllers\Api\WeeklyLoanController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

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

/**
 * Admin Get List of Users
 * route "/user"
 * @method "GET"
 */
Route::middleware(['auth:api', 'role'])->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * route "/register"
 * @method "POST"
 */
Route::post('/register', RegisterController::class)->name('register');

/**
 * route "/login"
 * @method "POST"
 */
Route::post('/login', LoginController::class)->name('login');

/**
 * route "/logout"
 * @method "POST"
 */
Route::post('/logout', LogoutController::class)->name('logout');

/**
 * API resource for weekly loans.
 * route "/weekly-loans"
 * @method "GET" -> WeeklyLoanController@index, "POST" -> WeeklyLoanController@store
 */
Route::apiResource('/weekly-loans', WeeklyLoanController::class)->middleware('auth:api');

/**
 * Get Weekly Loan detail.
 * route "/weekly-loans/{wl_hashid}"
 * @method "GET"
 */
Route::get('/weekly-loans/{wl_hashid}', [WeeklyLoanController::class, 'show'])->middleware(['auth:api', 'role'])->name('weekly-loans.show');

/**
 * Make payment to Weekly Loan.
 * route "/weekly-loans/pay/{wl_hashid}"
 * @method "POST"
 */
Route::post('weekly-loans/pay/{wl_hashid}', [WeeklyLoanController::class, 'payment'])->middleware(['auth:api', 'idempotent'])->name('weeklyloans.payment');

/**
 * Admin approve Weekly Loan.
 * route "/weekly-loans/approve/{wl_hashid}"
 * @method "PATCH"
 */
Route::patch('/weekly-loans/approve/{wl_hashid}', [WeeklyLoanController::class, 'approve'])->middleware(['auth:api', 'role'])->name('weekly-loans.approve');

/**
 * Admin reject Weekly Loan.
 * route "/weekly-loans/approve/{wl_hashid}"
 * @method "PATCH"
 */
Route::patch('/weekly-loans/reject/{wl_hashid}', [WeeklyLoanController::class, 'reject'])->middleware(['auth:api', 'role'])->name('weekly-loans.reject');

