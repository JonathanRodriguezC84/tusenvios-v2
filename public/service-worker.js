const CACHE_NAME = 'tus-envios-static-v17';
const STATIC_ASSETS = [
    '/site.webmanifest',
    '/favicon.ico',
    '/images/logotusenvios.png',
    '/images/logotusenvios-square.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS).catch(() => null))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys
                .filter((key) => key.startsWith('tus-envios') && key !== CACHE_NAME)
                .map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    // Las pantallas internas nunca se cachean. Asi el color de marca se ve al instante.
    if (event.request.mode === 'navigate' || event.request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(new Request(event.request, { cache: 'no-store' }))
                .catch(() => new Response('Sin conexion. Vuelve a intentar cuando tengas internet.', {
                    status: 503,
                    headers: { 'Content-Type': 'text/plain; charset=UTF-8' }
                }))
        );
        return;
    }

    const isStaticAsset = /\.(?:css|js|png|jpg|jpeg|gif|webp|svg|ico|woff2?|ttf|webmanifest)$/i.test(url.pathname);
    if (! isStaticAsset) {
        event.respondWith(fetch(new Request(event.request, { cache: 'no-store' })));
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) {
                return cached;
            }

            return fetch(event.request).then((response) => {
                if (response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                }

                return response;
            });
        })
    );
});