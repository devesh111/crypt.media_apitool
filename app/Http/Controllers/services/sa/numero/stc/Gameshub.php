<?php

namespace App\Http\Controllers\services\sa\numero\stc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Campaign ID: 1086 | Carrier: IQ_zain | Partner: Zain | Operator (aggregator): mediaworld (placeholder - confirm actual name)
 * Service: Gamebase | Price: 300 IQD / Daily | PIN length: 5
 *
 * Simple, stateless-ish integration: no logging, no Redis, no MongoDB,
 * no DB writes, no fallback/switch logic. Straight calls to the
 * pin/anti-fraud/validation endpoints with a normalized JSON response,
 * matching the {response:{status,message,script,raw}} shape used
 * elsewhere in the codebase (see rawapis.txt / Gameshub.php).
 */
class Gameshub extends Controller
{
    private $config = [
        'cmpid'             => 1086,
        'country'           => 'sa',
        'partner'           => 'numero',            // URL/namespace segment - matches services\iq\zain
        'operator'          => 'stc',       // URL/namespace segment - matches services\iq\zain\mediaworld (placeholder, confirm actual operator/aggregator name)
        'operator_display'  => 'SA_stc',          // human-readable carrier name, per campaign sheet
        'offer_name'        => 'gameshub',
        'service_name'      => 'Gameshub',
        'pin_length'        => 5,
        'unsub_shortcode'   => 4089,
        'unsub_keyword'     => '055',
        'price'             => 300,
        'currency'          => 'SAR',
        'billing_freq'      => 'Daily',
        // NOTE: not specified in campaign sheet, confirm with Numero and adjust
        'msisdn_length'     => 10,
        'base_url'          => 'http://143.110.180.96/ctap/inapi',
        'pin_send_path'     => '/pin/send',
        'pin_validate_path' => '/pin/validation',
        'anti_fraud_path'   => '/anti/fraud',
        'lookup_path'       => '/pin/checksub',
        'portal_path'       => '/portal/url',
        // campaign sheet says "Anti Fraud Call: Before" -> must pass before sending pin
        'anti_fraud_before' => true,
    ];

    /**
     * The route context (country/partner/operator/offer_name) is set by
     * ApiController when it resolves this class from the URL. Falls back
     * to the hardcoded config values if called directly (e.g. in tests).
     */
    private function routeContext(Request $request)
    {
        return $request->attributes->get('route_context', [
            'country'    => $this->config['country'],
            'partner'    => $this->config['partner'],
            'operator'   => $this->config['operator'],
            'offer_name' => $this->config['offer_name'],
        ]);
    }

    /**
     * Builds a dotted view path like services.iq.zain.mediaworld.gamebase.index
     * from the current route context, so this controller keeps working
     * unchanged if it's ever mounted under a different country/partner/
     * operator segment.
     */
    private function viewName(Request $request, $view)
    {
        $ctx = $this->routeContext($request);

        return "services.{$ctx['country']}.{$ctx['partner']}.{$ctx['operator']}.{$ctx['offer_name']}.{$view}";
    }

    /**
     * Builds the base URL for this offer, e.g. /iq/zain/mediaworld/gamebase,
     * so the views can construct pin_request/pin_verification URLs
     * without named routes.
     */
    private function baseUrl(Request $request)
    {
        $ctx = $this->routeContext($request);

        return "/{$ctx['country']}/{$ctx['partner']}/{$ctx['operator']}/{$ctx['offer_name']}";
    }

    /**
     * Landing page. Generates a txid used to correlate the pin/anti-fraud/
     * verify calls for this visit. Passed to the view as a hidden field
     * and carried back and forth by the client on every request - no
     * session, no DB.
     */
    public function index(Request $request)
    {
        $txid = (string) Str::uuid();

        return view($this->viewName($request, 'index'), [
            'config'      => $this->config,
            'txid'        => $txid,
            'action_base' => $this->baseUrl($request),
        ]);
    }

    /**
     * Step 1: (optional) anti-fraud check, then send OTP pin.
     * Route: POST /iq/zain/mediaworld/gamebase/pin_request
     *
     * Expects msisdn + txid (hidden field from the index view, carried
     * back by the client) in the request body.
     */
    public function pinRequest(Request $request)
    {
        $request->validate([
            'msisdn' => 'required|numeric',
            'txid'   => 'required|string',
        ]);

        $msisdn = $request->msisdn;
        $txid   = $request->txid;
        $ip     = $request->ip();
        $ua     = $request->userAgent();

        if ($this->config['anti_fraud_before']) {
            $passed = $this->checkAntiFraud($msisdn, $txid, $request->token, $ip, $ua);
            if (!$passed) {
                return response()->json([
                    'response' => [
                        'status'  => '0',
                        'message' => 'anti fraud check failed',
                        'script'  => '',
                        'raw'     => [],
                    ],
                ]);
            }
        }

        $params = [
            'msisdn' => $msisdn,
            'cmpid'  => $this->config['cmpid'],
            'txid'   => $txid,
            'ip'     => $ip,
            'ua'     => $ua,
        ];

        $sendPin = Http::get($this->config['base_url'] . $this->config['pin_send_path'], $params);
        $raw     = json_decode($sendPin->body(), true);

        if ($sendPin->successful() && ($raw['response'] ?? null) === 'SUCCESS') {
            // msisdn/txid are handed straight back to the client and
            // carried forward as hidden fields for the verify step -
            // no session, no DB write.
            return response()->json([
                'response' => [
                    'status'  => '1',
                    'message' => 'pin success',
                    'script'  => '',
                    'raw'     => $raw,
                ],
            ]);
        }

        return response()->json([
            'response' => [
                'status'  => '0',
                'message' => $raw['errorMessage'] ?? 'pin send failed',
                'script'  => '',
                'raw'     => $raw,
            ],
        ]);
    }

    /**
     * Step 2: validate the OTP pin entered by the user.
     * Route: POST /iq/zain/mediaworld/gamebase/pin_verification
     *
     * Expects msisdn + txid to be sent back by the client as hidden
     * fields (same values returned from / used in pin_request), plus
     * the otp the user typed in.
     */
    public function pinVerification(Request $request)
    {
        $request->validate([
            'msisdn' => 'required|numeric',
            'txid'   => 'required|string',
            'otp'    => 'required|digits:' . $this->config['pin_length'],
        ]);

        $msisdn = $request->msisdn;
        $txid   = $request->txid;

        $params = [
            'msisdn' => $msisdn,
            'cmpid'  => $this->config['cmpid'],
            'txid'   => $txid,
            'pin'    => $request->otp,
            'ip'     => $request->ip(),
            'ua'     => $request->userAgent(),
        ];

        $verify = Http::get($this->config['base_url'] . $this->config['pin_validate_path'], $params);
        $raw    = json_decode($verify->body(), true);

        if ($verify->successful() && ($raw['response'] ?? null) === 'SUCCESS') {
            return response()->json([
                'response' => [
                    'status'     => '1',
                    'message'    => 'verify success',
                    'script'     => '',
                    'portal_url' => $this->getPortalUrl($msisdn, $txid),
                    'raw'        => $raw,
                ],
            ]);
        }

        return response()->json([
            'response' => [
                'status'  => '0',
                'message' => $raw['errorMessage'] ?? 'verify failed',
                'script'  => '',
                'raw'     => $raw,
            ],
        ]);
    }

    /**
     * Optional: check if a number is already subscribed before showing the form.
     * Route: GET /iq/zain/mediaworld/gamebase/lookup
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'msisdn' => 'required|numeric',
            'txid'   => 'required|string',
        ]);

        $params = [
            'msisdn' => $request->msisdn,
            'cmpid'  => $this->config['cmpid'],
            'txid'   => $request->txid,
        ];

        $check = Http::get($this->config['base_url'] . $this->config['lookup_path'], $params);
        $raw   = json_decode($check->body(), true);

        return response()->json([
            'response' => [
                'status'  => $check->successful() ? '1' : '0',
                'message' => $raw['errorMessage'] ?? '',
                'script'  => '',
                'raw'     => $raw,
            ],
        ]);
    }

    /**
     * Operator-required footer page: pricing + opt-out instructions.
     * Route: GET /iq/zain/mediaworld/gamebase/footer
     */
    public function footer(Request $request)
    {
        return view($this->viewName($request, 'footer'), [
            'config' => $this->config,
        ]);
    }

    /**
     * Anti-fraud pre-check. Returns true if the operator says it's clean.
     *
     * NOTE: campaign.txt references #token# / #pin_verify_btn_id# but
     * doesn't document how the token is produced. In the Zain/Mediaworld
     * reference controller this comes from an embedded fraud-vendor JS
     * snippet. If Numero gave you a similar snippet, embed it in the
     * index view and have it populate the hidden "token" field before
     * pinRequest() is called.
     */
    private function checkAntiFraud($msisdn, $txid, $token, $ip, $ua)
    {
        $params = [
            'msisdn'     => $msisdn,
            'cmpid'      => $this->config['cmpid'],
            'txid'       => $txid,
            'verify_btn' => 'subscribe-btn',
            'token'      => $token,
            'timestamp'  => time(),
        ];

        $resp = Http::get($this->config['base_url'] . $this->config['anti_fraud_path'], $params);
        $raw  = json_decode($resp->body(), true);

        return $resp->successful() && ($raw['response'] ?? null) === 'SUCCESS';
    }

    /**
     * Fetches the subscriber portal URL to redirect the user to after subscribing.
     */
    private function getPortalUrl($msisdn, $txid)
    {
        $params = [
            'msisdn' => $msisdn,
            'cmpid'  => $this->config['cmpid'],
            'txid'   => $txid,
        ];

        $resp = Http::get($this->config['base_url'] . $this->config['portal_path'], $params);
        $raw  = json_decode($resp->body(), true);

        return $raw['url'] ?? '';
    }
}