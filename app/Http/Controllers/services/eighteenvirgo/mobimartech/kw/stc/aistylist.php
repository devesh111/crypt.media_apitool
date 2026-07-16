<?php
namespace App\Http\Controllers\services\eighteenvirgo\mobimartech\kw\stc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Aistylist extends Controller
{
    private $config = [
        'send_pin' => 'https://api.chefrecipes.net/api/v1/aistylist/kw/subscribe',
        'verify_pin' => 'https://api.chefrecipes.net/api/v1/aistylist/kw/subscribe',
        'service_name' => 'aistylist',
        'unsub_code' => '0',
        'sub_code' => '',
        'shortcode' => '',
        'pack_validity' => '1',
        'pack_price' => '0',
        'currency' => 'KWD',
        'pin_length' => 4,

        // custom fields
        'action_pin_request' => 'subscribe',
        'action_pin_verify' => 'verify',
        'networkname' => 'test',
        'fullUrl' => 'http://kw.test.net/test/',
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
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $txid = $request->has('txid') ? $request->input('txid') : uniqid();

            $response = Http::post($this->config['send_pin'], [
                'action' => $this->config['action_pin_request'],
                'networkname' => $this->config['networkname'],
                'clickid' => $txid,
                'msisdn' => $msisdn,
                'userIp' => $ip,
                'fullUrl' => $this->config['fullUrl'],
                'headers' => [
                    'cf-connecting-ip' => $ip
                ]
            ]);

            if ($response->successful()) {
                $operator = $response->json('operator');
                $userID = $response->json('userID');
                $script = $response->json('script');

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'script' => $script,
                    'user_id' => $userID,
                    'operator' => $operator,
                    'raw' => [
                        'pin_request' => $response->body()
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
                    'pin_request' => $response->body()
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

            $txid = $request->input('txid');
            $user_id = $request->input('user_id');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';

            $response = Http::post($this->config['verify_pin'], [
                'action' => $this->config['action_pin_verify'],
                'msisdn' => $msisdn,
                'pincode' => $pin,
                'userID' => $user_id,
            ]);

            if ($response->successful()) {

                return response()->json([
                    'status' => '1',
                    'message' => 'pin verified',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'raw' => [
                        'pin_verification' => $response->body()
                    ],
                ]);
            }

            return response()->json([
                'status' => '0',
                'message' => 'pin verification failed',
                'txid' => $txid,
                'cta_btn' => $cta_btn,
                'raw' => [
                    'pin_verification' => $response->body()
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