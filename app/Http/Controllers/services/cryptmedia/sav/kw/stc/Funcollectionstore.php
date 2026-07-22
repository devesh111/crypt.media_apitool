<?php
namespace App\Http\Controllers\services\cryptmedia\sav\kw\stc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Funcollectionstore extends Controller
{
    private $config = [
        'send_pin' => 'https://savmvas.com/cntwb/sendpin',
        'verify_pin' => 'https://savmvas.com/cntwb/verifypin',
        'antifraud_url' => 'http://app.globocom.info/mglobopay/scriptCall',
        'service_name' => 'funcollectionstore',
        'unsub_code' => 'Stop 1',
        'sub_code' => '',
        'shortcode' => '50777',
        'pack_validity' => '30',
        'pack_price' => '4',
        'currency' => 'KWD',
        'pin_length' => 4,
        // custom params
        'cid' => '552',
        'pub_id' => 'PUB7656',
        'sub_pub_id' => '64564/PUB67676',
        'service_id' => '8854',
    ];


    public function index(Request $request)
    {
        return response()->json(['message' => 'Welcome to Fun Collection Store API']);
    }

    public function pinRequest(Request $request)
    {
        try {

            $msisdn = $request->input('msisdn');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : 'cta_btn';
            $txid = $request->has('txid') ? $request->input('txid') : uniqid();

            $response = Http::get($this->config['send_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'user_ip' => $ip,
                'ua' => $ua,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
                'sessionKey' => '',
            ]);

            $antifraudRaw = '';

            if ($response->successful() && $response->json('status') == true) {
                $antifraudRaw = $this->getAntifraudKeys($request, 2, $msisdn);
                $sessionKey = $response->json('sessionKey');

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'script' => $antifraudRaw['script'],
                    'ti' => $antifraudRaw['ti'],
                    'ts' => $antifraudRaw['ts'],
                    'session_key' => $sessionKey,
                    'raw' => [
                        'pin_request' => $response->body(),
                        'antifraud' => $antifraudRaw,
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
            $ti = $request->input('ti');
            $sessionKey = $request->input('session_key');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';

            $response = Http::get($this->config['verify_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'user_ip' => $ip,
                'ua' => $ua,
                'otp' => $pin,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
                'sessionKey' => $sessionKey,
                'ti' => $ti,
            ]);

            if ($response->successful() && $response->json('status') == true) {

                return response()->json([
                    'status' => '1',
                    'message' => 'pin verified',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'raw' => $response->body(),
                ]);
            }

            return response()->json([
                'status' => '0',
                'message' => 'pin verification failed',
                'txid' => $txid,
                'cta_btn' => $cta_btn,
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

    private function getAntifraudKeys($request, $page, $msisdn) {
        $response = Http::get($this->config['antifraud_url'], [
            'serviceId' => $this->config['service_id'],
            'header' => base64_encode(json_encode($request->headers->all())),
            'page' => $page,
            'ip' => $request->ip(),
            'msisdn' => $msisdn,
        ]);

        if($response->successful()) {
            return [
                'script' => $response->json('script'),
                'ti' => $response->json('ti'),
                'ts' => $response->json('ts'),
            ];
        }
        return [
            'script' => '',
            'ti' => '',
            'ts' => '',
        ];
    }
}

