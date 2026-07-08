<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $config['service_name'] }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-primary">
    <main class="min-h-screen flex items-center justify-center w-full">
        <div id="app" class="w-full bg-white/90 text-center flex flex-col items-center justify-center max-w-lg mx-auto gap-4 border border-black/10 shadow-lg my-5 p-5 tracking-wide rounded-lg">
            <h2 class="text-2xl font-semibold text-gray-700">{{ $config['service_name'] }}</h2>
            <p class="text-sm text-gray-500">{{ $config['price'] }} {{ $config['currency'] }} / {{ $config['billing_freq'] }}</p>

            <div id="step-msisdn" class="flex flex-col gap-5 w-full my-4">
                <input
                    type="text"
                    id="msisdn"
                    placeholder="Enter your mobile number"
                    maxlength="{{ $config['msisdn_length'] }}"
                    class="bg-white w-full mx-auto p-3 border border-black/40 rounded-sm font-normal text-base text-black"
                >
                <button id="subscribe-btn" onclick="sendPin()" class="mx-auto max-w-sm border-2 bg-amber-500 text-white text-base font-semibold px-5 py-2 rounded-full shadow-lg border-white hover:scale-110 transition-all duration-300">Subscribe</button>
            </div>

            <div id="step-otp" style="display:none;" class="flex flex-col gap-5 w-full my-4">
                <input
                    type="text"
                    id="otp"
                    placeholder="Enter OTP"
                    maxlength="{{ $config['pin_length'] }}"
                    class="bg-white w-full mx-auto p-3 border border-black/40 rounded-sm font-normal text-base text-black"
                >
                <button onclick="verifyPin()" class="mx-auto max-w-sm border-2 bg-amber-500 text-white text-base font-semibold px-5 py-2 rounded-full shadow-lg border-white hover:scale-110 transition-all duration-300">Verify</button>
            </div>

            <p id="alert-msg" style="color:red;"></p>
        </div>

        <input type="hidden" id="txid" value="{{ $txid }}">
        <input type="hidden" id="af-token" value="">
        <input type="hidden" id="verified-msisdn" value="">

        <!--
            TODO: embed Numero's anti-fraud JS snippet here.
            campaign.txt references #token# and #verify_btn_id# (id="subscribe-btn"
            above) but the actual snippet that populates #af-token# wasn't
            provided. Ask Numero for it and drop it in this spot - it should
            set document.getElementById('af-token').value before the user
            taps Subscribe.
        -->

        <script>
        function sendPin() {
            fetch("{{ $action_base }}/pin_request", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    msisdn: document.getElementById('msisdn').value,
                    txid: document.getElementById('txid').value,
                    token: document.getElementById('af-token').value
                })
            })
            .then(r => r.json())
            .then(data => {
                var alertEl = document.getElementById('alert-msg');
                if (data.response.status === '1') {
                    // carry the confirmed msisdn forward as a hidden field
                    // for the verify step, instead of relying on the
                    // (now hidden) visible input still holding its value
                    document.getElementById('verified-msisdn').value =
                        document.getElementById('msisdn').value;
                    document.getElementById('step-msisdn').style.display = 'none';
                    document.getElementById('step-otp').style.display = 'block';
                    alertEl.innerText = '';
                } else {
                    alertEl.innerText = data.response.message;
                }
            });
        }

        function verifyPin() {
            fetch("{{ $action_base }}/pin_verification", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    msisdn: document.getElementById('verified-msisdn').value,
                    otp: document.getElementById('otp').value,
                    txid: document.getElementById('txid').value
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.response.status === '1') {
                    window.location.href = data.response.portal_url || '/';
                } else {
                    document.getElementById('alert-msg').innerText = data.response.message;
                }
            });
        }
        </script>
    </main>
</body>
</html>