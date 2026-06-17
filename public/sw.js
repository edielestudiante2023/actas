const APP_CACHE = 'actas-app-v1';

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((names) =>
            Promise.all(
                // Borra cachés antiguas (incluida la del SW de login) y deja solo la actual.
                names.filter((n) => n !== APP_CACHE).map((n) => caches.delete(n))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    event.respondWith(
        fetch(event.request)
            .then((res) => {
                // Cachea una copia de las respuestas GET correctas para fallback offline.
                if (res && res.status === 200 && res.type === 'basic') {
                    const copy = res.clone();
                    caches.open(APP_CACHE).then((c) => c.put(event.request, copy));
                }
                return res;
            })
            .catch(() => caches.match(event.request))
    );
});
