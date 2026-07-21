<?php
namespace App\Http\Controllers\services\cryptmedia\numero\iq\korek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gameclub extends Controller
{
    private $config = [
        'send_pin' => 'http://159.89.163.174/panel/sendpin',
        'verify_pin' => 'http://159.89.163.174/panel/verifypin',
        'antifraud_url' => 'https://korek-he.trendy-technologies.com/dcbprotect.php',
        'service_name' => 'gameclub',
        'unsub_code' => '0',
        'sub_code' => '1',
        'shortcode' => '3841',
        'pack_validity' => '1',
        'pack_price' => '',
        'currency' => 'IQD',
        'pin_length' => 4,
        // custom params
        'cid' => '2662',
        'pub_id' => 'PUB6792',
        'sub_pub_id' => '22045/PUB6787',
        'merchantname' => 'Takarub',
        'servicename' => 'KurdTube',
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
            $ts = time();

            $response = Http::get($this->config['send_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'user_ip' => $ip,
                'ua' => $ua,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
            ]);

            $script = '';
            $antifraudRaw = '';
            $uniqid = '';

            if ($response->successful() && $response->json('status') == true) {
                $antifraudRaw = Http::withoutVerifying()->get($this->config['antifraud_url'], [
                    'action' => 'script',
                    'ti' => $txid,
                    'ts' => $ts,
                    'te' => $cta_btn,
                    'servicename' => $this->config['servicename'],
                    'merchantname' => $this->config['merchantname'],
                    'type' => 'pin',
                ]);

                $script = $antifraudRaw->json('s');
                $uniqid = $antifraudRaw->json('t');

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'uniqid' => $uniqid,
                    'script' => $script,
                    'raw' => [
                        'pin_request' => $response->body(),
                        'antifraud' => $antifraudRaw->body(),
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
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();
            $ua = $request->has('ua') ? $request->input('ua') : $request->userAgent();

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $uniqid = $request->has('uniqid') ? $request->input('uniqid') : '';

            $response = Http::get($this->config['verify_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'user_ip' => $ip,
                'ua' => $ua,
                'otp' => $pin,
                'pub_id' => $this->config['pub_id'],
                'sub_pub_id' => $this->config['sub_pub_id'],
                'sessionKey' => $uniqid,
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