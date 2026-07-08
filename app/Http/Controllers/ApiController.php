<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Generic dispatcher for:
 *   /{country}/{partner}/{operator}/{offer_name}/{method?}
 *
 * Resolves to:
 *   App\Http\Controllers\services\{country}\{partner}\{operator}\{OfferName}
 *
 * e.g. /iq/numero/zain/gamebase/pin_request ->
 *      App\Http\Controllers\services\iq\numero\zain\Gamebase::pinRequest()
 *
 * Onboarding a new offer/partner/operator = drop in a new controller
 * (+ matching view folder) at the right namespace/path. No new routes,
 * no DB entry required.
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

    public function handle(Request $request, $partner, $country, $operator, $offer_name, $method = 'index')
    {
        $country  = strtolower($country);
        $partner  = strtolower($partner);
        $operator = strtolower($operator);

        $offerClass = Str::studly($offer_name); // gamebase -> Gamebase, game-cafe -> GameCafe

        $fqcn = 'App\\Http\\Controllers\\services\\' . $partner . '\\' . $country . '\\' . $operator . '\\' . $offerClass;

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
            'country'    => $country,
            'partner'    => $partner,
            'operator'   => $operator,
            'offer_name' => strtolower($offer_name),
        ]);

        $instance = new $fqcn();

        return $instance->{$controllerMethod}($request);
    }
}