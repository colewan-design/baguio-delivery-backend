<?php

use App\Http\Controllers\Api\Admin\LeadController as AdminLeadController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\RiderController as AdminRiderController;
use App\Http\Controllers\Api\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Api\ActivationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Api\Customer\VendorController as CustomerVendorController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\Rider\RegistrationController as RiderRegistrationController;
use App\Http\Controllers\Api\Rider\RiderController;
use App\Http\Controllers\Api\Vendor\MenuItemController;
use App\Http\Controllers\Api\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Api\Vendor\RegistrationController as VendorRegistrationController;
use App\Http\Controllers\Api\Vendor\VendorProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/leads', [LeadController::class, 'store']);
Route::post('/vendors/register', [VendorRegistrationController::class, 'store']);
Route::post('/riders/register', [RiderRegistrationController::class, 'store']);
Route::post('/activate/{user}', [ActivationController::class, 'store'])
    ->name('activation.complete')
    ->middleware('signed');
Route::post('/verify-email/{user}', [EmailVerificationController::class, 'store'])
    ->name('verification.complete')
    ->middleware('signed');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Customer
    Route::middleware('role:customer')->group(function () {
        Route::get('/vendors', [CustomerVendorController::class, 'index']);
        Route::get('/vendors/{vendor}', [CustomerVendorController::class, 'show']);
        Route::get('/vendors/{vendor}/menu', [CustomerVendorController::class, 'menu']);
        Route::get('/orders', [CustomerOrderController::class, 'index']);
        Route::post('/orders', [CustomerOrderController::class, 'store']);
        Route::get('/orders/{order}', [CustomerOrderController::class, 'show']);
    });

    // Vendor
    Route::middleware('role:vendor')->prefix('vendor')->group(function () {
        Route::get('/orders', [VendorOrderController::class, 'index']);
        Route::patch('/orders/{order}/accept', [VendorOrderController::class, 'accept']);
        Route::patch('/orders/{order}/reject', [VendorOrderController::class, 'reject']);
        Route::patch('/orders/{order}/ready', [VendorOrderController::class, 'ready']);

        Route::get('/menu', [MenuItemController::class, 'index']);
        Route::post('/menu', [MenuItemController::class, 'store']);
        Route::patch('/menu/{menuItem}', [MenuItemController::class, 'update']);
        Route::delete('/menu/{menuItem}', [MenuItemController::class, 'destroy']);

        Route::patch('/status', [VendorProfileController::class, 'update']);
    });

    // Rider
    Route::middleware('role:rider')->prefix('rider')->group(function () {
        Route::patch('/status', [RiderController::class, 'updateStatus']);
        Route::patch('/location', [RiderController::class, 'updateLocation']);
        Route::get('/jobs', [RiderController::class, 'jobs']);
        Route::patch('/orders/{order}/pickup', [RiderController::class, 'pickup']);
        Route::patch('/orders/{order}/deliver', [RiderController::class, 'deliver']);
    });

    // Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/vendors', [AdminVendorController::class, 'index']);
        Route::post('/vendors', [AdminVendorController::class, 'store']);
        Route::patch('/vendors/{vendor}', [AdminVendorController::class, 'update']);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{order}/reassign-rider', [AdminOrderController::class, 'reassignRider']);

        Route::get('/riders', [AdminRiderController::class, 'index']);
        Route::post('/riders', [AdminRiderController::class, 'store']);

        Route::get('/leads', [AdminLeadController::class, 'index']);
        Route::patch('/leads/{lead}', [AdminLeadController::class, 'update']);
    });
});
