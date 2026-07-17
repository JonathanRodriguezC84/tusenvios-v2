const CACHE_NAME = 'tusenvios-v3';
const PRECACHE_URLS = [
    '/css/app.css',
    '/js/app.js',
    '/offline.html',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const request = event.request;

    if (request.method !== 'GET') return;

    // Never intercept page navigations: pages must always come straight from the
    // network so clicking a link always shows the current page on the first try.
    if (request.mode === 'navigate') return;

    if (request.url.includes('/api/') || request.url.includes('/login') || request.url.includes('/logout')) return;

    // Only cache actual static assets (styles, scripts, fonts, images).
    if (!/\.(?:css|js|woff2?|ttf|png|jpe?g|gif|svg|webp|ico)(?:\?.*)?$/i.test(new URL(request.url).pathname)) return;

    event.respondWith(
        caches.match(request).then(cached => {
            const fetchPromise = fetch(request).then(response => {
                if (response && response.status === 200 && response.type === 'basic') {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
                }
                return response;
            }).catch(() => cached);

            return cached || fetchPromise;
        })
    );
});
