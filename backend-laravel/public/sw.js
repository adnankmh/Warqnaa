const WARQNA_CACHE = 'warqna-v139-mobile-app-no-studio';
const STATIC_ASSETS = [
  '/', '/games', '/store', '/rewards', '/offline.html',
  '/assets/css/app.css', '/assets/css/mobile-app.css',
  '/assets/js/app.js', '/assets/js/mobile-app.js',
  '/manifest.webmanifest',
  '/assets/icons/icon.svg', '/assets/icons/maskable.svg',
  '/assets/icons/icon-192.png', '/assets/icons/icon-512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(caches.open(WARQNA_CACHE).then(cache => cache.addAll(STATIC_ASSETS).catch(() => null)));
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(k => k !== WARQNA_CACHE).map(k => caches.delete(k)))));
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/realtime/') || url.pathname.includes('/sync') || url.pathname.includes('/chat')) return;
  const isAsset = url.pathname.startsWith('/assets/') || url.pathname === '/manifest.webmanifest' || url.pathname === '/offline.html';
  event.respondWith(
    fetch(req).then(res => {
      const copy = res.clone();
      if (isAsset || res.headers.get('content-type')?.includes('text/html')) {
        caches.open(WARQNA_CACHE).then(cache => cache.put(req, copy)).catch(() => null);
      }
      return res;
    }).catch(() => caches.match(req).then(cached => cached || caches.match('/offline.html')))
  );
});
