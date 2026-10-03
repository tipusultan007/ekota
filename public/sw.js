const CACHE_NAME = 'ekota-v3';
const ASSETS_TO_CACHE = [
    '/css/custom.css',
    '/splash-screen.css',
    '/build/plugins/select2/select2.min.css',
    '/build/plugins/flatpickr/flatpickr.min.css',
    '/build/plugins/jquery/jquery.min.js',
    '/build/plugins/select2/select2.min.js',
    '/build/plugins/bootstrap/bootstrap.bundle.min.js',
    '/build/plugins/lucide/lucide.min.js',
    '/build/plugins/perfect-scrollbar/perfect-scrollbar.min.js',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

// Install Event
self.addEventListener('install', event => {
    console.log('[SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log('[SW] Caching vital assets');
            return Promise.allSettled(
                ASSETS_TO_CACHE.map(url => {
                    return cache.add(url).catch(err => console.error(`[SW] Failed to cache: ${url}`, err));
                })
            );
        })
    );
});

// Activate Event
self.addEventListener('activate', event => {
    console.log('[SW] Activating...');
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
            );
        })
    );
});

// Fetch Event
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Network First strategy for the dashboard to ensure fresh data
    if (url.pathname === '/dashboard') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // Update cache with the fresh version
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Cache First for other assets
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});
