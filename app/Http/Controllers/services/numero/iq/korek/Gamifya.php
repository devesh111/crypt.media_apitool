<?php
namespace App\Http\Controllers\services\numero\iq\korek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gamifya extends Controller
{
    private $config = [
        'send_pin' => 'http://143.198.213.74/prod/IQKGcmp/sendPIN',
        'verify_pin' => 'http://143.198.213.74/prod/IQKGcmp/verifyPIN',
        'antifraud_url' => 'http://143.198.213.74/prod/IQKGcmp/antifraud',
        'service_name' => 'gamifya',
        'unsub_code' => '01',
        'sub_code' => '',
        'shortcode' => '2015',
        'pack_validity' => '1',
        'pack_price' => '240',
        'currency' => 'IQD',
        'pin_length' => 4,

        // custom params
        'cid' => '172',
    ];


    public function index(Request $request)
    {
        return response()->json(['message' => 'Welcome to Gamifya API']);
    }

    public function pinRequest(Request $request)
    {
        try {

            $msisdn = $request->input('msisdn');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();
            $cta_btn = $request->input('cta_btn');
            $txid = strtoupper(Str::random(16));

            $response = Http::get($this->config['send_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'ip' => $ip,
                'ua' => $ua,
            ]);

            $script = '';
            $antifraudRaw = '';

            if ($response->successful() && $response->json('response') == 'SUCCESS') {

                try {
                    $antifraud = Http::get($this->config['antifraud_url'], [
                        'msisdn' => $msisdn,
                        'ti' => $txid,
                        'ts' => time(),
                        'te' => $cta_btn,
                    ]);

                    $antifraudRaw = $antifraud->body();

                    if ($antifraud->successful()) {
                        $script = $antifraud->json('s');
                    }

                } catch (\Throwable $e) {
                    $antifraudRaw = $e->getMessage();
                }

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'script' => $script,
                    'raw' => [
                        'pin_request' => $response->body(),
                        'antifraud' => $antifraudRaw,
                    ]
                ]);
            }

            return response()->json([
                'status' => '0',
                'message' => 'pin failed',
                'script' => '',
                'raw' => [
                    'pin_request' => $response->body(),
                    'antifraud' => $antifraudRaw,
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
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $ts = $request->input('ts');

            $response = Http::get($this->config['verify_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'pin' => $pin,
                'ip' => $ip,
                'ti' => $txid,
                'ts' => $ts,
            ]);

            if ($response->successful() && $response->json('response') == 'SUCCESS') {

                return response()->json([
                    'status' => '1',
                    'message' => 'pin verified',
                    'raw' => $response->body(),
                ]);
            }

            return response()->json([
                'status' => '0',
                'message' => 'pin verification failed',
                'raw' => $response->body(),
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