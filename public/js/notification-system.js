/**
 * ============================================
 * NOTIFICATION SYSTEM - Sistema Global de Notificações
 * ============================================
 * Sistema moderno e reutilizável para notificações toast
 * Uso: Notify.success('message'), Notify.error('message'), etc.
 */

const Notify = {
    /**
     * Configurações padrão
     */
    config: {
        position: 'top-right', // top-right, top-left, bottom-right, bottom-left, top-center, bottom-center
        duration: 5000, // duração em ms
        maxNotifications: 5, // máximo de notificações simultâneas
        pauseOnHover: true,
        closeButton: true,
        progressBar: true,
        animation: 'slide', // slide, fade, bounce
        sound: false
    },

    /**
     * Tipos de notificação
     */
    types: {
        success: {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>',
            bg: 'bg-success-500/20',
            border: 'border-success-500/30',
            text: 'text-success-400',
            progressBg: 'bg-success-500'
        },
        error: {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>',
            bg: 'bg-danger-500/20',
            border: 'border-danger-500/30',
            text: 'text-danger-400',
            progressBg: 'bg-danger-500'
        },
        warning: {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>',
            bg: 'bg-warning-500/20',
            border: 'border-warning-500/30',
            text: 'text-warning-400',
            progressBg: 'bg-warning-500'
        },
        info: {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
            bg: 'bg-primary-500/20',
            border: 'border-primary-500/30',
            text: 'text-primary-400',
            progressBg: 'bg-primary-500'
        }
    },

    /**
     * Container de notificações
     */
    container: null,

    /**
     * Contador de notificações ativas
     */
    activeNotifications: 0,

    /**
     * Inicializa o container
     */
    initContainer() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'notify-container';
            this.container.className = this.getPositionClass();
            document.body.appendChild(this.container);
        }
    },

    /**
     * Retorna classe CSS baseada na posição
     */
    getPositionClass() {
        const positions = {
            'top-right': 'fixed top-20 right-4 z-[9999] space-y-4',
            'top-left': 'fixed top-20 left-4 z-[9999] space-y-4',
            'bottom-right': 'fixed bottom-4 right-4 z-[9999] space-y-4',
            'bottom-left': 'fixed bottom-4 left-4 z-[9999] space-y-4',
            'top-center': 'fixed top-20 left-1/2 -translate-x-1/2 z-[9999] space-y-4',
            'bottom-center': 'fixed bottom-4 left-1/2 -translate-x-1/2 z-[9999] space-y-4'
        };
        return positions[this.config.position] || positions['top-right'];
    },

    /**
     * Exibe uma notificação
     */
    show(message, type = 'info', options = {}) {
        this.initContainer();

        // Limitar número de notificações
        if (this.activeNotifications >= this.config.maxNotifications) {
            const firstNotification = this.container.firstChild;
            if (firstNotification) {
                this.remove(firstNotification);
            }
        }

        // Mesclar opções
        const opts = { ...this.config, ...options };
        const typeConfig = this.types[type] || this.types.info;

        // Criar elemento da notificação
        const notification = document.createElement('div');
        const notificationId = 'notify-' + Date.now() + Math.random();
        notification.id = notificationId;
        notification.className = this.getNotificationClass(opts.animation);

        // HTML da notificação
        notification.innerHTML = `
            <div class="glass-effect border ${typeConfig.border} rounded-xl p-4 shadow-2xl max-w-sm w-full ${this.getAnimationClass(opts.animation, 'in')}">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 rounded-lg ${typeConfig.bg} flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 ${typeConfig.text}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${typeConfig.icon}
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold ${typeConfig.text} capitalize">${type}</p>
                        <p class="text-sm text-gray-300 break-words">${this.escapeHtml(message)}</p>
                    </div>
                    ${opts.closeButton ? `
                    <button onclick="Notify.close('${notificationId}')" class="flex-shrink-0 text-gray-400 hover:text-white transition touch-manipulation">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    ` : ''}
                </div>
                ${opts.progressBar ? `
                <div class="mt-3 h-1 bg-gray-700/30 rounded-full overflow-hidden">
                    <div class="h-full ${typeConfig.progressBg} rounded-full notify-progress" style="width: 100%"></div>
                </div>
                ` : ''}
            </div>
        `;

        this.container.appendChild(notification);
        this.activeNotifications++;

        // Progress bar animation
        if (opts.progressBar) {
            const progressBar = notification.querySelector('.notify-progress');
            progressBar.style.transition = `width ${opts.duration}ms linear`;
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 10);
        }

        // Pause on hover
        if (opts.pauseOnHover) {
            let timeoutId = null;
            let remainingTime = opts.duration;
            let startTime = Date.now();

            const autoClose = () => {
                timeoutId = setTimeout(() => {
                    this.remove(notification);
                }, remainingTime);
            };

            notification.addEventListener('mouseenter', () => {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                    remainingTime -= (Date.now() - startTime);
                    
                    // Pausar animação da progress bar
                    const progressBar = notification.querySelector('.notify-progress');
                    if (progressBar) {
                        const computedStyle = window.getComputedStyle(progressBar);
                        const currentWidth = computedStyle.width;
                        progressBar.style.transition = 'none';
                        progressBar.style.width = currentWidth;
                    }
                }
            });

            notification.addEventListener('mouseleave', () => {
                startTime = Date.now();
                
                // Retomar animação da progress bar
                const progressBar = notification.querySelector('.notify-progress');
                if (progressBar && remainingTime > 0) {
                    progressBar.style.transition = `width ${remainingTime}ms linear`;
                    progressBar.style.width = '0%';
                }
                
                autoClose();
            });

            autoClose();
        } else {
            // Auto-close sem pause
            setTimeout(() => {
                this.remove(notification);
            }, opts.duration);
        }

        // Tocar som se habilitado
        if (opts.sound) {
            this.playSound(type);
        }

        return notificationId;
    },

    /**
     * Retorna classe base da notificação
     */
    getNotificationClass(animation) {
        return 'notify-item';
    },

    /**
     * Retorna classe de animação
     */
    getAnimationClass(animation, direction) {
        const animations = {
            slide: {
                in: 'animate-slide-in-right',
                out: 'animate-slide-out-right'
            },
            fade: {
                in: 'animate-fade-in',
                out: 'animate-fade-out'
            },
            bounce: {
                in: 'animate-bounce-in',
                out: 'animate-fade-out'
            }
        };

        return animations[animation]?.[direction] || animations.slide[direction];
    },

    /**
     * Remove uma notificação
     */
    remove(notification) {
        if (!notification || !notification.parentNode) return;

        const content = notification.querySelector('.glass-effect');
        if (content) {
            content.classList.add(this.getAnimationClass(this.config.animation, 'out'));
        }

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
                this.activeNotifications--;
            }
        }, 300);
    },

    /**
     * Fecha notificação por ID
     */
    close(notificationId) {
        const notification = document.getElementById(notificationId);
        if (notification) {
            this.remove(notification);
        }
    },

    /**
     * Remove todas as notificações
     */
    clearAll() {
        if (this.container) {
            while (this.container.firstChild) {
                this.container.removeChild(this.container.firstChild);
            }
            this.activeNotifications = 0;
        }
    },

    /**
     * Escape HTML para prevenir XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Toca som de notificação
     */
    playSound(type) {
        // Implementar sons se necessário
        // const audio = new Audio(`/sounds/${type}.mp3`);
        // audio.play().catch(() => {});
    },

    /**
     * Atalhos para tipos de notificação
     */
    success(message, options = {}) {
        return this.show(message, 'success', options);
    },

    error(message, options = {}) {
        return this.show(message, 'error', options);
    },

    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    },

    info(message, options = {}) {
        return this.show(message, 'info', options);
    },

    /**
     * Configurar opções globais
     */
    configure(options) {
        this.config = { ...this.config, ...options };
        
        // Atualizar posição do container se existir
        if (this.container) {
            this.container.className = this.getPositionClass();
        }
    }
};

// CSS para animações
const notifyStyles = document.createElement('style');
notifyStyles.textContent = `
    @keyframes slide-in-right {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slide-out-right {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    @keyframes fade-in {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes fade-out {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.9);
        }
    }

    @keyframes bounce-in {
        0% {
            transform: scale(0.3);
            opacity: 0;
        }
        50% {
            transform: scale(1.05);
        }
        70% {
            transform: scale(0.9);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .animate-slide-in-right {
        animation: slide-in-right 0.3s ease-out;
    }

    .animate-slide-out-right {
        animation: slide-out-right 0.3s ease-in;
    }

    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }

    .animate-fade-out {
        animation: fade-out 0.3s ease-in;
    }

    .animate-bounce-in {
        animation: bounce-in 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    /* Responsivo */
    @media (max-width: 640px) {
        #notify-container {
            left: 1rem !important;
            right: 1rem !important;
            transform: none !important;
        }
        
        .glass-effect {
            max-width: 100% !important;
        }
    }
`;
document.head.appendChild(notifyStyles);

// Substituir função global showNotification se existir
window.showNotification = function(message, type = 'info') {
    return Notify.show(message, type);
};

// Exportar para uso global
window.Notify = Notify;
