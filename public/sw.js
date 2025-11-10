// Evento de Instalación: Se dispara cuando el SW se registra por primera vez.
self.addEventListener('install', event => {
    console.log('Service Worker: Instalado');
    // Forzar al SW a activarse inmediatamente
    self.skipWaiting();
});

// Evento de Activación: Se dispara después de la instalación.
self.addEventListener('activate', event => {
    console.log('Service Worker: Activado');
    // Tomar control inmediato de todas las páginas
    event.waitUntil(self.clients.claim());
});

// Evento Fetch: Intercepta todas las peticiones de red.
self.addEventListener('fetch', event => {
    // Por ahora, solo respondemos con la petición de red
    event.respondWith(fetch(event.request));
});

/* ------------------------------------------------------------------
   LISTENER PARA PUSH (recibe la notificación del servidor)
   ------------------------------------------------------------------ */
self.addEventListener('push', event => {
    console.log('Service Worker: Push Recibido.');

    let data;
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        console.error('Error al parsear payload:', e);
        data = { title: 'Notificación', body: 'Revisa tu aplicación.' };
    }

    const title = data.title || 'Recordatorio de Medicina';

    const options = {
        body: data.body || 'Es hora de tu toma.',
        icon: data.icon || '/images/icons/icon-192x192.png',
        badge: data.badge || '/images/icons/icon-192x192.png',
        image: data.image || undefined,
        data: data.data || {},
        actions: data.actions || [], // <-- Esto leerá los botones 'open_app' y 'skip'
        requireInteraction: true // Mantiene la notificación en pantalla
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

/* ------------------------------------------------------------------
   LISTENER PARA CLICK EN NOTIFICACIÓN (¡CÓDIGO NUEVO!)
   ------------------------------------------------------------------ */
self.addEventListener('notificationclick', event => {
    console.log('Service Worker: Clic en Notificación Recibido.');

    // Cierra la notificación
    event.notification.close();

    const data = event.notification.data || {};
    const action = event.action; // 'open_app', 'skip', o vacío


    if (action === 'skip') {
        // Si el usuario presiona "Saltar"
        console.log('Acción: Saltar');
        // No hacemos nada, solo cerramos (ya se hizo arriba).
    
    } else {
        // Si el usuario presiona "No olvides tu dosis" (action === 'open_app')
        // O si presiona el cuerpo de la notificación (action === '')
        console.log('Acción: Abrir App');
        
        event.waitUntil(
            // Abre la URL que pasamos desde el backend
            clients.openWindow(data.url || '/dashboard')
        );
    }

});