<?php
namespace App\Http\Controllers\services\cryptmedia\sav\kw\zain;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Funcollectionstore extends Controller
{
    private $config = [
        'send_pin' => 'https://savmvas.com/cntwb/sendpin',
        'verify_pin' => 'https://savmvas.com/cntwb/verifypin',
        'antifraud_url' => 'https://antifraud.cgparcel.net/AntiFraud/Prepare',
        'service_name' => 'funcollectionstore',
        'unsub_code' => 'unsub 288',
        'sub_code' => '288',
        'shortcode' => '97979',
        'pack_validity' => '7',
        'pack_price' => '1',
        'currency' => 'KWD',
        'pin_length' => 4,
        // custom params
        'cid' => '551',
        'pub_id' => 'PUB9456',
        'sub_pub_id' => '463434/PUB9456',
        'ChannelID' => '99941372',
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
                $antifraudRaw = $this->getAntifraudKeys($request, $txid);

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'script' => $antifraudRaw['script'],
                    'sessionKey' => $antifraudRaw['antifraudkey'],
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
            $sessionKey = $request->input('sessionKey');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : 'AFsubmitbtn';

            $response = Http::get($this->config['verify_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'user_ip' => $ip,
                'ua' => $ua,
                'otp' => $pin,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
                'sessionKey' => $sessionKey,
                'ti' => $txid,
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

    private function getAntifraudKeys($request, $txid) {
        $headers = [];

        foreach ($request->headers->all() as $key => $value) {
            $headers[$key] = is_array($value) ? $value[0] : $value;
        }

        $encoded = base64_encode(json_encode($headers));
        $params = [
            'Page'      => 2,
            'ChannelID' => $this->config['ChannelID'],
            'ClickID'   => $txid,
            'Headers'   => $encoded,
            'UserIP'    => base64_encode($request->ip()),
        ];
       
        $response = Http::get($this->config['antifraud_url'], $params);

        if($response->successful()) {
            return [
                'script' => $response->body(),
                'antifraudkey' => $response->header('AntiFrauduniqid'),
                'mcpuniqid' => $response->header('MCPuniqid'),
                'txid' => $txid,
            ];
        }

        return [
            'script' => '',
            'antifraudkey' => '',
            'mcpuniqid' => '',
        ];
    }
}

