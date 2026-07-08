<?php
namespace App\Http\Controllers\services\numero\iq\korek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gamifya  extends Controller
{
    private $config = [
        'send_pin'      => 'http://143.198.213.74/prod/IQKGcmp/sendPIN',
        'verify_pin'    => 'http://143.198.213.74/prod/IQKGcmp/verifyPIN',
        'antifraud_url' => 'https://korek-he.trendy-technologies.com/dcbprotect.php',
        'service_name'  => 'gamifya',
        'unsub_code'    => '01',
        'sub_code'      => '',
        'shortcode'     => '2015',
        'pack_validity' => '1',
        'pack_price'    => '240',
        'currency'      => 'IQD',
        'pin_length'    => 4,
        
        // custom params
        'cid'          => '172',
    ];

    
    public function index(Request $request)
    {
        return response()->json(['message' => 'Welcome to Gamifya API']);
    }

    public function pinRequest(Request $request)
    {
        $msisdn     = $request->input('msisdn');
        $ip         = $request->has('ip') ? $request->input('ip') : $request->ip();
        $ua         = $request->has('ua')  ? $request->input('ua') : $request->userAgent();
        $cta_btn    = $request->input('cta_btn');
        $txid       = $request->input('txid');
        
       //dd($request);
        $response = Http::get($this->config['send_pin'],[
            'cid'       => '172',
            'msisdn'    => $msisdn,
            'ip'        => $ip,
            'ua'        => $ua,
        ]);

        if($response->successful() && $response->json('response') == 'SUCCESS'){
            
            
            // antifraud url
            $antifraud_rsvp = Http::get($this->config['antifraud_url'],[
                'action'        => 'script',
                'ti'            => $txid,
                'ts'            => time(),
                'te'            => $cta_btn,
                'servicename'   => 'Gamifya',
                'merchantname'  => 'NserveTech',
                'type'          => 'otp'
            ]);

            //dd($antifraud_rsvp->body());

            if($antifraud_rsvp->successful()){
                $script = $antifraud_rsvp->json('s');
            }
            else{
                $script = '';
            }

            return response()->json([
                'status'    => '1',
                'message'   => 'pin sent',
                'script'    => $script,
                'raw'       => $response->body()
            ]);
        }
        else{
            return response()->json([
                'status'    => '0',
                'message'   => 'pin failed',
                'script'    => '',
                'raw'       => $response->body()
            ]);
        }

        return response()->json($response->json());
    }

    public function pinVerification(Request $request)
    {
        $msisdn = $request->input('msisdn');
        $pin = $request->input('pin');
        $url = str_replace(['{msisdn}', '{pin}'], [$msisdn, $pin], $this->config['pin_verification']);

        $response = Http::get($url);
        return response()->json($response->json());
    }

}