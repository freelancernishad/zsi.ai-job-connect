<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\api\MediaController;
use App\Http\Controllers\api\ResumeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\HiringProcessController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Auth\users\AuthController;
use App\Http\Controllers\Auth\users\VerificationController;
use App\Http\Controllers\Auth\users\PasswordResetController;

Route::post('store/permissions', [RolePermissionController::class, 'storePermissions']);

Route::get('roles', [RoleController::class, 'index']);
Route::post('roles', [RoleController::class, 'store']);
Route::post('roles/{roles}', [RoleController::class, 'update']);
Route::apiResource('permissions', PermissionController::class);

Route::post('get/permissions/{id}', [RoleController::class, 'getPermissionsByRoleName']);
Route::post('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'attachPermission']);
Route::post('roles/{roleId}/permissions', [RolePermissionController::class, 'addPermissionsToRole']);
Route::delete('roles/{role}/permissions/{permission}', [RolePermissionController::class, 'detachPermission']);



// User authentication routes
Route::post('/user/login', [AuthController::class, 'login'])->name('login');
Route::get('/user/check/login', [AuthController::class, 'checkTokenExpiration'])->name('checklogin');
Route::post('/user/check-token', [AuthController::class, 'checkToken']);
Route::post('/user/register', [AuthController::class, 'register']);

// Email verification route
Route::get('/email/verify/{hash}', [VerificationController::class, 'verifyEmail']);

Route::post('/resend/verification-link', [AuthController::class, 'resendVerificationLink']);




Route::middleware(['auth:api'])->group(function () {
    Route::post('/user/logout', [AuthController::class, 'logout'])->name('user.logout');

    Route::prefix('users/role/system')->group(function () {
        Route::get('/', [RoleUserController::class, 'index']);
        Route::post('/', [RoleUserController::class, 'store']);
        Route::post('/{id}', [RoleUserController::class, 'update']);
        Route::get('/{id}', [RoleUserController::class, 'show']);
        Route::delete('/{id}', [RoleUserController::class, 'destroy']);
    });

    Route::post('/user/register/step2', [UserController::class, 'registerStep2']);
    Route::post('/user/register/step3', [UserController::class, 'registerStep3']);

    Route::get('/user/{username}', [UserController::class, 'getUserByUsername']);

    Route::get('/resumes', [ResumeController::class, 'index']);
    Route::post('/resumes', [ResumeController::class, 'store']);
    Route::get('/resumes/{id}', [ResumeController::class, 'show']);
    Route::delete('/resumes/{id}', [ResumeController::class, 'destroy']);

     Route::get('/authenticated/user/resumes', [ResumeController::class, 'getByAuthenticatedUser']);



     Route::prefix('media')->group(function () {
        Route::post('/', [MediaController::class, 'store']);
        Route::get('/', [MediaController::class, 'index']);
        Route::get('/{id}', [MediaController::class, 'show']);
        Route::put('/{id}', [MediaController::class, 'update']);
        Route::delete('/{id}', [MediaController::class, 'destroy']);
    });



    Route::post('/hiring-request', [HiringProcessController::class, 'createHiringRequest']);






    Route::post('users/change-password', [UserController::class, 'changePassword'])
        ->name('users.change_password')
        ->middleware('checkPermission:users.change_password');

    Route::get('/user-access', function () {
        return 'user access';
    })->name('user.access')->middleware('checkPermission:user.access');
});



Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [PasswordResetController::class, 'reset']);





