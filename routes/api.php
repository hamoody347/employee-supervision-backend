<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Login
Route::post('/login', [LoginController::class, 'login']);
Route::get('/fallbacklogin', function () {
    abort(401, 'Not Authenticatedd!');
})->name('login');

// Route::middleware(['auth:sanctum'])->group(function () {
// Auth Routes
Route::post('/logout', [LoginController::class, 'logout']);
Route::get('/user', function (Request $request) {
    return $request->user();
});
// Employee Routes
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/employees/supervisors', [EmployeeController::class, 'employeeSupervisor']);
Route::get('/employees/{id}', [EmployeeController::class, 'show']);
Route::post('/employees', [EmployeeController::class, 'store']);
Route::put('/employees/{id}', [EmployeeController::class, 'update']);
Route::delete('/employees/{id}', [EmployeeController::class, 'delete']);
Route::get('/chart', [EmployeeController::class, 'generateEmployeeChart']);
Route::put('/setsupervisor/{id}', [EmployeeController::class, 'setSupervisor']);
Route::get('/getsupervisors/{id}', [EmployeeController::class, 'getSupervisors']);
// });
