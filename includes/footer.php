            </main>
        </div>
    </div>

    <!-- Scripts JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Script específico da página -->
    <?php if (isset($page_script)): ?>
        <script src="<?php echo $page_script; ?>"></script>
    <?php endif; ?>
    
    <!-- Script inline para funcionalidades específicas -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            });
        }, 5000);

        // Confirm delete actions
        function confirmDelete(message = 'Tem certeza que deseja excluir este item?') {
            return confirm(message);
        }

        // Format currency inputs
        function formatCurrency(input) {
            let value = input.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            input.value = 'R$ ' + value;
        }

        // Format phone inputs
        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length === 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length === 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
            input.value = value;
        }

        // Format CPF/CNPJ inputs
        function formatDocument(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length === 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if (value.length === 14) {
                value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
            }
            input.value = value;
        }

        // Initialize formatters
        document.addEventListener('DOMContentLoaded', function() {
            // Currency formatters
            const currencyInputs = document.querySelectorAll('.currency-input');
            currencyInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    formatCurrency(this);
                });
            });

            // Phone formatters
            const phoneInputs = document.querySelectorAll('.phone-input');
            phoneInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    formatPhone(this);
                });
            });

            // Document formatters
            const documentInputs = document.querySelectorAll('.document-input');
            documentInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    formatDocument(this);
                });
            });

            // Modal functionality
            const modalTriggers = document.querySelectorAll('[data-modal]');
            modalTriggers.forEach(function(trigger) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    const modalId = this.getAttribute('data-modal');
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.classList.add('show');
                    }
                });
            });

            const modalCloses = document.querySelectorAll('.modal-close, .modal');
            modalCloses.forEach(function(close) {
                close.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('show');
                    }
                });
            });

            // Table row selection
            const tableRows = document.querySelectorAll('.table tbody tr');
            tableRows.forEach(function(row) {
                row.addEventListener('click', function() {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        this.classList.toggle('selected');
                    }
                });
            });

            // Search functionality
            const searchInputs = document.querySelectorAll('.search-input');
            searchInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const table = this.closest('.card').querySelector('.table');
                    if (table) {
                        const rows = table.querySelectorAll('tbody tr');
                        rows.forEach(function(row) {
                            const text = row.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }
                });
            });

            // Loading states
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner"></span> Processando...';
                    }
                });
            });
        });

        // Utility functions
        function showLoading(element) {
            element.innerHTML = '<span class="spinner"></span> Carregando...';
            element.disabled = true;
        }

        function hideLoading(element, originalText) {
            element.innerHTML = originalText;
            element.disabled = false;
        }

        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} fade-in`;
            alertDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i> ${message}`;
            
            const mainContent = document.querySelector('.main-content');
            mainContent.insertBefore(alertDiv, mainContent.firstChild);
            
            setTimeout(function() {
                alertDiv.style.opacity = '0';
                setTimeout(function() {
                    alertDiv.remove();
                }, 300);
            }, 5000);
        }

        // AJAX helper
        function ajaxRequest(url, data, callback) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(callback)
            .catch(error => {
                console.error('Error:', error);
                showAlert('Erro na requisição. Tente novamente.', 'danger');
            });
        }
    </script>
</body>
</html> 