const CACHE_NAME = 'village-foods-v4.0';
const OFFLINE_URL = 'offline.html';

const ASSETS_TO_CACHE = [
  'offline.html',
  'assets/css/variables.css',
  'assets/css/components.css',
  'assets/images/logo/VillageFoods Delivery Logo.png',
  'https://unpkg.com/lucide@latest',
  'https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap'
];

// Install Event - Cache initial assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Opened cache');
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// Activate Event - Clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch Event - Serve from network first for navigation, cache for static assets
self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  // BYPASS for Shop Images - ensuring they are always live and correctly handled
  if (event.request.url.includes('assets/images/shops/')) {
    return;
  }

  // NETWORK-FIRST for Navigation (HTML/PHP pages)
  // This ensures the user always gets the latest session/login state if online
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .catch(() => {
          return caches.match(OFFLINE_URL);
        })
    );
    return;
  }

  // CACHE-FIRST for Static Assets
  event.respondWith(
    caches.match(event.request).then((response) => {
      // Cache hit - return response
      if (response) {
        return response;
      }

      // Not in cache - fetch from network
      return fetch(event.request);
    })
  );
});
