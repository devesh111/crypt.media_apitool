<?php
namespace App\Http\Controllers\services\cryptmedia\numero\iq\asiacell;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gamifya extends Controller
{
    private $config = [
        'send_pin' => 'http://143.198.213.74/prod/IQAGcmp/sendPIN',
        'verify_pin' => 'http://143.198.213.74/prod/IQAGcmp/verifyPIN',
        'antifraud_url' => 'https://antifraud-vms.iraqcom.com/Prepare',
        'service_name' => 'gamifya',
        'unsub_code' => '0',
        'sub_code' => '',
        'shortcode' => '2162',
        'pack_validity' => '1',
        'pack_price' => '360',
        'currency' => 'IQD',
        'pin_length' => 4,

        // custom params
        'cid' => '94',
        'channel_id' => 22796,

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
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $txid = $request->has('txid') ? $request->input('txid') : uniqid();

            $antifraud_response = $this->getAntifraudKeys($request, $txid, 1);

            $antifraudid = $antifraud_response['antifraudid'];
            $uniqid = $antifraud_response['uniqid'];
            $script = $antifraud_response['script'];

            $response = Http::get($this->config['send_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'ip' => $ip,
                'ua' => $ua,
                'sessionKey' => $antifraudid
            ]);

            if ($response->successful() && $response->json('response') == 'SUCCESS') {

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'script' => $script,
                    'antifraudid' => $antifraudid,
                    'uniqid' => $uniqid,
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

    private function getAntifraudKeys($request, $clickId, $page, $msisdn = '')
    {
        $response = Http::get($this->config['antifraud_url'], [
            'Page' => $page,
            'ChannelID' => $this->config['channel_id'],
            'ClickID' => $clickId,
            'Headers' => base64_encode(json_encode($request->headers->all())),
            'UserIP' => base64_encode($request->ip()),
            'MSISDN' => $msisdn, // empty on MSISDN page, populated on OTP page
        ]);

        // Response headers from the antifraud API
        $antifraudid = $response->header('antifrauduniqid');
        $uniqid = $response->header('mcpuniqid');

        // Extract the JS snippet (comment block onwards) from the body
        $body = $response->body();
        $script = null;

        if (preg_match('/\/\*[\s\S]*/', $body, $matches)) {
            $script = $matches[0];
        }

        return [
            'antifraudid' => $antifraudid,
            'uniqid' => $uniqid,
            'script' => $script,
        ];
    }
    public function pinVerification(Request $request)
    {
        try {
            $msisdn = $request->input('msisdn');
            $pin = $request->input('pin');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();

            $txid = $request->input('txid');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';

            $antifraud_response = $this->getAntifraudKeys($request, $txid, 2, $msisdn);

            $response = Http::get($this->config['verify_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'pin' => $pin,
                'ip' => $ip,
                'sessionKey' => $antifraud_response['antifraudid']
            ]);

            if ($response->successful() && $response->json('response') == 'SUCCESS') {

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

}