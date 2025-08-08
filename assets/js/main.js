/**
 * LJ-OS Sistema para Lava Jato
 * JavaScript Principal
 */

// Namespace principal
const LavaJato = {
    // Configurações
    config: {
        apiUrl: window.location.origin + '/api/',
        currency: 'BRL',
        dateFormat: 'dd/mm/yyyy',
        timeFormat: 'HH:mm'
    },

    // Inicialização
    init: function() {
        this.bindEvents();
        this.initComponents();
        this.setupAjax();
    },

    // Bind de eventos
    bindEvents: function() {
        // Event listeners globais
        document.addEventListener('DOMContentLoaded', () => {
            this.setupFormatters();
            this.setupModals();
            this.setupTables();
            this.setupSearch();
            this.setupLoadingStates();
        });

        // Event listeners específicos
        this.bindFormEvents();
        this.bindTableEvents();
        this.bindModalEvents();
    },

    // Inicializar componentes
    initComponents: function() {
        // Inicializar tooltips
        this.initTooltips();
        
        // Inicializar datepickers
        this.initDatepickers();
        
        // Inicializar select2 (se disponível)
        this.initSelect2();
    },

    // Setup AJAX
    setupAjax: function() {
        // Interceptor para adicionar token CSRF
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            // Adicionar headers padrão
            options.headers = {
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            };

            // Adicionar token CSRF se disponível
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                options.headers['X-CSRF-TOKEN'] = csrfToken;
            }

            return originalFetch(url, options);
        };
    },

    // Setup de formatadores
    setupFormatters: function() {
        // Formatador de moeda
        document.querySelectorAll('.currency-input').forEach(input => {
            input.addEventListener('input', (e) => {
                this.formatCurrency(e.target);
            });
        });

        // Formatador de telefone
        document.querySelectorAll('.phone-input').forEach(input => {
            input.addEventListener('input', (e) => {
                this.formatPhone(e.target);
            });
        });

        // Formatador de CPF/CNPJ
        document.querySelectorAll('.document-input').forEach(input => {
            input.addEventListener('input', (e) => {
                this.formatDocument(e.target);
            });
        });

        // Formatador de placa
        document.querySelectorAll('.plate-input').forEach(input => {
            input.addEventListener('input', (e) => {
                this.formatPlate(e.target);
            });
        });
    },

    // Setup de modais
    setupModals: function() {
        // Abrir modal
        document.querySelectorAll('[data-modal]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = trigger.getAttribute('data-modal');
                this.openModal(modalId);
            });
        });

        // Fechar modal
        document.querySelectorAll('.modal-close, .modal').forEach(close => {
            close.addEventListener('click', (e) => {
                if (e.target === close) {
                    this.closeModal(close.closest('.modal'));
                }
            });
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    this.closeModal(openModal);
                }
            }
        });
    },

    // Setup de tabelas
    setupTables: function() {
        // Seleção de linhas
        document.querySelectorAll('.table tbody tr').forEach(row => {
            row.addEventListener('click', (e) => {
                if (!e.target.closest('a, button, input')) {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        row.classList.toggle('selected');
                    }
                }
            });
        });

        // Seleção múltipla
        document.querySelectorAll('.select-all').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const table = e.target.closest('.table-container').querySelector('.table');
                const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                    cb.closest('tr').classList.toggle('selected', e.target.checked);
                });
            });
        });
    },

    // Setup de busca
    setupSearch: function() {
        document.querySelectorAll('.search-input').forEach(input => {
            input.addEventListener('input', (e) => {
                this.filterTable(e.target);
            });
        });
    },

    // Setup de estados de loading
    setupLoadingStates: function() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    this.showLoading(submitBtn);
                }
            });
        });
    },

    // Bind eventos de formulário
    bindFormEvents: function() {
        // Validação de formulários
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });

        // Auto-save
        document.querySelectorAll('form[data-autosave]').forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    this.autoSave(form);
                });
            });
        });
    },

    // Bind eventos de tabela
    bindTableEvents: function() {
        // Ordenação
        document.querySelectorAll('.table th[data-sort]').forEach(th => {
            th.addEventListener('click', (e) => {
                this.sortTable(th);
            });
        });

        // Paginação
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadPage(link.href);
            });
        });
    },

    // Bind eventos de modal
    bindModalEvents: function() {
        // Carregar conteúdo via AJAX
        document.querySelectorAll('[data-modal-ajax]').forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const url = trigger.getAttribute('data-modal-ajax');
                const modalId = trigger.getAttribute('data-modal');
                this.loadModalContent(modalId, url);
            });
        });
    },

    // Inicializar tooltips
    initTooltips: function() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target);
            });
            
            element.addEventListener('mouseleave', (e) => {
                this.hideTooltip(e.target);
            });
        });
    },

    // Inicializar datepickers
    initDatepickers: function() {
        // Implementar datepicker se necessário
        // Por enquanto, usar input type="date" nativo
    },

    // Inicializar select2
    initSelect2: function() {
        // Implementar select2 se necessário
        // Por enquanto, usar select nativo
    },

    // Formatadores
    formatCurrency: function(input) {
        let value = input.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        input.value = 'R$ ' + value;
    },

    formatPhone: function(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length === 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length === 10) {
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        input.value = value;
    },

    formatDocument: function(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length === 11) {
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        } else if (value.length === 14) {
            value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        }
        input.value = value;
    },

    formatPlate: function(input) {
        let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (value.length >= 3) {
            value = value.slice(0, 3) + '-' + value.slice(3);
        }
        input.value = value;
    },

    // Modais
    openModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    },

    closeModal: function(modal) {
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    },

    loadModalContent: function(modalId, url) {
        const modal = document.getElementById(modalId);
        if (modal) {
            this.openModal(modalId);
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const content = modal.querySelector('.modal-content');
                    content.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erro ao carregar modal:', error);
                });
        }
    },

    // Tabelas
    filterTable: function(input) {
        const searchTerm = input.value.toLowerCase();
        const table = input.closest('.card').querySelector('.table');
        
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }
    },

    sortTable: function(th) {
        const table = th.closest('.table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const column = th.cellIndex;
        const isAsc = th.classList.contains('sort-asc');

        // Remover classes de ordenação
        table.querySelectorAll('th').forEach(header => {
            header.classList.remove('sort-asc', 'sort-desc');
        });

        // Adicionar classe de ordenação
        th.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

        // Ordenar linhas
        rows.sort((a, b) => {
            const aValue = a.cells[column].textContent.trim();
            const bValue = b.cells[column].textContent.trim();
            
            if (isAsc) {
                return bValue.localeCompare(aValue);
            } else {
                return aValue.localeCompare(bValue);
            }
        });

        // Reordenar linhas na tabela
        rows.forEach(row => tbody.appendChild(row));
    },

    // Formulários
    validateForm: function(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Este campo é obrigatório');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });

        return isValid;
    },

    showFieldError: function(field, message) {
        this.clearFieldError(field);
        field.classList.add('error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    },

    clearFieldError: function(field) {
        field.classList.remove('error');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    },

    autoSave: function(form) {
        const formData = new FormData(form);
        const url = form.getAttribute('action') || window.location.href;
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAlert('Dados salvos automaticamente', 'success');
            }
        })
        .catch(error => {
            console.error('Erro no auto-save:', error);
        });
    },

    // Loading states
    showLoading: function(element) {
        const originalText = element.innerHTML;
        element.setAttribute('data-original-text', originalText);
        element.innerHTML = '<span class="spinner"></span> Processando...';
        element.disabled = true;
    },

    hideLoading: function(element) {
        const originalText = element.getAttribute('data-original-text');
        if (originalText) {
            element.innerHTML = originalText;
            element.removeAttribute('data-original-text');
        }
        element.disabled = false;
    },

    // Alertas
    showAlert: function(message, type = 'success', duration = 5000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} fade-in`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
            ${message}
        `;
        
        const mainContent = document.querySelector('.main-content');
        mainContent.insertBefore(alertDiv, mainContent.firstChild);
        
        setTimeout(() => {
            alertDiv.style.opacity = '0';
            setTimeout(() => {
                alertDiv.remove();
            }, 300);
        }, duration);
    },

    // Tooltips
    showTooltip: function(element) {
        const text = element.getAttribute('data-tooltip');
        if (!text) return;

        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);

        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    },

    hideTooltip: function(element) {
        const tooltip = document.querySelector('.tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    },

    // Utilitários
    confirmDelete: function(message = 'Tem certeza que deseja excluir este item?') {
        return confirm(message);
    },

    formatDate: function(date, format = 'dd/mm/yyyy') {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        
        return format
            .replace('dd', day)
            .replace('mm', month)
            .replace('yyyy', year);
    },

    formatDateTime: function(date) {
        const d = new Date(date);
        return this.formatDate(d) + ' ' + d.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // AJAX helpers
    ajaxRequest: function(url, data, callback) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(callback)
        .catch(error => {
            console.error('Erro na requisição:', error);
            this.showAlert('Erro na requisição. Tente novamente.', 'danger');
        });
    },

    loadPage: function(url) {
        fetch(url)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('.main-content');
                const currentContent = document.querySelector('.main-content');
                
                if (newContent && currentContent) {
                    currentContent.innerHTML = newContent.innerHTML;
                    window.history.pushState({}, '', url);
                }
            })
            .catch(error => {
                console.error('Erro ao carregar página:', error);
                this.showAlert('Erro ao carregar página.', 'danger');
            });
    }
};

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => LavaJato.init());
} else {
    LavaJato.init();
}

// Expor para uso global
window.LavaJato = LavaJato; 