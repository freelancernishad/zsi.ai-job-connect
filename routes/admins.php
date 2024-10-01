<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HiringProcessController;

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\Auth\admins\AdminAuthController;
use App\Http\Controllers\api\EmployeeHiringPriceController;
use App\Http\Controllers\Backend\ServiceController as BackendServiceController;
use App\Http\Controllers\Backend\SkillListController as BackendSkillListController;


// Admin auth routes
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/check/login', [AdminAuthController::class, 'checkTokenExpiration']);
Route::get('/admin/check-token', [AdminAuthController::class, 'checkToken']);
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
        Route::post('cancel-payment/{paymentId}', [AdminUserController::class, 'cancelPayment']);
    });




    // Route to get requests by step
    Route::get('/hiring-requests/step/{step}', [HiringProcessController::class, 'getRequestsByStep']);

    // Route to get all hiring requests (admin only)
    Route::get('/hiring-requests', [HiringProcessController::class, 'getAllRequests']);

    // Route to get requests by employer
    Route::get('/hiring-requests/employer/{employerId}', [HiringProcessController::class, 'getRequestsByEmployer']);

    // Route to get requests by step with pagination
    Route::get('/hiring-requests/step/{step}/pagination', [HiringProcessController::class, 'getRequestsByStepWithPagination']);



    Route::post('/hiring-request/{id}/assign', [HiringProcessController::class, 'assignEmployee']);
    Route::get('/hiring-request/{id}', [HiringProcessController::class, 'getHiringRequest']);



    Route::prefix('employee-hiring-prices')->group(function () {
        Route::post('/', [EmployeeHiringPriceController::class, 'store']); // Create new record
        Route::put('/{employeeHiringPrice}', [EmployeeHiringPriceController::class, 'update']); // Update a record
        Route::delete('/{employeeHiringPrice}', [EmployeeHiringPriceController::class, 'destroy']); // Delete a record
    });


    Route::prefix('transactions')->group(function () {
        Route::get('/all', [TransactionController::class, 'getAllTransactions']);
        Route::get('/by-type', [TransactionController::class, 'getTransactionsByType']);
        Route::get('/by-user/{userId}', [TransactionController::class, 'getTransactionsByUser']);
    });


    Route::get('admin/users/search', [AdminUserController::class, 'getUsersBySearch']);

});

Route::get('/transactions/hiring-request/{hiringRequestId}', [TransactionController::class, 'getTransactionByHiringRequestId']);
