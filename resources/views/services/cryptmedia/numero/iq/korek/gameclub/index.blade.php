<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gameclub Subscription</title>

    <style>
        * {
            box-sizing: border-box;
        }

        :root {
            --bg: #d9f0fb;
            --text: #262626;
            --muted: #6a6a6a;
            --blue: #2ebddd;
            --blue-dark: #1f9fc0;
            --orange: #ff6500;
            --border: #d9d9d9;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #fff;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .wrapper {
            min-height: 100vh;
            padding: 0;
        }

        .container {
            width: min(calc(100% - 48px), 1500px);
            max-width: 100%;
            margin: 0 auto;
            min-height: 100vh;
            background: var(--bg);
        }

        .header_new {
            position: relative;
            padding: 10px 24px 0;
            text-align: center;
        }

        .price-strip {
            margin: 0;
            color: #666;
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .lang-wrap {
            position: absolute;
            top: 46px;
            right: 22px;
        }

        .lang-shell {
            position: relative;
            min-width: 168px;
        }

        .lang-select {
            width: 100%;
            border: 0;
            background: transparent;
            color: #6b6b6b;
            font-size: 1.05rem;
            font-weight: 700;
            appearance: none;
            outline: none;
            text-align: left;
            padding-right: 26px;
            cursor: pointer;
        }

        .lang-shell::after {
            content: "";
            position: absolute;
            right: 2px;
            top: 50%;
            width: 8px;
            height: 8px;
            border-right: 2px solid #7a7a7a;
            border-bottom: 2px solid #7a7a7a;
            transform: translateY(-70%) rotate(45deg);
            pointer-events: none;
        }

        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            padding: 18px 20px 0;
        }

        .hero-banner {
            width: min(100%, 660px);
            margin: 0 auto;
        }

        .hero-banner img {
            display: block;
            width: 100%;
            height: 300px;
            border: 0;
            border-radius: 15px;
        }

        .hero-copy {
            text-align: center;
            margin-top: 0;
        }

        .hero-title {
            margin: 0;
            color: #151515;
            font-size: 1.8rem;
            line-height: 1.1;
            font-weight: 500;
        }

        .hero-subtitle {
            margin: 4px 0 0;
            color: #171717;
            font-size: 1.05rem;
            line-height: 1.2;
            font-weight: 400;
        }

        .content {
            width: min(100%, 980px);
            margin: 0 auto;
            padding: 48px 24px 20px;
        }

        .orange {
            margin: 0 0 24px;
            color: var(--orange);
            text-align: center;
            font-size: clamp(1.6rem, 2.8vw, 3.2rem);
            line-height: 1.1;
            font-weight: 400;
        }

        .phone-control {
            display: flex;
            align-items: center;
            gap: 10px;
            width: min(100%, 980px);
            margin: 0 auto;
            padding: 18px 0 12px;
            border-bottom: 1px solid var(--border);
            justify-content: center;
            flex-wrap: nowrap;
        }

        .flag {
            display: inline-flex;
            width: 96px;
            height: 78px;
            overflow: hidden;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.12);
            flex: 0 0 auto;
        }

        .flag span {
            display: block;
            width: 100%;
            height: 100%;
        }

        .flag .red {
            width: 24px;
            background: #ed184f;
        }

        .flag .stack {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .flag .green {
            flex: 1;
            background: #007b45;
        }

        .flag .white {
            flex: 1;
            background: #ffffff;
        }

        .flag .black {
            flex: 1;
            background: #000000;
        }

        .tel {
            flex: 1 1 auto;
            min-width: 0;
            width: auto;
            border: 0;
            outline: none;
            background: transparent;
            color: #777;
            font-size: clamp(1.9rem, 4vw, 3.2rem);
            font-weight: 700;
            letter-spacing: 0;
            padding: 0;
        }

        .tel::placeholder {
            color: #8d8d8d;
        }

        .form-submit {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 48px 0 0;
            text-align: center;
        }

        .btn_t {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: min(100%, 820px);
            min-height: 184px;
            padding: 24px 28px;
            border: 0;
            border-radius: 22px;
            cursor: pointer;
            font-size: clamp(2rem, 3.6vw, 3.7rem);
            font-weight: 500;
            letter-spacing: 0;
            text-transform: uppercase;
            color: #fff;
            background: linear-gradient(180deg, #42bed8 0%, #35b0cf 100%);
            box-shadow: 0 14px 28px rgba(27, 108, 134, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.25);
            transition: transform 0.15s ease, filter 0.15s ease;
        }

        .btn_t:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
        }

        .text_after_btn {
            margin: 0;
            color: #7a7a7a;
            font-size: 0.88rem;
            line-height: 1.35;
            font-weight: 400;
            text-align: center;
        }

        .bl_txt_btn_exit {
            margin-top: 8px;
        }

        .bl_txt_btn_exit a {
            display: inline-block;
            color: #2ebddd;
            font-size: 1.05rem;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .terms {
            width: min(100%, 980px);
            margin: 24px auto 0;
            padding: 0 0 24px;
            text-align: center;
        }

        .terms p {
            margin: 0;
            color: #111;
            font-size: 0.92rem;
            line-height: 1.42;
            text-align: center;
        }

        .terms_heading {
            font-weight: 700;
        }

        .terms a {
            color: #2a6fbb;
            text-decoration: underline;
        }

        .otp-section {
            display: none;
            text-align: center;
        }
        .enter-otp {
            border: 2px solid rgba(0, 0, 0, 0.3);
            padding: 5px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .result {
            margin-top: 15px;
        }

        .message {
            margin: 0 0 16px;
            padding: 11px 12px;
            border-radius: 8px;
            font-size: 25px;
            line-height: 1.45;
            text-align: center;
            text-transform: capitalize;
        }

        .message .error {
            background: #fff0f0;
            color: #a31318;
        }

        .message .success {
            background: #edf9f1;
            color: #166534;
        }

        .loader {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.72);
            z-index: 20;
        }

        .loader .inner {
            text-align: center;
            color: #3b3f4a;
            font-weight: 700;
        }

        .loader .spinner {
            width: 52px;
            height: 52px;
            margin: 0 auto 10px;
            border: 5px solid rgba(46, 189, 221, 0.18);
            border-top-color: var(--blue);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        [data-lang="ar"] {
            display: none;
        }

        .is-ar [data-lang="en"] {
            display: none;
        }

        .is-ar [data-lang="ar"] {
            display: inline;
        }

        .is-ar p[data-lang="ar"],
        .is-ar div[data-lang="ar"],
        .is-ar li[data-lang="ar"] {
            display: block;
        }

        .is-ar .phone-control,
        .is-ar .orange,
        .is-ar .terms {
            direction: rtl;
        }

        .is-ar .lang-wrap {
            left: 22px;
            right: auto;
        }

        .is-ar .lang-shell::after {
            left: 2px;
            right: auto;
        }

        .is-ar .phone-control {
            flex-direction: row-reverse;
        }

        .is-ar .tel {
            text-align: right;
        }

        @media (max-width: 900px) {
            .btn_t {
                min-height: 130px;
            }
        }

        @media (max-width: 640px) {
            .container {
                width: calc(100% - 16px);
            }

            .header_new {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                padding: 12px 16px 18px;
            }

            .lang-wrap {
                position: static;
                align-self: flex-end;
                margin-top: 8px;
                margin-bottom: 6px;
            }

            .is-ar .lang-wrap {
                align-self: flex-start;
            }

            .price-strip {
                max-width: 100%;
                margin: 0 auto;
                font-size: 0.92rem;
                line-height: 1.22;
            }

            .content {
                padding-left: 16px;
                padding-right: 16px;
                padding-top: 36px;
            }

            .hero {
                padding: 0;
                padding-top: 8px;
            }

            .hero .hero-banner img {
                height: 160px;
            }

            .orange {
                font-size: 1.6rem;
            }

            .phone-control {
                gap: 8px;
                padding-top: 14px;
                padding-bottom: 10px;
            }

            .flag {
                width: 68px;
                height: 54px;
            }

            .phone-control > span:not(.flag) {
                font-size: 1.4rem !important;
                white-space: nowrap;
            }

            .tel {
                font-size: 1.35rem;
                line-height: 1.15;
            }

            .btn_t {
                min-height: 112px;
                border-radius: 18px;
            }
        }

        @media (max-width: 420px) {
            .header_new {
                padding-top: 8px;
            }

            .price-strip {
                font-size: 0.84rem;
            }

            .lang-shell {
                min-width: 132px;
            }

            .lang-select {
                font-size: 0.88rem;
            }

            .hero {
                padding-top: 14px;
            }

            .hero-title {
                font-size: 1.55rem;
            }

            .hero-subtitle {
                font-size: 0.95rem;
            }

            .orange {
                font-size: 1.35rem;
            }

            .phone-control {
                gap: 6px;
            }

            .tel {
                font-size: 1.22rem;
                text-align: left;
            }

            .btn_t {
                min-height: 96px;
                font-size: 1.8rem;
            }

            .text_after_btn {
                font-size: 0.82rem;
            }

            .terms p {
                font-size: 0.86rem;
            }
        }
    </style>
</head>
<body>
    <div class="loader" id="loader">
        <div class="inner">
            <div class="spinner"></div>
            <div data-lang="en">Loading ...</div>
        </div>
    </div>
    <div class="wrapper">
        <div class="container" id="container">
            <div class="header_new">
                <div class="hero">
                    <div class="hero-banner">
                        <img src="http://159.89.163.174/panel/assets/images/RnSLchpZUU.jpeg" alt="Gameclub banner">
                    </div>
                    <div class="hero-copy">
                        <p class="hero-title">
                            <span data-lang="en">Gameclub</span>
                        </p>
                        <p class="hero-subtitle">
                            <span data-lang="en">Unique collection of interactive games </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="content blok1">
                <form id="fnum" action="" method="post">
                    <p class="orange">
                        <span data-lang="en">Enter your mobile number to receive the OTP</span>
                    </p>
                    <div class="phone-control" id="phone-control">
                        <span class="flag" aria-hidden="true">
                            <span class="red"></span>
                            <span class="stack">
                                <span class="green"></span>
                                <span class="white"></span>
                                <span class="black"></span>
                            </span>
                        </span>
                        <span style="color:#777;font-size: clamp(1.9rem, 4vw, 3.2rem);font-weight:700;">+964</span>
                        <input
                            type="tel"
                            inputmode="tel"
                            placeholder="XXXXXXXXXX"
                            name="msisdn"
                            value=""
                            maxlength="10"
                            class="tel form-control"
                            id="tel"
                            required
                        >
                    </div>
                    <div class="form-submit">
                        <button type="button" id="btn-st1" class="btn_t btn_t-blue">
                            <span data-lang="en">Subscribe</span>
                        </button>
                        </div>
                    </div>
                </form>
                <div id="otp-section" class="otp-section">
                    <input type="hidden" id="txid" name="txid">
                    <input type="hidden" id="uniqid" name="uniqid">
                    <input type="hidden" id="cta_btn" name="cta_btn">
                    <input
                        type="text"
                        id="otp"
                        placeholder="Enter OTP" 
                        class="tel form-control enter-otp"
                    />
                    <div class="form-submit">
                        <button id="verifyBtn" class="btn_t btn_t-blue">
                            Verify
                        </button>
                    </div>
                </div>
                <div class="result">
                    <p id="message" class="message"></p>
                </div>
            </div>
        </div>
    </div>
    <script>
        let txid = '';
        let cta_btn = '';

        const loader = document.getElementById('loader');
        const form = document.getElementById('fnum');
        const phoneInput = document.getElementById('tel');
        const sendPinButton = document.getElementById('btn-st1');
        const verifyBtn = document.getElementById('verifyBtn');
        const baseUrl = window.location.pathname;

        if (phoneInput && sendPinButton) {
            sendPinButton.addEventListener('click', async () => {
                const t = phoneInput.value.trim();
                const valid = /^(0[0-9]{9}|[0-9]{10})$/.test(t);

                if (!valid) {
                    phoneInput.focus();
                    document.getElementById('phone-control').style.borderColor = '#ff0000';
                    return;
                }

                document.getElementById('phone-control').style.borderColor = '#cccccc';
                sendPinButton.disabled = true;
                loader.style.display = 'flex';

                const response = await fetch(baseUrl + '/pin_request',{
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN':'{{ csrf_token() }}'
                    },
                    body:JSON.stringify({
                        msisdn:"964" + t,
                    })
                });

                const result = await response.json();

                if(result.status == '1'){
                    loader.style.display = 'none';
                    txid = result.txid;
                    cta_btn = result.cta_btn;
                    uniqid = result.uniqid;

                    if (result.script) {
                        const script = document.createElement('script');
                        script.type = 'text/javascript';
                        script.textContent = result.script;
                        document.head.appendChild(script);
                    }

                    document.getElementById('otp-section').style.display='block';
                    document.getElementById('fnum').style.display='none';
                    document.getElementById('txid').value = result.txid;
                    document.getElementById('uniqid').value = result.uniqid;
                    document.getElementById('cta_btn').value = result.cta_btn;
                    sendPinButton.disabled = true;
                    document.getElementById('message').innerHTML = "<span class='success'>"+ result.message +"</span>";
                }
                if(result.status == '0' ) {
                    loader.style.display = 'none';
                    sendPinButton.disabled = false;
                    document.getElementById('message').innerHTML = "<span class='error'>"+ result.message +"</span>";
                }
            });

            window.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendPinButton.click();
                }
            });

            verifyBtn.addEventListener('click', async () => {
                document.getElementById('message').innerHTML='';

                const msisdn = document.getElementById('tel').value;
                const otp = document.getElementById('otp').value;
                txid = document.getElementById('txid').value;
                cta_btn = document.getElementById('cta_btn').value;
                uniqid = document.getElementById('uniqid').value;
                
                if(otp==''){
                    alert('Enter OTP');
                    return;
                }

                loader.style.display = 'flex';

                const response=await fetch(baseUrl + '/pin_verification',{

                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN':'{{ csrf_token() }}'
                    },

                    body:JSON.stringify({
                        msisdn : "964" + msisdn,
                        pin : otp,
                        txid : txid,
                        cta_btn : cta_btn,
                        uniqid: uniqid,
                    })
                });

                const result = await response.json();

                if(result.status == '1') {
                    document.getElementById('otp').value = "";
                    loader.style.display = 'none';
                    document.getElementById('message').innerHTML = "<span class='success'>"+ result.message +"</span>";
                }
                if(result.status == '0' ) {
                    loader.style.display = 'none';
                    document.getElementById('message').innerHTML = "<span class='error'>"+ result.message +"</span>";
                }
                console.log(result);
            });
        }
    </script>
</body>
</html>