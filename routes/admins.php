<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;

use App\Http\Controllers\Auth\admins\AdminAuthController;
use App\Http\Controllers\Backend\ServiceController as BackendServiceController;
use App\Http\Controllers\Backend\SkillListController as BackendSkillListController;


// Admin auth routes
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/check/login', [AdminAuthController::class, 'checkTokenExpiration']);
Route::post('/admin/check-token', [AdminAuthController::class, 'checkToken']);
Route::post('/admin/register', [AdminAuthController::class, 'register']);

Route::middleware('auth:admin')->group(function () {
    Route::post('admin/logout', [AdminAuthController::class, 'logout']);
    Route::get('/admin-access', function () {
        return 'admin access';
    });




    Route::get('services', [BackendServiceController::class, 'index']);
    Route::get('services/{id}', [BackendServiceController::class, 'show']);
    Route::post('services', [BackendServiceController::class, 'store']);
    Route::put('services/{id}', [BackendServiceController::class, 'update']);
    Route::delete('services/{id}', [BackendServiceController::class, 'destroy']);

    Route::get('skill-lists', [BackendSkillListController::class, 'index']);
    Route::get('skill-lists/{id}', [BackendSkillListController::class, 'show']);
    Route::post('skill-lists', [BackendSkillListController::class, 'store']);
    Route::put('skill-lists/{id}', [BackendSkillListController::class, 'update']);
    Route::delete('skill-lists/{id}', [BackendSkillListController::class, 'destroy']);
    Route::get('services/{serviceId}/skill-lists', [BackendSkillListController::class, 'listByService']);




    Route::prefix('admin')->group(function () {
        Route::get('users-with-pending-payments', [AdminUserController::class, 'getUsersWithPendingPayments']);
        Route::post('approve-payment/{paymentId}', [AdminUserController::class, 'approvePayment']);
    });

});
