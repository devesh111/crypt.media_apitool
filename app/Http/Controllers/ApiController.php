<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Offer;

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
        'index' => 'index',
        'pin_request' => 'pinRequest',
        'pin_verification' => 'pinVerification'
    ];

    public function handle(Request $request, $slug, $method = 'index')
    {
        $offer = Offer::where('slug', $slug)
            ->where('active', true)
            ->first();

        if (!$offer) {
            abort(404, 'Offer not found');
        }

        $company = strtolower($offer->company);
        $partner = strtolower($offer->partner);
        $country = strtolower($offer->country);
        $operator = strtolower($offer->operator);
        $offer_name = strtolower($offer->offer_name);

        $offerClass = Str::studly($offer_name);

        $fqcn =
            "App\\Http\\Controllers\\services\\{$company}\\{$partner}\\{$country}\\{$operator}\\{$offerClass}";

        if (!class_exists($fqcn)) {
            abort(404, 'Offer controller not found');
        }

        $controllerMethod = $this->methodMap[$method] ?? Str::camel($method);

        if (!method_exists($fqcn, $controllerMethod)) {
            abort(404, 'Method not found');
        }

        $request->attributes->set('route_context', [
            'company' => $company,
            'partner' => $partner,
            'country' => $country,
            'operator' => $operator,
            'offer_name' => $offer_name,
            'slug' => $slug,
        ]);

        $instance = app($fqcn);

        return $instance->{$controllerMethod}($request);
    }
}