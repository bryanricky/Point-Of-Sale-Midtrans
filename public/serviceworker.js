const CACHE_NAME = 'tokoridho-pos-v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
  console.log('✅ Service Worker: Installed');
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('✅ Pre-caching offline page');
      return cache.addAll([OFFLINE_URL]);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  console.log('✅ Service Worker: Activated');
  event.waitUntil(
    caches.keys().then((cacheNames) =>
      Promise.all(
        cacheNames.map((name) => {
          if (name !== CACHE_NAME) {
            console.log('🗑️ Removing old cache:', name);
            return caches.delete(name);
          }
        })
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const request = event.request;

  // ✅ 1. Navigasi halaman (mode navigate)
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          return caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, response.clone());
            return response;
          });
        })
        .catch(async () => {
          const cachedResponse = await caches.match(request);
          return cachedResponse || caches.match(OFFLINE_URL);
        })
    );
    return;
  }

  // ✅ 2. Untuk semua resource lainnya (static, API, dll)
  event.respondWith(
    fetch(request)
      .then((response) => {
        return caches.open(CACHE_NAME).then((cache) => {
          cache.put(request, response.clone());
          return response;
        });
      })
      .catch(() => {
        return caches.match(request).then((cachedResponse) => {
          return cachedResponse || caches.match(OFFLINE_URL);
        });
      })
  );
});
