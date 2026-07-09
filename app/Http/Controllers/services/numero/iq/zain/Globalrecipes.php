<?php
namespace App\Http\Controllers\services\numero\iq\zain;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Globalrecipes extends Controller
{
    private $config = [
        'send_pin' => 'http://159.89.163.174/prod/IQcmp/sendPIN',
        'verify_pin' => 'http://159.89.163.174/prod/IQcmp/verifyPIN',
        'fraudstop_snip_url' => 'https://uk.api.shield.monitoringservice.co/',
        'fraudstop_url'         => 'https://sg.apiserver.shield.monitoringservice.co',
        'service_name' => 'globalrecipes',
        'unsub_code' => 'G7',
        'sub_code' => '',
        'shortcode' => '3368',
        'pack_validity' => '1',
        'pack_price' => '240',
        'currency' => 'IQD',
        'pin_length' => 5,
        // custom params
        'cid' => '307',
        'service_key' => 'xSf2bJoBY94rSvaeK2i0',
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

            $response = Http::get($this->config['send_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'ip' => $ip,
                'ua' => $ua,
            ]);

            $script = '';
            $antifraudRaw = '';

            if ($response->successful() && $response->json('response') == 'SUCCESS') {
                $result = $this->getFraudScript($this->config['service_key'],$txid);

                return response()->json([
                    'status' => '1',
                    'message' => 'pin sent',
                    'txid' => $txid,
                    'cta_btn' => $cta_btn,
                    'uniqid' => $result['uniqid'],
                    'script' => $result['source'],
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

    private function getFraudScript($service_key,$click_id){
        
        $secreteHeaderParams = array('Upgrade-Insecure-Requests');
        $head = apache_request_headers();
            if(is_array($head) !== false){
                foreach ($secreteHeaderParams as $shp) {
                    if(array_key_exists($shp, $head)){
                        unset($head[$shp]);
                    }
                }
                    $h = urlencode(json_encode($head));
            }
            else{
                $h = "";
            }
            $ctx = stream_context_create(array('http' => array('user_agent' => (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''), 'timeout' => 5)));
            $params = http_build_query(array(
                'lpu' => urlencode((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http')."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),
                'timestamp' => str_replace('.', '', isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true)),
                'user_ip' => $_SERVER['REMOTE_ADDR'],
                'head' => $h
            ));
            $response = json_decode(file_get_contents($this->config['fraudstop_url'].'/'.$service_key.'/'.$click_id.'/JS?'.$params, null, $ctx));
            if(!empty($response)){
                $source = $response->source;
                $uniqid = $response->uniqid; // Unique Key To Use For Block API Call
            }
            else{
                $uniqid = md5($params['user_ip'].'-'.$click_id.'-'.microtime(true)); // Unique Key To Use For Block API Call
                $source = "(function(s, o, u, r, k){
                    b = s.URL;
                    v = (b.substr(b.indexOf(r)).replace(r + '=', '')).toString();
                    r = (v.indexOf('&') !== -1) ? v.split('&')[0] : v;
                    a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.setAttribute('crossorigin', 'anonymous');
                    a.src = u+'script.js?ak='+k+'&lpi='+r+'&lpu='+encodeURIComponent(b)+'&key=$uniqid&_headers=".base64_encode($h)."'';
                    m.parentNode.insertBefore(a, m);
                })(document, 'script', '".$this->config['fraudstop_snip_url']."', 'uniqid', '".$service_key."');";
        }

        return ['uniqid' => $uniqid, 'source' => $source];
    }

    public function pinVerification(Request $request)
    {
        try {
            $msisdn = $request->input('msisdn');
            $pin = $request->input('pin');
            $ip = $request->has('ip') ? $request->input('ip') : $request->ip();

            // These MUST be the same values used in the antifraud request
            $txid = $request->input('txid');
            $ts = $request->input('ts');
            $cta_btn = $request->has('cta_btn') ? $request->input('cta_btn') : '#cta_btn';
            $uniqid = $request->input('uniqid');

            $response = Http::get($this->config['verify_pin'], [
                'cid' => $this->config['cid'],
                'msisdn' => $msisdn,
                'pin' => $pin,
                'uniqid' => $uniqid,
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