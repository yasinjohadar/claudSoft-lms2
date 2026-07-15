(function () {
    var STORAGE_KEY = 'claudsoft_device_fp';
    var HEADER_NAME = 'X-Device-Fingerprint';

    function toHex(buffer) {
        return Array.from(new Uint8Array(buffer))
            .map(function (b) { return b.toString(16).padStart(2, '0'); })
            .join('');
    }

    function sha256(text) {
        if (!window.crypto || !window.crypto.subtle || typeof TextEncoder === 'undefined') {
            // Fallback: simple non-crypto hash when SubtleCrypto is unavailable (HTTP, old browsers).
            var hash = 0;
            for (var i = 0; i < text.length; i++) {
                hash = ((hash << 5) - hash) + text.charCodeAt(i);
                hash |= 0;
            }
            var hex = (hash >>> 0).toString(16).padStart(8, '0');
            return Promise.resolve((hex + hex + hex + hex + hex + hex + hex + hex).slice(0, 64));
        }

        return window.crypto.subtle.digest('SHA-256', new TextEncoder().encode(text)).then(toHex);
    }

    function canvasFingerprint() {
        try {
            var canvas = document.createElement('canvas');
            canvas.width = 240;
            canvas.height = 60;
            var ctx = canvas.getContext('2d');
            if (!ctx) {
                return 'no-canvas';
            }
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillStyle = '#f60';
            ctx.fillRect(10, 10, 100, 30);
            ctx.fillStyle = '#069';
            ctx.fillText('ClaudSoftFP', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('ClaudSoftFP', 4, 17);
            return canvas.toDataURL();
        } catch (e) {
            return 'canvas-error';
        }
    }

    function webglFingerprint() {
        try {
            var canvas = document.createElement('canvas');
            var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) {
                return 'no-webgl';
            }
            var debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            if (!debugInfo) {
                return 'webgl-no-debug';
            }
            var vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) || '';
            var renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) || '';
            return vendor + '~' + renderer;
        } catch (e) {
            return 'webgl-error';
        }
    }

    function collectComponents() {
        var nav = window.navigator || {};
        var screenObj = window.screen || {};
        return [
            canvasFingerprint(),
            webglFingerprint(),
            String(screenObj.width || '') + 'x' + String(screenObj.height || ''),
            String(screenObj.colorDepth || ''),
            String(Intl && Intl.DateTimeFormat ? Intl.DateTimeFormat().resolvedOptions().timeZone : ''),
            String(nav.language || ''),
            String(nav.languages ? nav.languages.join(',') : ''),
            String(nav.hardwareConcurrency || ''),
            String(nav.deviceMemory || ''),
            String(nav.platform || ''),
            String(nav.maxTouchPoints || 0),
            String(typeof window.ontouchstart !== 'undefined'),
        ].join('|');
    }

    function getOrCreateFingerprint() {
        return sha256(collectComponents()).then(function (fp) {
            try {
                localStorage.setItem(STORAGE_KEY, fp);
            } catch (e) {
                // ignore quota / private mode
            }
            try {
                document.cookie = 'claudsoft_device_fp=' + encodeURIComponent(fp)
                    + ';path=/;Max-Age=31536000;SameSite=Lax';
            } catch (e) {
                // ignore
            }
            return fp;
        });
    }

    function injectIntoForms(fp) {
        document.querySelectorAll('form[data-device-token]').forEach(function (form) {
            var input = form.querySelector('input[name="device_fingerprint_client"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'device_fingerprint_client';
                form.appendChild(input);
            }
            input.value = fp;
        });
    }

    function patchFetch(fp) {
        if (typeof window.fetch !== 'function') {
            return;
        }
        var originalFetch = window.fetch.bind(window);
        window.fetch = function (input, init) {
            init = init || {};
            var headers = new Headers(init.headers || {});
            if (!headers.has(HEADER_NAME)) {
                headers.set(HEADER_NAME, fp);
            }
            init.headers = headers;
            return originalFetch(input, init);
        };
    }

    window.ClaudSoftDeviceFingerprint = {
        get: getOrCreateFingerprint,
        STORAGE_KEY: STORAGE_KEY,
        HEADER_NAME: HEADER_NAME,
    };

    document.addEventListener('DOMContentLoaded', function () {
        getOrCreateFingerprint().then(function (fp) {
            if (!fp) {
                return;
            }
            injectIntoForms(fp);
            patchFetch(fp);
        }).catch(function () {
            // Non-blocking: device binding falls back to token / UA fingerprint.
        });
    });
})();
