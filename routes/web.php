<?php

use Illuminate\Support\Facades\Route;

Route::get('{any}', function () {
    return view('components.index');
})->where('any', '.*');
