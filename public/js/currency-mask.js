/**
 * ============================================
 * CURRENCY MASK - Sistema Global de Formatação
 * ============================================
 * Sistema reutilizável para formatação de moeda USD
 * Uso: data-currency-mask ou CurrencyMask.init()
 */

const CurrencyMask = {
    /**
     * Configurações padrão
     */
    config: {
        locale: 'en-US',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    },

    /**
     * Formata um valor numérico para moeda
     */
    format(value) {
        const numValue = parseFloat(value) || 0;
        return new Intl.NumberFormat(this.config.locale, {
            style: 'currency',
            currency: this.config.currency,
            minimumFractionDigits: this.config.minimumFractionDigits,
            maximumFractionDigits: this.config.maximumFractionDigits
        }).format(numValue);
    },

    /**
     * Remove formatação e retorna apenas números
     */
    unformat(value) {
        if (typeof value === 'number') return value;
        return parseFloat(value.replace(/[^0-9.-]/g, '')) || 0;
    },

    /**
     * Aplica máscara em um input
     */
    applyMask(input) {
        // Guardar valor numérico original
        const numericValue = this.unformat(input.value);
        
        // Criar input oculto para guardar valor numérico
        let hiddenInput = input.parentElement.querySelector('.currency-hidden-value');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = input.name;
            hiddenInput.className = 'currency-hidden-value';
            input.parentElement.appendChild(hiddenInput);
            
            // Remover o name do input visível para evitar duplicação
            input.removeAttribute('name');
        }
        
        // Atualizar valores
        hiddenInput.value = numericValue;
        input.value = this.formatInputValue(numericValue);
        
        // Adicionar classe visual
        input.classList.add('currency-formatted');
        
        // Event listeners
        this.attachEvents(input, hiddenInput);
        
        return { input, hiddenInput, value: numericValue };
    },

    /**
     * Formata valor para exibição no input
     */
    formatInputValue(value) {
        const numValue = parseFloat(value) || 0;
        return numValue.toLocaleString(this.config.locale, {
            minimumFractionDigits: this.config.minimumFractionDigits,
            maximumFractionDigits: this.config.maximumFractionDigits
        });
    },

    /**
     * Anexa eventos ao input
     */
    attachEvents(input, hiddenInput) {
        // Evento de input
        input.addEventListener('input', (e) => {
            let value = e.target.value;
            
            // Permitir apenas números, ponto e vírgula
            value = value.replace(/[^\d.,]/g, '');
            
            // Substituir vírgula por ponto
            value = value.replace(/,/g, '.');
            
            // Permitir apenas um ponto decimal
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limitar casas decimais
            if (parts.length === 2 && parts[1].length > this.config.maximumFractionDigits) {
                value = parts[0] + '.' + parts[1].substring(0, this.config.maximumFractionDigits);
            }
            
            const numericValue = parseFloat(value) || 0;
            hiddenInput.value = numericValue;
            
            // Atualizar atributos de validação do input oculto
            if (input.hasAttribute('min')) {
                hiddenInput.setAttribute('min', input.getAttribute('min'));
            }
            if (input.hasAttribute('max')) {
                hiddenInput.setAttribute('max', input.getAttribute('max'));
            }
            if (input.hasAttribute('required')) {
                hiddenInput.setAttribute('required', 'required');
            }
        });

        // Evento de blur para formatar
        input.addEventListener('blur', (e) => {
            const numericValue = this.unformat(e.target.value);
            e.target.value = this.formatInputValue(numericValue);
            hiddenInput.value = numericValue;
            
            // Validar limites
            this.validateLimits(input, hiddenInput, numericValue);
        });

        // Evento de focus para remover formatação
        input.addEventListener('focus', (e) => {
            const numericValue = this.unformat(e.target.value);
            e.target.value = numericValue === 0 ? '' : numericValue.toString();
            e.target.select();
        });

        // Prevenir colagem de valores não numéricos
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericValue = this.unformat(pastedText);
            e.target.value = numericValue.toString();
            hiddenInput.value = numericValue;
            e.target.dispatchEvent(new Event('input', { bubbles: true }));
        });
    },

    /**
     * Valida limites min/max
     */
    validateLimits(input, hiddenInput, value) {
        const min = parseFloat(input.getAttribute('min'));
        const max = parseFloat(input.getAttribute('max'));
        
        if (!isNaN(min) && value < min) {
            hiddenInput.value = min;
            input.value = this.formatInputValue(min);
            this.showValidationError(input, `Minimum value is ${this.format(min)}`);
        } else if (!isNaN(max) && value > max) {
            hiddenInput.value = max;
            input.value = this.formatInputValue(max);
            this.showValidationError(input, `Maximum value is ${this.format(max)}`);
        } else {
            this.clearValidationError(input);
        }
    },

    /**
     * Mostra erro de validação
     */
    showValidationError(input, message) {
        input.classList.add('border-red-500');
        
        let errorDiv = input.parentElement.querySelector('.currency-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'currency-error text-red-500 text-sm mt-1';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    },

    /**
     * Remove erro de validação
     */
    clearValidationError(input) {
        input.classList.remove('border-red-500');
        const errorDiv = input.parentElement.querySelector('.currency-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    },

    /**
     * Inicializa todos os inputs com data-currency-mask
     */
    init() {
        const inputs = document.querySelectorAll('[data-currency-mask]');
        inputs.forEach(input => {
            if (!input.classList.contains('currency-formatted')) {
                this.applyMask(input);
            }
        });
    },

    /**
     * Inicializa um input específico
     */
    initElement(selector) {
        const input = typeof selector === 'string' 
            ? document.querySelector(selector)
            : selector;
            
        if (input && !input.classList.contains('currency-formatted')) {
            return this.applyMask(input);
        }
        return null;
    },

    /**
     * Remove máscara de um input
     */
    removeMask(input) {
        const hiddenInput = input.parentElement.querySelector('.currency-hidden-value');
        if (hiddenInput) {
            input.name = hiddenInput.name;
            hiddenInput.remove();
        }
        
        input.classList.remove('currency-formatted');
        this.clearValidationError(input);
    }
};

// Auto-inicialização quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => CurrencyMask.init());
} else {
    CurrencyMask.init();
}

// Observer para detectar novos inputs adicionados dinamicamente
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (node.nodeType === 1) { // Element node
                if (node.hasAttribute && node.hasAttribute('data-currency-mask')) {
                    CurrencyMask.applyMask(node);
                }
                // Buscar inputs dentro do nó adicionado
                const inputs = node.querySelectorAll ? node.querySelectorAll('[data-currency-mask]') : [];
                inputs.forEach(input => {
                    if (!input.classList.contains('currency-formatted')) {
                        CurrencyMask.applyMask(input);
                    }
                });
            }
        });
    });
});

// Iniciar observação
observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Exportar para uso global
window.CurrencyMask = CurrencyMask;
