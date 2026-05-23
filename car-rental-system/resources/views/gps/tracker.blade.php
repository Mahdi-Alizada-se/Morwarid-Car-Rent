<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>{{ $vehicle->brand }} {{ $vehicle->model }} Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0f172a;
            color: #e2e8f0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .card {
            background: #1e293b;
            border-radius: 20px;
            padding: 28px 24px;
            width: 100%;
            max-width: 360px;
            text-align: center;
        }

        .emoji {
            font-size: 52px;
            margin-bottom: 12px;
        }

        .name {
            font-size: 22px;
            font-weight: 700;
            color: #f1f5f9;
            margin-bottom: 2px;
        }

        .plate {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 24px;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-green {
            background: #22c55e;
            animation: blink 1.4s infinite;
        }

        .dot-yellow {
            background: #f59e0b;
        }

        .dot-red {
            background: #ef4444;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .status-txt {
            font-size: 15px;
            font-weight: 500;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .box {
            background: #0f172a;
            border-radius: 12px;
            padding: 12px 8px;
            text-align: center;
        }

        .box-label {
            font-size: 10px;
            color: #475569;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .box-val {
            font-size: 16px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .last {
            font-size: 12px;
            color: #475569;
            margin-bottom: 12px;
        }

        .notice {
            background: #0f172a;
            border-radius: 10px;
            padding: 12px;
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
        }

        .count {
            font-size: 11px;
            color: #334155;
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div style="display:flex;justify-content:center;margin-bottom:12px;">
            <div class="rounded-xl px-2 py-1" style="background-color: #4F46E5;">
                <img src="{{ asset('images/logo.png') }}" alt="Morwarid Car Rental" class="h-12 w-auto object-contain">
            </div> alt="Morwarid" style="height:60px;width:auto;object-fit:contain;">
        </div>
        <div class="name">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
        <div class="plate">{{ $vehicle->license_plate }} · {{ $vehicle->color }}</div>

        <div class="status-row">
            <div class="dot dot-yellow" id="dot"></div>
            <span class="status-txt" id="statusTxt">Starting GPS...</span>
        </div>

        <div class="grid">
            <div class="box">
                <div class="box-label">Latitude</div>
                <div class="box-val" id="lat">—</div>
            </div>
            <div class="box">
                <div class="box-label">Longitude</div>
                <div class="box-val" id="lng">—</div>
            </div>
            <div class="box">
                <div class="box-label">Speed</div>
                <div class="box-val" id="spd">0 km/h</div>
            </div>
            <div class="box">
                <div class="box-label">Accuracy</div>
                <div class="box-val" id="acc">—</div>
            </div>
        </div>

        <div class="last" id="lastTxt">Last sent: never</div>
        <div class="notice" id="notice">🔒 Keep this page open in Chrome.<br>Screen can dim — GPS keeps running.</div>
        <div class="notice" id="nextUpdateBox" style="margin-top:8px;">
            Next GPS update in: <span id="nextUpdate" style="font-weight:700;color:#94a3b8;">5:00</span>
        </div>
        <div class="count" id="countTxt"></div>
    </div>

    <script>
        const VID = {{ $vehicle->id }};
        const API = '{{ url('/api/v1/gps/update') }}';
        const CSRF = '{{ csrf_token() }}';
        let sent = 0;
        let wakeLock = null;

        const dot = document.getElementById('dot');
        const statusTxt = document.getElementById('statusTxt');

        function setStatus(type, msg) {
            dot.className = 'dot dot-' + type;
            statusTxt.textContent = msg;
        }

        async function keepAwake() {
            try {
                if ('wakeLock' in navigator) {
                    wakeLock = await navigator.wakeLock.request('screen');
                    document.getElementById('notice').textContent =
                        '✅ Screen kept awake. GPS active.';
                }
            } catch (e) {
                document.getElementById('notice').textContent =
                    '⚠️ Go to Settings → Display → keep screen on manually.';
            }
        }

        document.addEventListener('visibilitychange', async () => {
            if (document.visibilityState === 'visible' && !wakeLock) {
                await keepAwake();
            }
        });

        async function send(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            const spd = ((pos.coords.speed || 0) * 3.6).toFixed(1);
            const acc = (pos.coords.accuracy || 0).toFixed(0);

            document.getElementById('lat').textContent = lat.toFixed(5);
            document.getElementById('lng').textContent = lng.toFixed(5);
            document.getElementById('spd').textContent = spd + ' km/h';
            document.getElementById('acc').textContent = acc + 'm';

            setStatus('yellow', 'Sending...');

            try {
                const r = await fetch(API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        vehicle_id: VID,
                        latitude: lat,
                        longitude: lng,
                        speed: parseFloat(spd),
                        heading: pos.coords.heading || 0,
                    })
                });

                if (r.ok) {
                    sent++;
                    setStatus('green', '✅ GPS Active');
                    document.getElementById('lastTxt').textContent =
                        'Last sent: ' + new Date().toLocaleTimeString();
                    document.getElementById('countTxt').textContent =
                        sent + ' location update' + (sent === 1 ? '' : 's') + ' sent';
                } else {
                    setStatus('yellow', '⚠️ Server error — retrying');
                }
            } catch (e) {
                setStatus('red', '❌ No internet — will retry');
            }
        }

        function onError(e) {
            const msgs = {
                1: '❌ Location denied — tap Allow in Chrome',
                2: '⚠️ GPS signal weak — move outside',
                3: '⏳ GPS timeout — retrying...',
            };
            setStatus('red', msgs[e.code] || 'GPS error');
        }

        function start() {
            if (!navigator.geolocation) {
                setStatus('red', 'GPS not available on this device');
                return;
            }

            setStatus('yellow', 'Requesting GPS permission...');

            const opts = {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 0,
            };

            // Watch position for continuous updates
            navigator.geolocation.watchPosition(send, onError, opts);

            // Also send every 15 seconds
            // ─── 5-minute interval ────────────────────────────────────────────────────────
            let nextUpdate = 300;
            const countdownEl = document.getElementById('nextUpdate');

            setInterval(() => {
                navigator.geolocation.getCurrentPosition(send, onError, {
                    enableHighAccuracy: true,
                    timeout: 30000,
                    maximumAge: 10000,
                });
            }, 300000);

            // ─── Countdown timer ──────────────────────────────────────────────────────────
            setInterval(() => {
                nextUpdate--;
                if (nextUpdate <= 0) nextUpdate = 300;
                const m = Math.floor(nextUpdate / 60);
                const s = nextUpdate % 60;
                if (countdownEl) {
                    countdownEl.textContent = m + ':' + s.toString().padStart(2, '0');
                }
            }, 1000);
        }

        keepAwake();
        start();
    </script>
</body>

</html>