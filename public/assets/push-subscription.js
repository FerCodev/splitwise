/**
 * Gestión de suscripción Web Push para Gastito.
 */
(function () {
    'use strict';

    var BASE_URL = (window.Gastito && window.Gastito.baseUrl) ? window.Gastito.baseUrl.replace(/\/+$/, '') : '';

    function getCsrfData() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) return { name: 'csrf_token_name', value: '' };
        var name = meta.getAttribute('data-name') || 'csrf_token_name';
        var value = meta.getAttribute('content') || '';
        return { name: name, value: value };
    }

    function updateCsrf(newHash) {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            meta.setAttribute('content', newHash);
        }
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; i++) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function showResult(el, message, type) {
        el.textContent = message;
        el.className = 'mt-2 small text-' + (type || 'muted');
    }

    function checkSupport() {
        var unsupported = document.getElementById('push-unsupported');
        var insecure = document.getElementById('push-insecure');
        var activate = document.getElementById('btn-activate-push');
        var deactivate = document.getElementById('btn-deactivate-push');
        var test = document.getElementById('btn-test-push');
        var statusText = document.getElementById('push-status-text');

        if (!window.isSecureContext || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
            if (unsupported) unsupported.classList.remove('d-none');
            if (!window.isSecureContext && insecure) insecure.classList.remove('d-none');
            return false;
        }

        if (activate) activate.classList.remove('d-none');
        if (deactivate) deactivate.classList.remove('d-none');
        if (test) test.classList.remove('d-none');

        checkCurrentSubscription();
        return true;
    }

    function checkCurrentSubscription() {
        if (!navigator.serviceWorker || !navigator.serviceWorker.ready) return;

        navigator.serviceWorker.ready.then(function (registration) {
            return registration.pushManager.getSubscription();
        }).then(function (subscription) {
            var activate = document.getElementById('btn-activate-push');
            var deactivate = document.getElementById('btn-deactivate-push');
            var test = document.getElementById('btn-test-push');
            var statusText = document.getElementById('push-status-text');

            if (subscription) {
                if (activate) activate.classList.add('d-none');
                if (deactivate) deactivate.classList.remove('d-none');
                if (test) test.classList.remove('d-none');
                if (statusText) statusText.textContent = 'Este dispositivo ya tiene notificaciones activadas.';
            } else {
                if (activate) activate.classList.remove('d-none');
                if (deactivate) deactivate.classList.add('d-none');
                if (test) test.classList.add('d-none');
                if (statusText) statusText.textContent = 'Notificaciones no activadas en este dispositivo.';
            }
        }).catch(function () { /* ignore */ });
    }

    function getPublicKey() {
        return fetch(BASE_URL + '/notificaciones/clave-publica')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.configured || !data.publicKey) {
                    throw new Error('Push no configurado');
                }
                return data.publicKey;
            });
    }

    function subscribeToPush() {
        var resultEl = document.getElementById('push-result');
        var csrf = getCsrfData();

        getPublicKey().then(function (publicKey) {
            return navigator.serviceWorker.ready.then(function (registration) {
                return registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(publicKey)
                });
            });
        }).then(function (subscription) {
            var subJson = subscription.toJSON();
            var body = new FormData();
            body.append(csrf.name, csrf.value);
            body.append('endpoint', subJson.endpoint);
            body.append('keys[p256dh]', subJson.keys.p256dh || '');
            body.append('keys[auth]', subJson.keys.auth || '');

            return fetch(BASE_URL + '/notificaciones/suscripciones', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: body
            });
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.success) {
                showResult(resultEl, 'Dispositivo activado correctamente.', 'success');
                if (data.csrfHash) {
                    updateCsrf(data.csrfHash);
                }
                checkCurrentSubscription();
            } else {
                showResult(resultEl, (data.error || 'Error al activar.'), 'danger');
            }
        }).catch(function (err) {
            showResult(resultEl, err.message || 'Error al activar las notificaciones.', 'danger');
        });
    }

    function unsubscribeFromPush() {
        var resultEl = document.getElementById('push-result');
        var csrf = getCsrfData();

        navigator.serviceWorker.ready.then(function (registration) {
            return registration.pushManager.getSubscription();
        }).then(function (subscription) {
            if (!subscription) {
                showResult(resultEl, 'No hay suscripción activa.', 'warning');
                return Promise.reject('no-sub');
            }

            var subJson = subscription.toJSON();

            return subscription.unsubscribe().then(function () {
                var body = new FormData();
                body.append(csrf.name, csrf.value);
                body.append('endpoint', subJson.endpoint);
                return fetch(BASE_URL + '/notificaciones/suscripciones/eliminar', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: body
                });
            });
        }).then(function () {
            showResult(resultEl, 'Dispositivo desactivado.', 'success');
            checkCurrentSubscription();
        }).catch(function (err) {
            if (err !== 'no-sub') {
                showResult(resultEl, 'Error al desactivar.', 'danger');
            }
        });
    }

    function sendTestNotification() {
        var resultEl = document.getElementById('push-result');
        var csrf = getCsrfData();

        var body = new FormData();
        body.append(csrf.name, csrf.value);

        fetch(BASE_URL + '/notificaciones/prueba', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.error) {
                showResult(resultEl, data.error, 'warning');
            } else {
                showResult(resultEl, 'Prueba enviada (éxito: ' + (data.success || 0) + ').', data.success > 0 ? 'success' : 'warning');
            }
            if (data.csrfHash) {
                updateCsrf(data.csrfHash);
            }
            if (data.csrfToken) {
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('data-name', data.csrfToken);
            }
        }).catch(function () {
            showResult(resultEl, 'Error al enviar prueba.', 'danger');
        });
    }

    function requestPermissionAndSubscribe() {
        if (Notification.permission === 'granted') {
            subscribeToPush();
            return;
        }

        if (Notification.permission === 'denied') {
            showResult(document.getElementById('push-result'), 'El permiso de notificaciones fue denegado. Revisá la configuración del navegador.', 'warning');
            return;
        }

        Notification.requestPermission().then(function (permission) {
            if (permission === 'granted') {
                subscribeToPush();
            } else {
                showResult(document.getElementById('push-result'), 'Permiso denegado. No se pueden activar las notificaciones.', 'warning');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!checkSupport()) return;

        document.getElementById('btn-activate-push').addEventListener('click', function (e) {
            e.preventDefault();
            requestPermissionAndSubscribe();
        });

        document.getElementById('btn-deactivate-push').addEventListener('click', function (e) {
            e.preventDefault();
            unsubscribeFromPush();
        });

        var testBtn = document.getElementById('btn-test-push');
        if (testBtn) {
            testBtn.addEventListener('click', function (e) {
                e.preventDefault();
                sendTestNotification();
            });
        }
    });
})();
