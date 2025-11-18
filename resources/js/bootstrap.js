import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;


window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    encrypted: true,
    disableStats: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
    },
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('🟢 WebSocket connected successfully');
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('🔴 WebSocket disconnected');
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ WebSocket error:', err);
});

if (window.userId) {
    window.Echo.private(`user.${window.userId}`)
        .listen('TradeExecuted', (e) => {
            console.log('Trade executed:', e);
            // Trigger custom event for Livewire components or vanilla JS
            window.dispatchEvent(new CustomEvent('trade-executed', { detail: e }));
        })
        .listen('OpportunityDetected', (e) => {
            console.log('Opportunity detected:', e);
            window.dispatchEvent(new CustomEvent('opportunity-detected', { detail: e }));
        })
        .listen('ProfitGenerated', (e) => {
            console.log('Profit generated:', e);
            window.dispatchEvent(new CustomEvent('profit-generated', { detail: e }));
        })
        .listen('BotStatusChanged', (e) => {
            console.log('Bot status changed:', e);
            window.dispatchEvent(new CustomEvent('bot-status-changed', { detail: e }));
        });
}

export default window.Echo;
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
