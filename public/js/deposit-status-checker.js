/**
 * ============================================
 * DEPOSIT STATUS CHECKER - Sistema Otimizado de Verificação
 * ============================================
 * Sistema escalável usando Server-Sent Events (SSE) ou Long Polling
 * Reduz carga no servidor e melhora experiência do usuário
 */

const DepositStatusChecker = {
    /**
     * Configurações
     */
    config: {
        method: 'sse', // 'sse' (Server-Sent Events) ou 'long-polling'
        fallbackMethod: 'long-polling',
        pollingInterval: 15000, // 15 segundos (aumentado de 10s)
        exponentialBackoff: true,
        maxBackoffInterval: 60000, // 1 minuto
        maxRetries: 3,
        timeout: 55000, // 55 segundos para long polling
        enableNotifications: true,
        autoRedirect: true,
        redirectDelay: 2000
    },

    /**
     * Estado da verificação
     */
    state: {
        isRunning: false,
        retryCount: 0,
        currentInterval: null,
        eventSource: null,
        lastCheck: null,
        transactionId: null
    },

    /**
     * Inicia verificação de status
     */
    start(transactionId, options = {}) {
        if (this.state.isRunning) {
            console.warn('Status checker already running');
            return;
        }

        // Mesclar opções
        this.config = { ...this.config, ...options };
        this.state.transactionId = transactionId;
        this.state.isRunning = true;
        this.state.retryCount = 0;

        console.log(`Starting deposit status checker for ${transactionId}`);

        // Tentar SSE primeiro, depois fallback para long polling
        if (this.config.method === 'sse' && this.isSSESupported()) {
            this.startSSE();
        } else {
            this.startLongPolling();
        }
    },

    /**
     * Para verificação
     */
    stop() {
        console.log('Stopping deposit status checker');
        this.state.isRunning = false;

        // Limpar EventSource
        if (this.state.eventSource) {
            this.state.eventSource.close();
            this.state.eventSource = null;
        }

        // Limpar intervalos
        if (this.state.currentInterval) {
            clearInterval(this.state.currentInterval);
            this.state.currentInterval = null;
        }
    },

    /**
     * Verifica suporte a SSE
     */
    isSSESupported() {
        return typeof EventSource !== 'undefined';
    },

    /**
     * Inicia verificação via Server-Sent Events
     */
    startSSE() {
        const url = this.getSSEUrl();
        
        console.log('Starting SSE connection:', url);

        try {
            this.state.eventSource = new EventSource(url);

            // Evento de mensagem
            this.state.eventSource.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleStatusUpdate(data);
                } catch (error) {
                    console.error('Error parsing SSE data:', error);
                }
            };

            // Evento de erro
            this.state.eventSource.onerror = (error) => {
                console.error('SSE connection error:', error);
                
                // Fechar conexão
                if (this.state.eventSource) {
                    this.state.eventSource.close();
                    this.state.eventSource = null;
                }

                // Tentar reconectar ou usar fallback
                if (this.state.retryCount < this.config.maxRetries) {
                    this.state.retryCount++;
                    console.log(`Retrying SSE connection (${this.state.retryCount}/${this.config.maxRetries})...`);
                    
                    setTimeout(() => {
                        if (this.state.isRunning) {
                            this.startSSE();
                        }
                    }, this.getBackoffDelay());
                } else {
                    console.log('SSE max retries reached, falling back to long polling');
                    this.startLongPolling();
                }
            };

            // Evento personalizado para heartbeat
            this.state.eventSource.addEventListener('heartbeat', (event) => {
                console.log('SSE heartbeat received');
                this.state.lastCheck = Date.now();
            });

        } catch (error) {
            console.error('Error starting SSE:', error);
            this.startLongPolling();
        }
    },

    /**
     * Inicia verificação via Long Polling
     */
    startLongPolling() {
        console.log('Starting long polling');

        const check = async () => {
            if (!this.state.isRunning) return;

            try {
                this.state.lastCheck = Date.now();
                const data = await this.fetchStatus();
                this.handleStatusUpdate(data);

                // Se não estiver concluído, agendar próxima verificação
                if (!data.is_completed && !data.is_expired && this.state.isRunning) {
                    const delay = this.getPollingInterval();
                    this.state.currentInterval = setTimeout(check, delay);
                }
            } catch (error) {
                console.error('Long polling error:', error);

                // Tentar novamente com backoff
                if (this.state.retryCount < this.config.maxRetries && this.state.isRunning) {
                    this.state.retryCount++;
                    const delay = this.getBackoffDelay();
                    console.log(`Retrying in ${delay}ms (${this.state.retryCount}/${this.config.maxRetries})...`);
                    this.state.currentInterval = setTimeout(check, delay);
                } else {
                    console.error('Max retries reached, stopping checker');
                    this.stop();
                    if (this.config.enableNotifications) {
                        Notify.error('Unable to check deposit status. Please refresh the page.');
                    }
                }
            }
        };

        // Iniciar primeira verificação
        check();
    },

    /**
     * Busca status via API
     */
    async fetchStatus() {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.config.timeout);

        try {
            const response = await fetch(this.getStatusUrl(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Reset retry count on success
            this.state.retryCount = 0;
            
            return data;
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                console.error('Request timeout');
            }
            throw error;
        }
    },

    /**
     * Processa atualização de status
     */
    handleStatusUpdate(data) {
        console.log('Status update received:', data);

        // Atualizar UI se houver elementos
        this.updateUI(data);

        // Se completou ou expirou, parar verificação
        if (data.is_completed || data.is_expired || data.is_paid) {
            this.stop();

            // Notificações
            if (this.config.enableNotifications) {
                if (data.is_completed || data.is_paid) {
                    Notify.success('Payment confirmed! Your balance has been updated.');
                } else if (data.is_expired) {
                    Notify.warning('Deposit has expired. Please create a new deposit.');
                }
            }

            // Redirect automático
            if (this.config.autoRedirect) {
                setTimeout(() => {
                    window.location.reload();
                }, this.config.redirectDelay);
            }
        }
    },

    /**
     * Atualiza elementos da UI
     */
    updateUI(data) {
        // Atualizar badge de status
        const statusBadge = document.querySelector('[data-status-badge]');
        if (statusBadge && data.status) {
            statusBadge.textContent = data.status_label || data.status;
            statusBadge.className = this.getStatusBadgeClass(data.status);
        }

        // Atualizar última verificação
        const lastCheckElement = document.querySelector('[data-last-check]');
        if (lastCheckElement) {
            lastCheckElement.textContent = new Date().toLocaleTimeString();
        }

        // Disparar evento personalizado
        document.dispatchEvent(new CustomEvent('deposit:status-updated', {
            detail: data
        }));
    },

    /**
     * Retorna classe CSS do badge de status
     */
    getStatusBadgeClass(status) {
        const classes = {
            completed: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800',
            paid: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800',
            pending: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800',
            processing: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800',
            failed: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800',
            expired: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800',
            cancelled: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800'
        };
        return classes[status] || classes.pending;
    },

    /**
     * Calcula intervalo de polling com backoff
     */
    getPollingInterval() {
        if (!this.config.exponentialBackoff) {
            return this.config.pollingInterval;
        }

        const baseInterval = this.config.pollingInterval;
        const backoffInterval = Math.min(
            baseInterval * Math.pow(1.5, this.state.retryCount),
            this.config.maxBackoffInterval
        );

        return backoffInterval;
    },

    /**
     * Calcula delay para retry com backoff
     */
    getBackoffDelay() {
        return Math.min(
            1000 * Math.pow(2, this.state.retryCount),
            this.config.maxBackoffInterval
        );
    },

    /**
     * Retorna URL do SSE endpoint
     */
    getSSEUrl() {
        const baseUrl = window.location.origin;
        return `${baseUrl}/deposit/${this.state.transactionId}/stream`;
    },

    /**
     * Retorna URL do status endpoint
     */
    getStatusUrl() {
        const baseUrl = window.location.origin;
        return `${baseUrl}/deposit/${this.state.transactionId}/check-status-long`;
    },

    /**
     * Obtém status atual
     */
    getStatus() {
        return {
            isRunning: this.state.isRunning,
            transactionId: this.state.transactionId,
            lastCheck: this.state.lastCheck,
            retryCount: this.state.retryCount,
            method: this.state.eventSource ? 'sse' : 'long-polling'
        };
    }
};

// Exportar para uso global
window.DepositStatusChecker = DepositStatusChecker;

// Auto-start se houver data-deposit-checker no DOM
document.addEventListener('DOMContentLoaded', () => {
    const checkerElement = document.querySelector('[data-deposit-checker]');
    if (checkerElement) {
        const transactionId = checkerElement.dataset.transactionId;
        const method = checkerElement.dataset.method || 'long-polling';
        
        if (transactionId) {
            DepositStatusChecker.start(transactionId, { method });
        }
    }
});

// Limpar ao sair da página
window.addEventListener('beforeunload', () => {
    if (DepositStatusChecker.state.isRunning) {
        DepositStatusChecker.stop();
    }
});
