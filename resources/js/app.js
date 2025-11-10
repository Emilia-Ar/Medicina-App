import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


if ('serviceWorker' in navigator && 'PushManager' in window) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(swReg => {
                console.log('Service Worker registrado:', swReg);
                // Guarda la registración del SW para usarla después
                window.swRegistration = swReg;
                // Revisa el estado de la suscripción al cargar
                checkSubscriptionStatus();
            })
            .catch(err => {
                console.error('Fallo en el registro del Service Worker:', err);
            });
    });
} else {
    console.warn('Push messaging no es soportado en este navegador.');
}

function checkSubscriptionStatus() {
    // Revisa si ya tenemos una suscripción
    window.swRegistration.pushManager.getSubscription()
        .then(subscription => {
            const isSubscribed = !(subscription === null);
            if (isSubscribed) {
                console.log('Usuario YA está suscrito.');
                // (Opcional) Sincronizar con el backend
                // sendSubscriptionToBackend(subscription);
            } else {
                console.log('Usuario NO está suscrito.');
                // Muestra el botón para suscribirse
                displayNotificationButton();
            }
        });
}

function displayNotificationButton() {
    const notificationArea = document.getElementById('push-notification-area');
    const enableButton = document.getElementById('enable-push-notifications');
    
    if (!notificationArea || !enableButton) return;

    notificationArea.style.display = 'block';
    
    enableButton.addEventListener('click', () => {
        askForNotificationPermission();
        enableButton.disabled = true;
        enableButton.textContent = 'Procesando...';
    });
}

function askForNotificationPermission() {
    Notification.requestPermission(status => {
        console.log('Estado del permiso de notificación:', status);
        if (status === 'granted') {
            console.log('Permiso concedido. Suscribiendo al usuario...');
            subscribeUserToPush();
        } else {
            console.warn('Permiso denegado.');
            document.getElementById('enable-push-notifications').textContent = 'Permiso denegado';
        }
    });
}

function subscribeUserToPush() {
    // Función para convertir la clave VAPID de base64 a Uint8Array
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    const applicationServerKey = urlBase64ToUint8Array(window.vapidPublicKey);
    
    window.swRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    })
    .then(subscription => {
        console.log('Usuario suscrito:', subscription);
        // Envía la suscripción al backend de Laravel
        sendSubscriptionToBackend(subscription);
        document.getElementById('push-notification-area').style.display = 'none';
    })
    .catch(err => {
        console.error('Fallo al suscribir al usuario:', err);
        document.getElementById('enable-push-notifications').textContent = 'Error al activar';
        document.getElementById('enable-push-notifications').disabled = false;
    });
}


 //Envía el objeto de suscripción al backend.

function sendSubscriptionToBackend(subscription) {
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');

    fetch('/push-subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            endpoint: subscription.endpoint,
            publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Fallo al enviar suscripción al backend.');
        }
        return response.json();
    })
    .then(data => console.log('Suscripción guardada en backend:', data))
    .catch(err => console.error(err));
}
// Asegúrate de que el CSRF token está en tu layout (Breeze ya lo incluye)
// <meta name="csrf-token" content="{{ csrf_token() }}">