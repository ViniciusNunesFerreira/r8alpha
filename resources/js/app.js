import './bootstrap';
import Alpine from 'alpinejs';

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Global helper functions
window.formatCurrency = function(value, decimals = 2) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(value);
};

window.formatNumber = function(value, decimals = 2) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(value);
};

window.formatPercentage = function(value, decimals = 2) {
    return new Intl.NumberFormat('en-US', {
        style: 'percent',
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(value / 100);
};

window.formatDate = function(date, format = 'short') {
    const d = new Date(date);
    const options = {
        short: { year: 'numeric', month: 'short', day: 'numeric' },
        long: { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' },
        time: { hour: '2-digit', minute: '2-digit', second: '2-digit' },
    };
    return new Intl.DateTimeFormat('en-US', options[format] || options.short).format(d);
};

// Copy to clipboard
window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Usa a função de notificação definida em app.blade.php
        window.showNotification('success', 'Copied to clipboard!');
    }).catch(() => {
        window.showNotification('error', 'Failed to copy');
    });
};

// Confirm dialog
window.confirmAction = function(message, callback) {
    if (confirm(message)) {
        callback();
    }
};

// Livewire event listeners
document.addEventListener('livewire:load', function () {
    console.log('✅ Livewire loaded successfully');
});

// Handle Livewire notifications
window.addEventListener('notification', event => {
    const data = event.detail;
    // Usa a função de notificação definida em app.blade.php
    window.showNotification(data.type || 'info', data.message || 'Notification', data.duration || 5000);
});

// Handle network errors
window.addEventListener('online', () => {
    window.showNotification('success', 'Connection restored', 3000);
});

window.addEventListener('offline', () => {
    window.showNotification('error', 'Connection lost', 0);
});

// Prevent double form submissions
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.hasAttribute('data-submitting')) {
        e.preventDefault();
        return false;
    }
    form.setAttribute('data-submitting', 'true');
    setTimeout(() => form.removeAttribute('data-submitting'), 3000);
});

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert[data-auto-hide]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('opacity-0');
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Console welcome message
console.log('%c🚀 Crypto Arbitrage Platform', 'color: #6366f1; font-size: 20px; font-weight: bold;');
console.log('%cBuilt with Laravel + Livewire + Tailwind CSS', 'color: #8b5cf6; font-size: 12px;');
console.log('%c⚠️ Warning: Be careful when pasting code in the console!', 'color: #ef4444; font-size: 14px; font-weight: bold;');
