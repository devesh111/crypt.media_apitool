<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/{country}/{partner}/{operator}/{offer_name}/{method?}', [ApiController::class, 'handle'])
    ->where([
        'country'    => '[a-zA-Z]+',
        'partner'    => '[a-zA-Z]+',
        'operator'   => '[a-zA-Z]+',
        'offer_name' => '[a-zA-Z0-9_-]+',
        'method'     => '[a-zA-Z0-9_-]+',
    ])
    ->name('offer.dynamic');