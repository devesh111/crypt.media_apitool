<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/{company}/{partner}/{country}/{operator}/{offer_name}/{method?}', [ApiController::class, 'handle'])
    ->where([
        'country'    => '[a-zA-Z]+',
        'company'    => '[a-zA-Z]+',
        'partner'    => '[a-zA-Z]+',
        'operator'   => '[a-zA-Z]+',
        'offer_name' => '[a-zA-Z0-9_-]+',
        'method'     => '[a-zA-Z0-9_-]+',
    ])
    ->name('offer.dynamic');

// pin_request
//1) msisdn
//2) ip (optional)
//3) ua (optional)