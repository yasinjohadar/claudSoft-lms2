(function () {
    var STORAGE_KEY = 'claudsoft_device_token';
    var COOKIE_KEY = 'claudsoft_device_token';

    function generateUuid() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0;
            var v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function setCookie(name, value) {
        try {
            document.cookie = name + '=' + encodeURIComponent(value)
                + ';path=/;Max-Age=31536000;SameSite=Lax';
        } catch (e) {
            // ignore
        }
    }

    function getOrCreateDeviceToken() {
        try {
            var existing = localStorage.getItem(STORAGE_KEY);
            if (existing && /^[a-f0-9-]{36}$/i.test(existing)) {
                setCookie(COOKIE_KEY, existing.toLowerCase());
                return existing.toLowerCase();
            }

            var token = generateUuid().toLowerCase();
            localStorage.setItem(STORAGE_KEY, token);
            setCookie(COOKIE_KEY, token);
            return token;
        } catch (e) {
            return null;
        }
    }

    window.ClaudSoftDeviceToken = {
        get: getOrCreateDeviceToken,
        STORAGE_KEY: STORAGE_KEY,
    };

    document.addEventListener('DOMContentLoaded', function () {
        var token = getOrCreateDeviceToken();
        if (!token) {
            return;
        }

        document.querySelectorAll('form[data-device-token]').forEach(function (form) {
            var input = form.querySelector('input[name="device_token"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'device_token';
                form.appendChild(input);
            }
            input.value = token;
        });
    });
})();
