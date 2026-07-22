<?php
namespace App\Http\Controllers\services\cryptmedia\sav\kw\ooredoo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Funcollectionstore extends Controller
{
    private $config = [
        'send_pin' => 'https://savmvas.com/cntwb/sendpin',
        'verify_pin' => 'https://savmvas.com/cntwb/verifypin',
        'antifraud_url' => '',
        'service_name' => 'funcollectionstore',
        'unsub_code' => 'stop 1',
        'sub_code' => '1',
        'shortcode' => '1934',
        'pack_validity' => '30',
        'pack_price' => '3.5',
        'currency' => 'KWD',
        'pin_length' => 4,
        // custom params
        'cid' => '550',
        'pub_id' => 'PUB9091',
        'sub_pub_id' => '89703/PUB9091',
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

            if ($response->successful() && $response->json('status') == true) {
                $sessionKey = $response->json('sessionKey');
                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'script' => '',
                    'session_key' => $sessionKey,
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
                'antifraudkey' => '',
                'mcpuniqid' => '',
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
            $session_key = $request->input('session_key');
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
                'sessionKey' => $session_key,
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
}

