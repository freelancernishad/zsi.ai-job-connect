<?php

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\ServerStatusController;

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\Global\GlobalUserController;
use App\Http\Controllers\api\EmployeeHiringPriceController;
use App\Http\Controllers\Global\ServiceController as GlobalServiceController;
use App\Http\Controllers\Global\SkillListController as GlobalSkillListController;
use App\Http\Controllers\Backend\SkillListController as BackendSkillListController;
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





Route::get('/userAgent', function (Request $request) {
    return request()->header('User-Agent');
});

Route::get('/weather', [WeatherController::class, 'show']);

Route::get('/social-links', [SocialLinkController::class, 'index']);
Route::get('/social-links/{platform}', [SocialLinkController::class, 'showByPlatform']);

Route::get('/pages/slug/{slug}', [PageController::class, 'showBySlug']);

Route::get('advertisements', [AdvertisementController::class, 'index']);

Route::get('/visitors', [VisitorController::class, 'index']);
Route::get('/visitors/reports', [VisitorController::class, 'generateReports']);




Route::get('services', [GlobalServiceController::class, 'index']);
Route::get('/other/services', [GlobalServiceController::class, 'other_services']);
Route::get('services/{id}', [GlobalServiceController::class, 'show']);

Route::get('skill-lists', [GlobalSkillListController::class, 'index']);
Route::post('add/skill-lists', [BackendSkillListController::class, 'store']);
Route::get('skill-lists/{id}', [GlobalSkillListController::class, 'show']);
Route::get('services/{serviceId}/skill-lists', [GlobalSkillListController::class, 'listByService']);


Route::get('/global/users/filter', [GlobalUserController::class, 'filterUsers']);



Route::prefix('employee-hiring-prices')->group(function () {
    Route::get('/', [EmployeeHiringPriceController::class, 'index']); // Get all records
    Route::get('/{employeeHiringPrice}', [EmployeeHiringPriceController::class, 'show']); // Get a single record
});










Route::post('/stripe/create/payment', [StripePaymentController::class, 'createPayment']);
Route::get('/stripe/confirm/payment', [StripePaymentController::class, 'paymentSuccess']);


Route::post('stripe/webhook', [StripePaymentController::class, 'handleWebhook']);



Route::get('/server-status', [ServerStatusController::class, 'status']);
