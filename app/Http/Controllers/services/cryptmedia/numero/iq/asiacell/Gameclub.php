<?php
namespace App\Http\Controllers\services\cryptmedia\numero\iq\asiacell;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gameclub extends Controller
{
    private $config = [
        'send_pin' => 'http://159.89.163.174/panel/sendpin',
        'verify_pin' => 'http://159.89.163.174/panel/verifypin',
        'antifraud_url' => 'https://sg.apiserver.shield.monitoringservice.co/scs2gJkB-W5fcuufPM1_/JS',
        'service_name' => 'gameclub',
        'unsub_code' => '0',
        'sub_code' => 'GC',
        'shortcode' => '4014',
        'pack_validity' => '1',
        'pack_price' => '300',
        'currency' => 'IQD',
        'pin_length' => 4,
        // custom params
        'cid' => '2663',
        'pub_id' => 'PUB8199',
        'sub_pub_id' => '56045/PUB4697',
        'channel_id' => '22728',
    ];


    public function index(Request $request)
    {
        $context = $request->attributes->get('route_context');

        $page = 1;
        $txid = uniqid();

        $antifraudraw = $this->getAntiFraudScript($request, $page, $txid);

        return view(
            "services.{$context['company']}.{$context['partner']}.{$context['country']}.{$context['operator']}.{$context['offer_name']}.index",
            [
                'config' => $this->config,
                'context' => $context,
                'script' => $antifraudraw['script'],
                'antifraudkey' => $antifraudraw['antifraudkey'],
                'mcpuniqid' => $antifraudraw['mcpuniqid'],
                'txid' => $txid,
            ]
        );
    }

    public function pinRequest(Request $request)
    {
        try {

            $msisdn = $request->input('msisdn');
            $antifraudkey = $request->input('antifraudkey');
            $mcpuniqid = $request->input('mcpuniqid');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : 'AFsubmitbtn';
            $txid = $request->input('txid');
            $ts = time();
            $page = 2;

            $antifraudraw = $this->getAntiFraudScript($request, $page, $txid);

            $response = Http::get($this->config['send_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'user_ip' => $ip,
                'ua' => $ua,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
                'sessionKey' => $antifraudkey,
                'fraudCheckToken' => $mcpuniqid,
                'timestamp' => $ts,
            ]);

            if ($response->successful() && $response->json('status') == true) {
                
                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'script' => $antifraudraw['script'],
                    'antifraudkey' => $antifraudraw['antifraudkey'],
                    'mcpuniqid' => $antifraudraw['mcpuniqid'],
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
            $antifraudkey = $request->input('antifraudkey');
            $mcpuniqid = $request->input('mcpuniqid');
            $pin = $request->input('pin');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $ts = time();

            $response = Http::get($this->config['verify_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'user_ip' => $ip,
                'ua' => $ua,
                'otp' => $pin,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
                'sessionKey' => $antifraudkey,
                'fraudCheckToken' => $mcpuniqid,
                'timestamp' => $ts,
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

    private function getAntiFraudScript($request, $page, $txid) {
        $response = Http::get($this->config['antifraud_url'], [
            'Page' => $page,
            'ChannelID' => $this->config['channel_id'],
            'ClickID' => $txid,
            'Headers' => base64_encode(json_encode($request->headers->all())),
            'UserIP' => base64_encode($request->ip()),
        ]);

        if($response->successful()) {
            return [
                'script' => $response->body(),
                'antifraudkey' => $response->header('AntiFrauduniqid'),
                'mcpuniqid' => $response->header('MCPuniqid'),
            ];
        }

        return [
            'script' => '',
            'antifraudkey' => '',
            'mcpuniqid' => '',
        ];
    }

}

