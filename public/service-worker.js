const CACHE_NAME = 'splitwise-pwa-v12';
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

function isValidScopeUrl(url) {
  try {
    const resolved = new URL(url, SCOPE_URL);
    return resolved.origin === SCOPE_ORIGIN && resolved.pathname.startsWith(SCOPE_URL.pathname);
  } catch (_) {
    return false;
  }
}

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
  if (event.request.method !== 'GET') return;

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
  let title = 'Gastito';
  let body = 'Tenés una nueva notificación.';
  let icon = DEFAULT_ICON;
  let badge = DEFAULT_ICON;
  let tag = '';
  let data = {};

  if (event.data) {
    try {
      const parsed = event.data.json();
      if (parsed && typeof parsed === 'object') {
        title = parsed.title || 'Gastito';
        body = parsed.body || 'Tenés una nueva notificación.';
        icon = parsed.icon || DEFAULT_ICON;
        badge = parsed.badge || DEFAULT_ICON;
        tag = parsed.tag || '';

        const rawUrl = parsed.url || '';
        if (typeof rawUrl === 'string' && rawUrl.length > 0) {
          if (isValidScopeUrl(rawUrl)) {
            const resolved = new URL(rawUrl, SCOPE_URL);
            data.url = resolved.pathname + resolved.search;
          }
        }
      }
    } catch (_) {
      body = event.data.text() || 'Tenés una nueva notificación.';
    }
  }

  event.waitUntil(self.registration.showNotification(title, {
    body: body,
    icon: icon,
    badge: badge,
    tag: tag,
    data: data,
    vibrate: [200, 100, 200],
    requireInteraction: false,
  }));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = (event.notification.data && event.notification.data.url) ? event.notification.data.url : SCOPE_URL.pathname;

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      for (const client of windowClients) {
        try {
          const clientUrl = new URL(client.url);
          if (clientUrl.origin === SCOPE_ORIGIN) {
            if (targetUrl && targetUrl !== '/' && typeof client.navigate === 'function') {
              return client.navigate(targetUrl).then((navigated) => navigated ? navigated.focus() : client.focus());
            }
            client.postMessage({ type: 'notificationclick', url: targetUrl });
            return client.focus();
          }
        } catch (_) { /* skip */ }
      }

      if (targetUrl && isValidScopeUrl(targetUrl)) {
        return clients.openWindow(targetUrl);
      }

      return clients.openWindow(SCOPE_URL.pathname);
    })
  );
});

// pushsubscriptionchange: la renovación automática requiere sesión y CSRF.
// En lugar de un flujo parcial que falla silenciosamente, delegamos a la
// pantalla de Perfil/Configuración donde el usuario puede re-suscribirse.
// Documentado en la vista de configuración de notificaciones.
self.addEventListener('pushsubscriptionchange', (event) => {
  event.waitUntil(
    self.registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: event.oldSubscription ? event.oldSubscription.options.applicationServerKey : null
    }).catch(function () { /* la pantalla de Perfil repara la suscripción */ })
  );
});

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
