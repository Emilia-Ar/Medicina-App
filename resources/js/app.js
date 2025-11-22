import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// ---------------------------------------------------
// Service Worker + Push Notifications
// ---------------------------------------------------

if ('serviceWorker' in navigator && 'PushManager' in window) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(swReg => {
                console.log('%c✔ Service Worker registrado', 'color: #00c853');
                window.swRegistration = swReg;

                // Revisa si ya está suscrito
                checkSubscriptionStatus();
            })
            .catch(err => {
                console.error('%c✖ Error registrando SW:', 'color: red', err);
            });
    });
} else {
    console.warn('Push messaging no es soportado por este navegador.');
}

// ---------------------------------------------------
// Verifica si el usuario ya está suscrito
// ---------------------------------------------------
function checkSubscriptionStatus() {
    window.swRegistration.pushManager.getSubscription()
        .then(subscription => {
            if (subscription) {
                console.log('✔ Usuario YA está suscrito.');
            } else {
                console.log('✖ Usuario NO está suscrito.');
                displayNotificationButton();
            }
        })
        .catch(err => console.error('Error revisando subscripción:', err));
}

// ---------------------------------------------------
// Muestra botón para activar notificaciones
// ---------------------------------------------------
function displayNotificationButton() {
    const area = document.getElementById('push-notification-area');
    const button = document.getElementById('enable-push-notifications');

    if (!area || !button) return;

    area.style.display = 'block';

    button.addEventListener('click', () => {
        button.disabled = true;
        button.textContent = 'Procesando…';
        askForNotificationPermission(button);
    });
}

// ---------------------------------------------------
// Solicita permiso al usuario
// ---------------------------------------------------
function askForNotificationPermission(button) {
    Notification.requestPermission().then(status => {
        console.log('Estado del permiso:', status);

        if (status === 'granted') {
            subscribeUserToPush(button);
        } else {
            button.textContent = 'Permiso denegado';
            button.disabled = false;
        }
    });
}

// ---------------------------------------------------
// Convierte clave Base64 a Uint8Array
// ---------------------------------------------------
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// ---------------------------------------------------
// Suscribe el usuario al Push
// ---------------------------------------------------
function subscribeUserToPush(button) {
    if (!window.vapidPublicKey || window.vapidPublicKey.length < 30) {
        console.error('✖ ERROR: VAPID public key inválida o no asignada.');
        alert("Error: la clave VAPID pública no está configurada en el frontend.");
        button.textContent = 'Error';
        button.disabled = false;
        return;
    }

    const appServerKey = urlBase64ToUint8Array(window.vapidPublicKey);

    window.swRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: appServerKey
    })
        .then(subscription => {
            console.log('%c✔ Usuario SUSCRITO correctamente', 'color: #00c853', subscription);
            sendSubscriptionToBackend(subscription);

            const area = document.getElementById('push-notification-area');
            if (area) area.style.display = 'none';
        })
        .catch(err => {
            console.error('✖ Error al suscribir:', err);
            button.textContent = 'Error al activar';
            button.disabled = false;
        });
}

// ---------------------------------------------------
// Envía la suscripción al backend Laravel
// ---------------------------------------------------
function sendSubscriptionToBackend(subscription) {
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');

    return fetch('/push-subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            endpoint: subscription.endpoint,
            publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
        })
    })
        .then(res => res.json())
        .then(data => {
            console.log('%c✔ Suscripción guardada en el backend', 'color: #2962ff', data);
        })
        .catch(err => {
            console.error('✖ Error enviando al backend:', err);
        });
}
