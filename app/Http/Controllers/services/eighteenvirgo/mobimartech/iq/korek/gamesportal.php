<?php
namespace App\Http\Controllers\services\eighteenvirgo\mobimartech\iq\korek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gamesportal extends Controller
{
    private $config = [
        'send_pin' => 'https://api.chefrecipes.net/api/v1/gamesportal/iq/korek/subscribe',
        'verify_pin' => 'https://api.chefrecipes.net/api/v1/gamesportal/iq/korek/verify',
        'antifraud_url' => 'https://antifraud.cgparcel.net/AntiFraud/Prepare/',
        'service_name' => 'gamesportal',
        'unsub_code' => '',
        'sub_code' => '',
        'shortcode' => '',
        'pack_validity' => '',
        'pack_price' => '',
        'currency' => 'IQD',
        'pin_length' => 6,
        
        //custom fields
        'channel_id' => '99939164',
        'action_pin_request' => 'subscribe',
        'networkname' => 'test',
        'action_pin_verify' => 'verify',
    ];


    public function index(Request $request)
    {
        return response()->json(['message' => 'Welcome to Games Portal API']);
    }

    public function pinRequest(Request $request)
    {
        try {
            $msisdn = $request->input('msisdn');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $txid = $request->has('txid') ? $request->input('txid') : uniqid();

            $antifraud_response = $this->getAntifraudKeys($request, 1);

            $antifraudid = $antifraud_response['antifraudid'];
            $uniqid = $antifraud_response['uniqid'];
            $script = $antifraud_response['script'];
            $cid = $antifraud_response['cid'];

            $response = Http::post($this->config['send_pin'], [
                'action' => $this->config['action_pin_request'],
                'networkname' => $this->config['networkname'],
                'clickid' => $txid,
                'cid' => $cid,
                'msisdn' => $msisdn,
                'mcpid' => $antifraudid,
            ]);

            if ($response->successful()) {

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'antifraudid' => $antifraudid,
                    'uniqid' => $uniqid,
                    'cid' => $cid,
                    'user_id' => $response->json('userID'),
                    'operator' => $response->json('operator'),
                    'script' => $script,
                    'raw' => [
                        'pin_request' => $response->body(),
                        'antifraud' => $antifraud_response,
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
                    'antifraud' => '',
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
            $user_id = $request->input('user_id');

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';

            $antifraud_response = $this->getAntifraudKeys($request, 2, $msisdn);

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
                        'pin_verification' => $response->body(),
                        'antifraud' => $antifraud_response,
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
                    'antifraud' => $antifraud_response,
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

    private function getAntifraudKeys($request, $page, $msisdn = '')
    {
        $uniqueString = md5(uniqid());
        $cid = substr($uniqueString, 0, 50);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=UTF-8',
        ])->get($this->config['antifraud_url'], [
            'Page' => $page,
            'ChannelID' => $this->config['channel_id'],
            'ClickID' => $cid,
            'Headers' => base64_encode(json_encode($request->headers->all())),
            'UserIP' => base64_encode($request->ip()),
            'MSISDN' => $msisdn, // empty on MSISDN page, populated on OTP page
        ]);

        $antiFrauduniqid = '';
        $script = '';
        $uniqid = '';

        $script = $response->body();
        
        $antiFrauduniqid = $response->header('AntiFrauduniqid') ?? '';
        $uniqid = $response->header('uniqid') ?? '';

        return [
            'antifraudid' => $antiFrauduniqid,
            'uniqid' => $uniqid,
            'script' => $script,
            'cid' => $cid,
        ];
    }
}