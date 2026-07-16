<?php
namespace App\Http\Controllers\services\eighteenvirgo\jovial\iq\korek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gamingbox extends Controller
{
    private $config = [
        'send_pin' => 'http://13.232.36.171/app/sendotp.php',
        'verify_pin' => 'http://13.232.36.171/app/verifyotp.php',
        'antifraud_url' => 'http://3.6.216.218/gamingbox/evina_Afscript_gbox.php',
        'service_name' => 'gamingbox',
        'unsub_code' => '031',
        'sub_code' => '31',
        'shortcode' => '3999',
        'pack_validity' => '1',
        'pack_price' => '240',
        'currency' => 'IQD',
        'pin_length' => 4,

        // custom params
        'cid' => '300',
        'offerkey' => 'rra39wQUjy',
        'offerid' => '112244',
        'sender_id' => '3999',
    ];


    public function index(Request $request)
    {
        return response()->json(['message' => 'Welcome to Gamifya API']);
    }

    public function pinRequest(Request $request)
    {
        try {

            $msisdn = $request->input('msisdn');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $txid = $request->has('txid') ? $request->input('txid') : uniqid();

            $response = Http::get($this->config['send_pin'], [
                'offerkey' => $this->config['offerkey'],
                'offerid' => $this->config['offerid'],
                'msisdn' => $msisdn,
            ]);

            $script = '';
            $antifraudRaw = '';
            $ti = '';
            $ts = '';

            if ($response->successful() && $response->json('response') == 'SUCCESS') {

                try {
                    $antifraud = Http::get($this->config['antifraud_url']);

                    if ($antifraud->successful()) {
                        $antifraudRaw = $antifraud->body();
                        $data = $antifraud->json('data');
                        $ti = $data['ti'] ?? '';
                        $ts = $data['ts'] ?? '';
                        $script = $data['header'] ?? '';
                    }

                } catch (\Throwable $e) {
                    $antifraudRaw = $e->getMessage();
                }

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'script' => $script,
                    'ti' => $ti,
                    'ts' => $ts,
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

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $ti = $request->input('ti');
            $ts = $request->input('ts');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';

            $response = Http::get($this->config['verify_pin'], [
                'offerkey' => $this->config['offerkey'],
                'offerid' => $this->config['offerid'],
                'msisdn' => $msisdn,
                'otp' => $pin,
                'ti' => $ti,
                'ts' => $ts,
            ]);

            if ($response->successful() && $response->json('response') == 'SUCCESS') {

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