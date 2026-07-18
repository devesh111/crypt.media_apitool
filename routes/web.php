<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;
// use App\Models\Offer;

Route::match(['get','post'], '/{slug}.html/{method?}', [ApiController::class, 'handle'])
    ->where([
        'slug' => '[A-Za-z0-9]+',
        'method' => '[A-Za-z0-9_-]+',
    ]);

// Route::get('/offers', function () {
//     return Offer::all();
// });