<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\QuotationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::prefix('v1')->group(function () {
    Route::get('/leads', [LeadController::class, 'index']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign']);
    Route::post('/leads/{lead}/qualify', [LeadController::class, 'qualify']);
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert']);

    Route::post('/activities', [ActivityController::class, 'store']);

    Route::post('/opportunities/{opportunity}/stage', [OpportunityController::class, 'updateStage']);

    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::post('/quotations/{quotation}/approve', [QuotationController::class, 'approve']);
    Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convert']);
});
