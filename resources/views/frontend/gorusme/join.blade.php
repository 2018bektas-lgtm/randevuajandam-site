<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#0b1220">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ $csrf ?? csrf_token() }}">
    <meta http-equiv="Permissions-Policy" content="camera=(self), microphone=(self)">
    <title>Online görüşme — Randevu Ajandam</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0; height: 100%;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #0a0f1a; color: #f8fafc;
            overflow: hidden;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }

        /* ——— LOBBY (katılmadan önce) ——— */
        .lobby {
            min-height: 100%; min-height: 100dvh;
            display: flex; flex-direction: column;
            justify-content: center; padding: 24px 20px;
            padding-bottom: max(24px, env(safe-area-inset-bottom));
            max-width: 480px; margin: 0 auto;
        }
        .lobby-badge {
            display: inline-block; font-size: 10px; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase;
            color: #6ee7b7; background: rgba(16,185,129,.12);
            border: 1px solid rgba(16,185,129,.28); border-radius: 999px;
            padding: 6px 12px; margin-bottom: 16px;
        }
        .lobby h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 8px; line-height: 1.25; }
        .lobby .meta { font-size: 14px; color: #94a3b8; margin: 0 0 20px; }
        .lobby .hint {
            font-size: 12px; line-height: 1.5; color: #bae6fd;
            background: rgba(14,165,233,.1); border: 1px solid rgba(14,165,233,.25);
            border-radius: 14px; padding: 12px 14px; margin-bottom: 20px;
        }
        .lobby .hint strong { color: #e0f2fe; }
        .btn-join {
            width: 100%; border: 0; border-radius: 16px; cursor: pointer;
            background: linear-gradient(180deg, #d4783a, #C96A2B);
            color: #fff; font-size: 16px; font-weight: 800;
            padding: 16px 20px;
            box-shadow: 0 8px 28px rgba(201,106,43,.35);
        }
        .btn-join:disabled { opacity: .55; cursor: not-allowed; }
        .btn-join:active:not(:disabled) { transform: scale(.98); }
        .status-lobby {
            margin-top: 14px; font-size: 13px; color: #cbd5e1; text-align: center; min-height: 1.4em;
        }
        .status-lobby.err { color: #fca5a5; }
        .wait-box {
            border: 1px solid rgba(245,158,11,.35); background: rgba(245,158,11,.1);
            border-radius: 16px; padding: 20px; color: #fef3c7;
        }

        /* ——— CALL STAGE (mobil pro) ——— */
        .call {
            display: none;
            position: fixed; inset: 0;
            background: #000;
            z-index: 50;
        }
        .call.active { display: block; }

        #remoteVideo {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover; background: #0a0f1a;
        }
        .remote-placeholder {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 10px; color: #94a3b8; font-size: 14px;
            background: radial-gradient(ellipse at center, #13203a 0%, #0a0f1a 70%);
            pointer-events: none;
        }
        .remote-placeholder .dot {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(201,106,43,.15); border: 2px solid rgba(201,106,43,.35);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
        }

        /* PiP — kendi görüntümüz sağ alt */
        .pip {
            position: absolute;
            right: max(12px, env(safe-area-inset-right));
            bottom: max(100px, calc(88px + env(safe-area-inset-bottom)));
            width: min(32vw, 128px);
            aspect-ratio: 3/4;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,.22);
            box-shadow: 0 8px 28px rgba(0,0,0,.45);
            background: #111;
            z-index: 60;
            transition: transform .2s ease, opacity .2s ease;
        }
        .pip video {
            width: 100%; height: 100%; object-fit: cover;
            transform: scaleX(-1); /* ayna */
            background: #000;
        }
        .pip .pip-label {
            position: absolute; left: 6px; top: 6px;
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            background: rgba(0,0,0,.5); padding: 2px 6px; border-radius: 6px;
        }

        /* Üst bar — dokununca */
        .chrome-top, .chrome-bottom {
            position: absolute; left: 0; right: 0; z-index: 70;
            opacity: 0; pointer-events: none;
            transition: opacity .25s ease, transform .25s ease;
        }
        .chrome-top {
            top: 0;
            padding: max(12px, env(safe-area-inset-top)) 16px 12px;
            background: linear-gradient(to bottom, rgba(0,0,0,.72), transparent);
            transform: translateY(-8px);
        }
        .chrome-bottom {
            bottom: 0;
            padding: 16px 16px max(20px, env(safe-area-inset-bottom));
            background: linear-gradient(to top, rgba(0,0,0,.78), transparent);
            transform: translateY(8px);
            display: flex; flex-direction: column; align-items: center; gap: 12px;
        }
        .call.chrome-on .chrome-top,
        .call.chrome-on .chrome-bottom {
            opacity: 1; pointer-events: auto;
            transform: translateY(0);
        }
        .call.chrome-on .pip {
            bottom: max(148px, calc(136px + env(safe-area-inset-bottom)));
        }

        .chrome-top .title { font-size: 15px; font-weight: 700; margin: 0; }
        .chrome-top .sub { font-size: 12px; color: #cbd5e1; margin: 2px 0 0; }
        .chrome-top .live {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            color: #6ee7b7; margin-top: 6px;
        }
        .chrome-top .live i {
            width: 7px; height: 7px; border-radius: 50%; background: #34d399;
            box-shadow: 0 0 0 0 rgba(52,211,153,.6);
            animation: blink 1.4s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: .35; }
        }

        .call-status {
            font-size: 12px; color: #e2e8f0; text-align: center;
            max-width: 90%; text-shadow: 0 1px 4px rgba(0,0,0,.6);
        }

        .ctrl-row {
            display: flex; align-items: center; justify-content: center; gap: 18px;
        }
        .ctrl {
            width: 54px; height: 54px; border-radius: 50%; border: 0; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.14);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            color: #fff; font-size: 20px;
            transition: transform .15s, background .15s;
        }
        .ctrl:active { transform: scale(.92); }
        .ctrl.off { background: rgba(255,255,255,.9); color: #0f172a; }
        .ctrl.hangup {
            width: 64px; height: 64px;
            background: #ef4444;
            box-shadow: 0 6px 20px rgba(239,68,68,.45);
            font-size: 22px;
        }
        .ctrl.hangup:active { background: #dc2626; }

        .tap-hint {
            position: absolute; left: 50%; top: 18%;
            transform: translateX(-50%);
            font-size: 11px; color: rgba(255,255,255,.55);
            z-index: 55; pointer-events: none;
            opacity: 0; transition: opacity .3s;
        }
        .call.chrome-on .tap-hint { opacity: 0; }
        .call:not(.chrome-on) .tap-hint.show { opacity: 1; }

        /* Masaüstü: biraz nefes */
        @media (min-width: 900px) {
            .pip {
                width: 180px;
                right: 24px;
                bottom: 120px;
            }
            .call.chrome-on .pip { bottom: 140px; }
        }

        #debug {
            display: none; position: fixed; left: 8px; right: 8px; bottom: 8px;
            z-index: 100; max-height: 25vh; overflow: auto;
            font-size: 10px; color: #fca5a5; background: rgba(0,0,0,.85);
            border: 1px solid rgba(239,68,68,.3); border-radius: 10px; padding: 8px;
            white-space: pre-wrap;
        }
        #debug.show { display: block; }
    </style>
</head>
<body>
@if(! $canJoin)
    <div class="lobby">
        <div class="wait-box">
            <strong>Görüşme odası henüz açık değil</strong>
            @if($window)
                <p style="margin:10px 0 0;font-size:13px;opacity:.9">
                    {{ $window[0]->format('d.m.Y H:i') }} – {{ $window[1]->format('d.m.Y H:i') }}
                </p>
            @endif
        </div>
    </div>
@elseif(!empty($isPlatform))
    {{-- Lobby --}}
    <div class="lobby" id="lobby">
        <span class="lobby-badge">{{ $role === 'hekim' ? 'Hekim' : 'Hasta' }}</span>
        <h1>{{ trim(($randevu->doktor->unvan ?? '').' '.($randevu->doktor->ad_soyad ?? 'Hekim')) }}</h1>
        <p class="meta">
            {{ $randevu->tarih?->format('d.m.Y') }} · {{ substr((string) $randevu->saat, 0, 5) }}
            @if($randevu->hizmet) · {{ $randevu->hizmet->ad }} @endif
            · {{ $displayName }}
        </p>
        <div class="hint">
            <strong>Hesap / Zoom / Jitsi yok.</strong><br>
            Butona bas → kamera izni ver → karşı taraf da katılsın.
            Görüşmede ekrana dokunarak Bitir ve kontrolleri açarsın.
        </div>
        <button type="button" class="btn-join" id="btnStart">Kamerayı aç ve katıl</button>
        <p class="status-lobby" id="statusLobby">Hazır</p>
        <p id="envHint" style="font-size:11px;color:#64748b;text-align:center;margin-top:8px"></p>
    </div>

    {{-- Fullscreen call --}}
    <div class="call" id="callStage" aria-hidden="true">
        <video id="remoteVideo" autoplay playsinline></video>
        <div class="remote-placeholder" id="remotePlaceholder">
            <div class="dot">👤</div>
            <span>Karşı taraf bekleniyor…</span>
        </div>

        <div class="pip" id="pip">
            <span class="pip-label">Siz</span>
            <video id="localVideo" autoplay playsinline muted></video>
        </div>

        <div class="tap-hint" id="tapHint">Ekrana dokun — kontroller</div>

        <div class="chrome-top">
            <p class="title" id="callTitle">{{ trim(($randevu->doktor->unvan ?? '').' '.($randevu->doktor->ad_soyad ?? 'Görüşme')) }}</p>
            <p class="sub" id="callSub">{{ $role === 'hekim' ? 'Hekim görünümü' : 'Hasta görünümü' }} · {{ $displayName }}</p>
            <div class="live" id="liveBadge" style="display:none"><i></i> Canlı</div>
        </div>

        <div class="chrome-bottom">
            <p class="call-status" id="statusCall">Bağlanıyor…</p>
            <div class="ctrl-row">
                <button type="button" class="ctrl" id="btnMute" title="Mikrofon" aria-label="Mikrofon">🎤</button>
                <button type="button" class="ctrl hangup" id="btnHangup" title="Bitir" aria-label="Görüşmeyi bitir">📞</button>
                <button type="button" class="ctrl" id="btnCam" title="Kamera" aria-label="Kamera">📷</button>
            </div>
            <button type="button" class="ctrl" id="btnUnmuteRemote" style="display:none;width:auto;padding:0 16px;border-radius:999px;font-size:12px;font-weight:700;height:40px">
                🔊 Sesi aç
            </button>
        </div>
    </div>
    <pre id="debug"></pre>
@else
    <div class="lobby"><p>Görüşme yapılandırması eksik.</p></div>
@endif

@if(!empty($canJoin) && !empty($isPlatform))
<script src="https://unpkg.com/peerjs@1.5.4/dist/peerjs.min.js" defer></script>
<script>
(function () {
    'use strict';

    var role = @json($role);
    var displayName = @json($displayName);
    var roomName = @json($roomName);
    var iceServers = @json($iceServers);
    var peerCfg = @json(config('gorusme.peerjs', []));
    var signalUrl = @json($signalUrl);
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    var lobby = document.getElementById('lobby');
    var callStage = document.getElementById('callStage');
    var statusLobby = document.getElementById('statusLobby');
    var statusCall = document.getElementById('statusCall');
    var debugEl = document.getElementById('debug');
    var envHint = document.getElementById('envHint');
    var localVideo = document.getElementById('localVideo');
    var remoteVideo = document.getElementById('remoteVideo');
    var remotePlaceholder = document.getElementById('remotePlaceholder');
    var liveBadge = document.getElementById('liveBadge');
    var tapHint = document.getElementById('tapHint');
    var btnStart = document.getElementById('btnStart');
    var btnMute = document.getElementById('btnMute');
    var btnCam = document.getElementById('btnCam');
    var btnHangup = document.getElementById('btnHangup');
    var btnUnmuteRemote = document.getElementById('btnUnmuteRemote');

    var localStream = null;
    var peer = null;
    var mediaCall = null;
    var pc = null;
    var pollTimer = null;
    var chromeTimer = null;
    var started = false;
    var audioOn = true;
    var videoOn = true;
    var seenIce = new Set();
    var lastRemoteOffer = null;
    var lastRemoteAnswer = null;

    var hostPeerId = String(roomName || ('room' + Date.now())).replace(/[^a-zA-Z0-9_-]/g, '').slice(0, 60);

    if (envHint) {
        envHint.textContent = location.host + ' · ' + (window.isSecureContext ? 'güvenli' : 'güvensiz!');
    }

    function setLobbyStatus(t, err) {
        if (!statusLobby) return;
        statusLobby.textContent = t;
        statusLobby.className = 'status-lobby' + (err ? ' err' : '');
    }
    function setCallStatus(t) {
        if (statusCall) statusCall.textContent = t;
    }
    function setDebug(t) {
        if (!debugEl) return;
        debugEl.classList.add('show');
        debugEl.textContent = t;
    }

    /* ——— Chrome (kontroller) göster/gizle ——— */
    function showChrome(ms) {
        if (!callStage) return;
        callStage.classList.add('chrome-on');
        if (tapHint) tapHint.classList.remove('show');
        clearTimeout(chromeTimer);
        if (ms !== 0) {
            chromeTimer = setTimeout(function () {
                callStage.classList.remove('chrome-on');
                if (tapHint) {
                    tapHint.classList.add('show');
                    setTimeout(function () { tapHint.classList.remove('show'); }, 2200);
                }
            }, ms || 4500);
        }
    }
    function toggleChrome() {
        if (!callStage.classList.contains('active')) return;
        if (callStage.classList.contains('chrome-on')) {
            callStage.classList.remove('chrome-on');
            clearTimeout(chromeTimer);
        } else {
            showChrome(5000);
        }
    }

    callStage.addEventListener('click', function (e) {
        // Kontrol butonlarına basınca gizleme
        if (e.target.closest('.ctrl') || e.target.closest('.pip')) return;
        toggleChrome();
    });

    /* ——— Kamera ——— */
    async function openCamera() {
        if (!window.isSecureContext) {
            throw Object.assign(new Error('http://127.0.0.1:8000 kullanın'), { name: 'SecurityError' });
        }
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw Object.assign(new Error('Kamera API yok'), { name: 'TypeError' });
        }
        var attempts = [
            { audio: true, video: { facingMode: 'user', width: { ideal: 720 }, height: { ideal: 1280 } } },
            { audio: true, video: { facingMode: 'user' } },
            { audio: true, video: true },
            { audio: false, video: true },
            { audio: true, video: false }
        ];
        var last = null;
        for (var i = 0; i < attempts.length; i++) {
            try {
                return await navigator.mediaDevices.getUserMedia(attempts[i]);
            } catch (e) {
                last = e;
                if (e.name === 'NotAllowedError' || e.name === 'SecurityError') throw e;
            }
        }
        throw last || new Error('Kamera açılamadı');
    }

    function mediaErr(e) {
        var n = (e && e.name) || 'Error';
        if (n === 'NotAllowedError' || n === 'PermissionDeniedError')
            return 'İzin verilmedi. Adres çubuğundan kamera/mikrofon → İzin ver, yenile, tekrar dene.';
        if (n === 'NotFoundError') return 'Kamera bulunamadı.';
        if (n === 'NotReadableError') return 'Kamera meşgul (başka uygulamayı kapatın).';
        if (n === 'SecurityError') return 'http://127.0.0.1:8000 adresini kullanın.';
        return 'Hata: ' + n + ' ' + ((e && e.message) || '');
    }

    function enterCallUi() {
        if (lobby) lobby.style.display = 'none';
        callStage.classList.add('active');
        callStage.setAttribute('aria-hidden', 'false');
        showChrome(6000);
        if (tapHint) {
            tapHint.classList.add('show');
            setTimeout(function () { tapHint.classList.remove('show'); }, 2500);
        }
        try { document.documentElement.requestFullscreen && document.documentElement.requestFullscreen(); } catch (e) {}
    }

    function showLocal(stream) {
        localStream = stream;
        localVideo.srcObject = stream;
        localVideo.muted = true;
        localVideo.playsInline = true;
        localVideo.play().catch(function () {});
    }

    function showRemote(stream) {
        remoteVideo.srcObject = stream;
        remoteVideo.muted = false;
        remoteVideo.volume = 1;
        remoteVideo.playsInline = true;
        if (remotePlaceholder) remotePlaceholder.style.display = 'none';
        if (liveBadge) liveBadge.style.display = 'inline-flex';
        if (btnUnmuteRemote) btnUnmuteRemote.style.display = 'inline-flex';
        stream.getAudioTracks().forEach(function (t) { t.enabled = true; });
        remoteVideo.play().then(function () {
            setCallStatus('Bağlandı');
            showChrome(3500);
        }).catch(function () {
            setCallStatus('Ses için “Sesi aç”a bas');
            showChrome(0);
        });
    }

    /* ——— PeerJS ——— */
    function waitPeerJs(ms) {
        return new Promise(function (resolve) {
            if (typeof Peer !== 'undefined') return resolve(true);
            var t0 = Date.now();
            var iv = setInterval(function () {
                if (typeof Peer !== 'undefined') { clearInterval(iv); resolve(true); }
                else if (Date.now() - t0 > ms) { clearInterval(iv); resolve(false); }
            }, 80);
        });
    }

    function connectPeerJs() {
        return new Promise(function (resolve, reject) {
            var id = role === 'hekim' ? hostPeerId : undefined;
            peer = new Peer(id, {
                host: (peerCfg && peerCfg.host) || '0.peerjs.com',
                port: (peerCfg && peerCfg.port) || 443,
                path: (peerCfg && peerCfg.path) || '/',
                secure: !peerCfg || peerCfg.secure !== false,
                key: (peerCfg && peerCfg.key) || 'peerjs',
                debug: 0,
                config: {
                    iceServers: iceServers && iceServers.length ? iceServers : [{ urls: 'stun:stun.l.google.com:19302' }],
                    sdpSemantics: 'unified-plan'
                }
            });
            var done = false;
            peer.on('open', function (myId) {
                if (done) return; done = true; resolve(myId);
            });
            peer.on('error', function (err) {
                if (done) return;
                if (err && err.type === 'peer-unavailable') return;
                if (err && (err.type === 'network' || err.type === 'server-error' || err.type === 'socket-error')) {
                    done = true; reject(err);
                }
            });
            setTimeout(function () { if (!done) { done = true; reject(new Error('timeout')); } }, 8000);
        });
    }

    function wireCall(call) {
        mediaCall = call;
        call.on('stream', showRemote);
        call.on('close', function () { setCallStatus('Karşı taraf ayrıldı'); showChrome(0); });
        call.on('error', function (err) { setCallStatus('Hata: ' + (err.type || '')); });
    }

    function startPeerJsSession() {
        if (role === 'hekim') {
            peer.on('call', function (call) {
                setCallStatus('Hasta bağlanıyor…');
                call.answer(localStream);
                wireCall(call);
            });
            setCallStatus('Hastayı bekliyor…');
        } else {
            var tries = 0;
            var tryCall = function () {
                tries++;
                if (!peer || peer.destroyed) return;
                setCallStatus('Hekim aranıyor… (' + tries + ')');
                try {
                    var call = peer.call(hostPeerId, localStream, { metadata: { name: displayName } });
                    if (!call) { if (tries < 25) setTimeout(tryCall, 2500); return; }
                    wireCall(call);
                    setTimeout(function () {
                        if (!remoteVideo.srcObject && tries < 25) {
                            try { call.close(); } catch (e) {}
                            tryCall();
                        }
                    }, 7000);
                } catch (e) {
                    if (tries < 25) setTimeout(tryCall, 2500);
                }
            };
            tryCall();
        }
    }

    /* ——— DIY fallback ——— */
    async function api(method, body) {
        var opt = {
            method: method,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf },
            credentials: 'same-origin'
        };
        if (body) {
            opt.headers['Content-Type'] = 'application/json';
            opt.body = JSON.stringify(body);
        }
        var res = await fetch(signalUrl, opt);
        var j = await res.json().catch(function () { return {}; });
        if (!res.ok) throw new Error(j.message || ('HTTP ' + res.status));
        return j;
    }

    async function startDiy() {
        pc = new RTCPeerConnection({
            iceServers: iceServers && iceServers.length ? iceServers : [{ urls: 'stun:stun.l.google.com:19302' }]
        });
        localStream.getTracks().forEach(function (t) { pc.addTrack(t, localStream); });
        pc.ontrack = function (ev) { if (ev.streams[0]) showRemote(ev.streams[0]); };
        pc.onicecandidate = function (ev) {
            if (!ev.candidate) return;
            api('POST', { type: 'ice', name: displayName, candidate: ev.candidate.toJSON() }).catch(function () {});
        };
        await api('POST', { type: 'ping', name: displayName });
        if (role === 'hekim') {
            var offer = await pc.createOffer({ offerToReceiveAudio: true, offerToReceiveVideo: true });
            await pc.setLocalDescription(offer);
            await api('POST', { type: 'offer', sdp: offer.sdp, name: displayName });
            setCallStatus('Hastayı bekliyor…');
        } else {
            setCallStatus('Hekim bekleniyor…');
        }
        pollTimer = setInterval(diyPoll, 1200);
        diyPoll();
    }

    async function diyPoll() {
        if (!pc) return;
        try {
            var j = await api('GET');
            var other = (j.state || {})[role === 'hekim' ? 'hasta' : 'hekim'] || {};
            if (role === 'hasta' && other.offer && other.offer !== lastRemoteOffer) {
                lastRemoteOffer = other.offer;
                await pc.setRemoteDescription({ type: 'offer', sdp: other.offer });
                var answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                await api('POST', { type: 'answer', sdp: answer.sdp, name: displayName });
            }
            if (role === 'hekim' && other.answer && other.answer !== lastRemoteAnswer) {
                lastRemoteAnswer = other.answer;
                if (pc.signalingState === 'have-local-offer') {
                    await pc.setRemoteDescription({ type: 'answer', sdp: other.answer });
                }
            }
            (other.ice || []).forEach(function (c) {
                var key = JSON.stringify(c);
                if (seenIce.has(key)) return;
                seenIce.add(key);
                if (pc.remoteDescription) pc.addIceCandidate(c).catch(function () {});
            });
        } catch (e) {}
    }

    /* ——— Start ——— */
    async function start(ev) {
        if (ev) { try { ev.preventDefault(); } catch (e) {} }
        if (started) return;
        btnStart.disabled = true;
        setLobbyStatus('Kamera izni isteniyor…');

        try {
            var stream = await openCamera();
            showLocal(stream);
            started = true;
            enterCallUi();
            setCallStatus('Bağlanıyor…');
        } catch (e) {
            started = false;
            btnStart.disabled = false;
            setLobbyStatus(mediaErr(e), true);
            setDebug(String(e && e.name) + '\n' + String(e && e.message));
            return;
        }

        try {
            var ok = await waitPeerJs(4000);
            if (ok) {
                await connectPeerJs();
                startPeerJsSession();
            } else {
                await startDiy();
            }
        } catch (e) {
            try { await startDiy(); }
            catch (e2) { setCallStatus('Bağlantı kurulamadı'); showChrome(0); }
        }
    }

    btnStart.addEventListener('click', start);
    btnStart.addEventListener('touchend', function (e) { e.preventDefault(); start(e); }, { passive: false });

    btnMute.addEventListener('click', function (e) {
        e.stopPropagation();
        if (!localStream) return;
        audioOn = !audioOn;
        localStream.getAudioTracks().forEach(function (t) { t.enabled = audioOn; });
        btnMute.classList.toggle('off', !audioOn);
        btnMute.textContent = audioOn ? '🎤' : '🔇';
        showChrome(4000);
    });
    btnCam.addEventListener('click', function (e) {
        e.stopPropagation();
        if (!localStream) return;
        videoOn = !videoOn;
        localStream.getVideoTracks().forEach(function (t) { t.enabled = videoOn; });
        btnCam.classList.toggle('off', !videoOn);
        btnCam.textContent = videoOn ? '📷' : '🚫';
        showChrome(4000);
    });
    btnUnmuteRemote.addEventListener('click', function (e) {
        e.stopPropagation();
        remoteVideo.muted = false;
        remoteVideo.volume = 1;
        remoteVideo.play().then(function () {
            setCallStatus('Ses açık');
            btnUnmuteRemote.style.display = 'none';
        }).catch(function () {});
        showChrome(3000);
    });
    btnHangup.addEventListener('click', function (e) {
        e.stopPropagation();
        try { mediaCall && mediaCall.close(); } catch (err) {}
        try { peer && peer.destroy(); } catch (err) {}
        try { pc && pc.close(); } catch (err) {}
        if (pollTimer) clearInterval(pollTimer);
        if (localStream) localStream.getTracks().forEach(function (t) { t.stop(); });
        localStream = null;
        localVideo.srcObject = null;
        remoteVideo.srcObject = null;
        peer = null; mediaCall = null; pc = null;
        started = false;
        try { document.exitFullscreen && document.exitFullscreen(); } catch (err) {}
        callStage.classList.remove('active', 'chrome-on');
        if (lobby) lobby.style.display = '';
        btnStart.disabled = false;
        if (remotePlaceholder) remotePlaceholder.style.display = '';
        if (liveBadge) liveBadge.style.display = 'none';
        setLobbyStatus('Görüşme bitti. Tekrar katılabilirsiniz.');
    });
})();
</script>
@endif
</body>
</html>
