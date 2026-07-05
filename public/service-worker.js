const CACHE_NAME = 'splitwise-pwa-v11';
const SCOPE_URL = new URL(self.registration.scope);
const SCOPE_ORIGIN = SCOPE_URL.origin;

const assetUrl = (path) => new URL(path, SCOPE_URL).toString();
const CORE_ASSETS = [
  assetUrl('assets/app.css'),
  assetUrl('manifest.webmanifest'),
  assetUrl('assets/pwa/icon-192.png'),
  assetUrl('assets/pwa/icon-512.png')
];

const DEFAULT_ICON = assetUrl('assets/pwa/icon-192.png');

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(CORE_ASSETS)).catch(() => undefined)
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        if (response && response.status === 200 && event.request.url.startsWith(assetUrl('assets/'))) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
        }
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});

self.addEventListener('push', (event) => {
  let payload = { title: 'Gastito', body: 'Tenés una nueva notificación.' };

  if (event.data) {
    try {
      const parsed = event.data.json();
      if (parsed && typeof parsed === 'object') {
        payload.title = parsed.title || 'Gastito';
        payload.body = parsed.body || 'Tenés una nueva notificación.';
        payload.icon = parsed.icon || DEFAULT_ICON;
        payload.badge = parsed.badge || DEFAULT_ICON;
        payload.tag = parsed.tag || '';
        payload.data = payload.data || {};

        const rawUrl = parsed.url || '';
        if (typeof rawUrl === 'string' && rawUrl.length > 0) {
          try {
            const resolved = new URL(rawUrl, SCOPE_URL);
            if (resolved.origin === SCOPE_ORIGIN) {
              payload.data.url = resolved.pathname + resolved.search;
            }
          } catch (_) { /* ignore invalid urls */ }
        }
      }
    } catch (_) {
      payload.body = event.data.text() || 'Tenés una nueva notificación.';
    }
  }

  const options = {
    body: payload.body,
    icon: payload.icon || DEFAULT_ICON,
    badge: payload.badge || DEFAULT_ICON,
    tag: payload.tag || '',
    data: payload.data || {},
    vibrate: [200, 100, 200],
    requireInteraction: false,
  };

  event.waitUntil(self.registration.showNotification(payload.title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      for (let i = 0; i < windowClients.length; i++) {
        const client = windowClients[i];
        try {
          const clientUrl = new URL(client.url);
          if (clientUrl.origin === SCOPE_ORIGIN) {
            client.postMessage({ type: 'notificationclick', url: targetUrl });
            return client.focus();
          }
        } catch (_) { /* skip */ }
      }

      try {
        const dest = new URL(targetUrl, SCOPE_URL);
        if (dest.origin === SCOPE_ORIGIN) {
          return clients.openWindow(dest.toString());
        }
      } catch (_) { /* skip */ }

      return clients.openWindow('/');
    })
  );
});

self.addEventListener('pushsubscriptionchange', (event) => {
  event.waitUntil(
    self.registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: event.oldSubscription ? event.oldSubscription.options.applicationServerKey : null
    }).then(function (newSubscription) {
      return fetch('/notificaciones/suscripciones', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newSubscription.toJSON())
      }).catch(function () { /* No session, will be repaired from profile page */ });
    }).catch(function () { /* Silently fail; profile page can repair */ })
  );
});

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
