<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Generic dispatcher for:
 *   /{company}/{country}/{partner}/{operator}/{offer_name}/{method?}
 *
 * Resolves to:
 *   App\Http\Controllers\services\{company}\{country}\{partner}\{operator}\{OfferName}
 *
 */

class ApiController extends Controller
{
    /**
     * Maps a URL method segment to the offer controller's method name.
     * Add to this list as new step types are introduced.
     */
    private $methodMap = [
        'index'            => 'index',
        'pin_request'      => 'pinRequest',
        'pin_verification' => 'pinVerification'
    ];

    public function handle(Request $request, $company, $partner, $country, $operator, $offer_name, $method = 'index')
    {
        $company  = strtolower($company);
        $country  = strtolower($country);
        $partner  = strtolower($partner);
        $operator = strtolower($operator);

        $offerClass = Str::studly($offer_name); // gamebase -> Gamebase, game-cafe -> GameCafe

        $fqcn = 'App\\Http\\Controllers\\services\\' . $company . '\\' . $partner . '\\' . $country . '\\' . $operator . '\\' . $offerClass;

        if (!class_exists($fqcn)) {
            abort(404, 'offer not found');
        }

        $controllerMethod = $this->methodMap[$method] ?? Str::camel($method);

        if (!method_exists($fqcn, $controllerMethod)) {
            abort(404, 'method not found');
        }

        // Hand the resolved routing context to the offer controller so it
        // can build its own view name / callback URLs dynamically instead
        // of hardcoding them.
        $request->attributes->set('route_context', [
            'company'    => $company,
            'country'    => $country,
            'partner'    => $partner,
            'operator'   => $operator,
            'offer_name' => strtolower($offer_name),
        ]);

        $instance = new $fqcn();

        return $instance->{$controllerMethod}($request);
    }
}