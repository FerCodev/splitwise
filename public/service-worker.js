const CACHE_NAME = 'splitwise-pwa-v7';
const SCOPE_URL = new URL(self.registration.scope);
const assetUrl = (path) => new URL(path, SCOPE_URL).toString();
const CORE_ASSETS = [
  assetUrl('assets/app.css'),
  assetUrl('manifest.webmanifest'),
  assetUrl('assets/pwa/icon-192.png'),
  assetUrl('assets/pwa/icon-512.png')
];

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