<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="referrer" content="origin">
    <title>كلاودسوفت</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100%; height: 100%; background: #000; overflow: hidden;
            -webkit-user-select: none; user-select: none; -webkit-touch-callout: none;
            -webkit-tap-highlight-color: transparent;
        }
        #stage { position: fixed; inset: 0; background: #000; }
        #stage iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; display: block; }

        /* علامة مائية متحركة فوق الفيديو — لا تعيق اللمس. */
        .wm {
            position: absolute; z-index: 5; pointer-events: none;
            font: 600 13px/1.2 system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif;
            color: rgba(255, 255, 255, .38);
            text-shadow: 0 0 2px rgba(0, 0, 0, .6);
            white-space: nowrap;
            transition: top 1.6s ease, left 1.6s ease, opacity .8s ease;
        }
        .wm.faint { color: rgba(255, 255, 255, .14); font-size: 11px; }

        /* ملء شاشة للصفحة كاملة (يبقي العلامة المائية) بدلاً من ملء شاشة Bunny. */
        #fs {
            position: absolute; z-index: 6; top: 10px; left: 10px;
            width: 38px; height: 38px; border-radius: 10px; border: 0;
            background: rgba(0, 0, 0, .45); color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            opacity: .75;
        }
        #fs:active { opacity: 1; }
        #fs svg { width: 20px; height: 20px; }
    </style>
</head>
<body>
<div id="stage">
    {{-- بدون allowfullscreen: زر ملء الشاشة في Bunny معطّل لصالح زر الصفحة (العلامة المائية تبقى ظاهرة). --}}
    <iframe
        src="{{ $embedUrl }}"
        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
        referrerpolicy="origin"
        loading="eager"
    ></iframe>

    <div class="wm" id="wm1">{{ $watermark }}</div>
    <div class="wm faint" id="wm2">{{ $watermark }}</div>

    <button id="fs" type="button" aria-label="ملء الشاشة">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/>
        </svg>
    </button>
</div>

<script>
    (function () {
        // ردع الحفظ/النسخ/الفحص (طبقة إضافية؛ الحماية الأساسية من Bunny والتذكرة والعلامة المائية).
        ['contextmenu', 'dragstart', 'selectstart', 'copy'].forEach(function (e) {
            document.addEventListener(e, function (ev) { ev.preventDefault(); });
        });
        document.addEventListener('keydown', function (ev) {
            var k = (ev.key || '').toLowerCase();
            if (k === 'f12' || ((ev.ctrlKey || ev.metaKey) && ['s', 'u', 'p', 'c'].indexOf(k) !== -1)
                || ((ev.ctrlKey || ev.metaKey) && ev.shiftKey && ['i', 'j', 'c'].indexOf(k) !== -1)) {
                ev.preventDefault();
            }
        });

        // تحريك العلامة المائية لمواقع عشوائية دورياً.
        var marks = [document.getElementById('wm1'), document.getElementById('wm2')];
        function move() {
            marks.forEach(function (m) {
                var maxX = Math.max(0, window.innerWidth - m.offsetWidth - 12);
                var maxY = Math.max(0, window.innerHeight - m.offsetHeight - 50);
                m.style.left = (6 + Math.random() * maxX) + 'px';
                m.style.top = (44 + Math.random() * maxY) + 'px';
            });
        }
        move();
        setInterval(move, 7000);
        window.addEventListener('resize', move);

        // ملء الشاشة للصفحة كاملة.
        var stage = document.getElementById('stage');
        document.getElementById('fs').addEventListener('click', function () {
            var doc = document;
            var isFs = doc.fullscreenElement || doc.webkitFullscreenElement;
            if (isFs) {
                (doc.exitFullscreen || doc.webkitExitFullscreen).call(doc);
            } else {
                var req = stage.requestFullscreen || stage.webkitRequestFullscreen;
                if (req) req.call(stage);
            }
        });
    })();
</script>
</body>
</html>
