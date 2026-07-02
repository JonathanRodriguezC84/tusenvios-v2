<?php

use App\Http\Controllers\Api\CarrierApiController;
use App\Http\Controllers\Api\ShippingRateApiController;
use App\Http\Controllers\Api\ShipmentApiController;
use App\Http\Controllers\Api\TrackingApiController;
use Illuminate\Support\Facades\Route;

// ─── Transportadoras (api.key middleware existente) ───

Route::prefix('v1')->middleware('api.key')->group(function () {
    Route::post('/auth/login', [CarrierApiController::class, 'login']);
    Route::get('/shipments', [CarrierApiController::class, 'shipments']);
    Route::post('/shipments/{shipment}/status', [CarrierApiController::class, 'updateStatus']);
    Route::get('/scan/{guideNumber}', [CarrierApiController::class, 'scan']);
});

// ─── Tenants (api.tenant middleware — nuevo) ───

Route::prefix('v1')->middleware('api.tenant')->group(function () {
    Route::get('/my/shipments', [ShipmentApiController::class, 'index']);
    Route::post('/my/shipments', [ShipmentApiController::class, 'store']);
    Route::get('/my/shipments/{shipment}', [ShipmentApiController::class, 'show']);
});

// ─── Público ───

Route::get('/v1/track/{guideNumber}', [TrackingApiController::class, 'show']);
Route::get('/ping', fn () => response()->json(['status' => 'ok', 'version' => '1.0.0']));
Route::get('/shipping-rates', ShippingRateApiController::class);
