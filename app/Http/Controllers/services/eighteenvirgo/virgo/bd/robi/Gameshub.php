<?php
namespace App\Http\Controllers\services\eighteenvirgo\virgo\bd\robi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Gameshub extends Controller
{
    private $config = [
        'send_pin' => 'http://api.18virgo.com/V7PSHe3Ddb-bdrobi-gh-d/pin_request.asp',
        'verify_pin' => 'http://api.18virgo.com/V7PSHe3Ddb-bdrobi-gh-d/pin_verification.asp',
        'antifraud_url' => '',
        'service_name' => 'gameshub',
        'unsub_code' => '',
        'sub_code' => '',
        'shortcode' => '',
        'pack_validity' => '',
        'pack_price' => '3.03',
        'currency' => 'BDT',
        'pin_length' => 4,
    ];


    public function index(Request $request)
    {
        $context = $request->attributes->get('route_context');

        return view(
            "services.{$context['company']}.{$context['partner']}.{$context['country']}.{$context['operator']}.{$context['offer_name']}.index",
            [
                'config' => $this->config,
                'context' => $context,
            ]
        );
    }

    public function pinRequest(Request $request)
    {
        try {
            $msisdn = $request->input('msisdn');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $txid = $request->has('txid') ? $request->input('txid') : uniqid();

            $response = Http::get($this->config['send_pin'], [
                'msisdn' => $msisdn,
            ]);

            if ($response->successful()) {

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'ref_id' => $response->json('ref_id'),
                    'raw' => [
                        'pin_request' => $response->body(),
                    ]
                ]);
            }

            return response()->json([
                'status' => '0',
                'message' => 'pin failed',
                'txid' => $txid,
                'cta_btn' => $cta_btn,
                'script' => '',
                'raw' => [
                    'pin_request' => $response->body(),
                ]
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => '0',
                'message' => $e->getMessage(),
                'script' => '',
                'raw' => ''
            ]);
        }
    }

    public function pinVerification(Request $request)
    {
        try {
            $msisdn = $request->input('msisdn');
            $pin = $request->input('pin');

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $ref_id = $request->input('ref_id');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';

            $response = Http::get($this->config['verify_pin'], [
                'msisdn' => $msisdn,
                'pin' => $pin,
                'ref_id' => $ref_id,
            ]);

            if ($response->successful() && $response->json('errorCode') == "0") {

                return response()->json([
                    'status' => '1',
                    'message' => 'pin verified',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'raw' => [
                        'pin_verification' => $response->body(),
                    ],
                ]);
            }

            return response()->json([
                'status' => '0',
                'message' => 'pin verification failed',
                'txid' => $txid,
                'cta_btn' => $cta_btn,
                'raw' => [
                    'pin_verification' => $response->body(),
                ],
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => '0',
                'message' => $e->getMessage(),
                'raw' => '',
            ]);
        }
    }
}