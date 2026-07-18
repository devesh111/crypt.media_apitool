<?php
namespace App\Http\Controllers\services\cryptmedia\numero\ae\etisalat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Videowarrior extends Controller
{
    private $config = [
        'send_pin' => 'http://159.89.163.174/panel/sendpin',
        'verify_pin' => 'http://159.89.163.174/panel/verifypin',
        'antifraud_url' => '',
        'service_name' => 'videowarrior',
        'unsub_code' => '1111',
        'sub_code' => '',
        'shortcode' => '',
        'pack_validity' => '7',
        'pack_price' => '3.25',
        'currency' => 'AED',
        'pin_length' => 4,

        // custom params
        'cid' => '2861',
        'pub_id' => 'PUB1121',
        'sub_pub_id' => '25978/PUB2212',
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
            $timestamp = time();

            $response = Http::get($this->config['send_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
                'user_ip' => $ip,
                'ua' => $ua,
                'sessionKey' => $txid,
                'timestamp' => $timestamp,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
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
                'raw' => [
                    'pin_request' => $response->body(),
                ]
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => '0',
                'message' => $e->getMessage(),
                'raw' => '',
            ]);
        }
    }

    public function pinVerification(Request $request)
    {
        try {
            $msisdn = $request->input('msisdn');
            $pin = $request->input('pin');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();

            // These MUST be the same values used in the antifraud request
            $txid = $request->has('txid') ? $request->input('txid') : uniqid();
            $ts = $request->input('ts');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $timestamp = time();

            $response = Http::get($this->config['verify_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'otp' => $pin,
                'user_ip' => $ip,
                'ua' => $ua,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
                'sessionKey' => $txid,
                'timestamp' => $timestamp,
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
