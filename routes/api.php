<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SeviceAppControllers\InvoicesController;
use App\Http\Controllers\SeviceAppControllers\CustomerController;


Route::middleware('api.key')->group(function () {


    // show specific customer details
    // required: customer_id
    Route::post('/customer', [CustomerController::class, 'show']);

    // show specific customer subscribed services
    // required: customer_id
    Route::post('/customer/services', [CustomerController::class, 'showServices']);


    // show paid invoices for specific customer
    // required: customer_id
    Route::post('/customer/paid-invoices', [InvoicesController::class, 'show']);
});
