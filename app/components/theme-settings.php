<?php
/**
 * Componente de Configurações de Tema
 * Incluir este arquivo nas páginas onde você quer mostrar as configurações
 */

// Carregar o sistema de localização
require_once __DIR__ . '/../../autoload.php';
$localization = LJOS\Utils\Localization::getInstance();
?>

<!-- Configurações de Tema e Idioma -->
<div class="theme-settings-panel">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-cog"></i> 
                <?= $localization->get('settings') ?>
            </h5>
        </div>
        <div class="card-body">
            <!-- Controles Rápidos -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-center gap-2">
                        <!-- Toggle de Tema -->
                        <button class="btn btn-outline-primary theme-toggle" title="<?= $localization->get('theme') ?>">
                            <i class="fas fa-moon"></i>
                        </button>
                        
                        <!-- Toggle de Contraste -->
                        <button class="btn btn-outline-secondary contrast-toggle" title="<?= $localization->get('contrast') ?>">
                            <i class="fas fa-adjust"></i>
                        </button>
                        
                        <!-- Toggle de Idioma -->
                        <button class="btn btn-outline-info language-toggle" title="<?= $localization->get('language') ?>">
                            <i class="fas fa-flag"></i>
                        </button>
                        
                        <!-- Toggle de Tamanho da Fonte -->
                        <button class="btn btn-outline-warning font-size-toggle" title="<?= $localization->get('font_size') ?>">
                            <i class="fas fa-text-height"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Configurações Detalhadas -->
            <div class="row">
                <!-- Tema -->
                <div class="col-md-6 mb-3">
                    <label for="theme-select" class="form-label">
                        <i class="fas fa-palette"></i> <?= $localization->get('theme') ?>
                    </label>
                    <select class="form-select" id="theme-select">
                        <option value="light"><?= $localization->get('light_mode') ?></option>
                        <option value="dark"><?= $localization->get('dark_mode') ?></option>
                    </select>
                </div>
                
                <!-- Contraste -->
                <div class="col-md-6 mb-3">
                    <label for="contrast-select" class="form-label">
                        <i class="fas fa-adjust"></i> <?= $localization->get('contrast') ?>
                    </label>
                    <select class="form-select" id="contrast-select">
                        <option value="low"><?= $localization->get('low_contrast') ?></option>
                        <option value="normal"><?= $localization->get('normal_contrast') ?></option>
                        <option value="high"><?= $localization->get('high_contrast') ?></option>
                    </select>
                </div>
                
                <!-- Idioma -->
                <div class="col-md-6 mb-3">
                    <label for="language-select" class="form-label">
                        <i class="fas fa-globe"></i> <?= $localization->get('language') ?>
                    </label>
                    <select class="form-select" id="language-select">
                        <option value="pt-BR">Português (Brasil)</option>
                        <option value="en-US">English (US)</option>
                    </select>
                </div>
                
                <!-- Tamanho da Fonte -->
                <div class="col-md-6 mb-3">
                    <label for="font-size-select" class="form-label">
                        <i class="fas fa-text-height"></i> <?= $localization->get('font_size') ?>
                    </label>
                    <select class="form-select" id="font-size-select">
                        <option value="small"><?= $localization->get('small') ?></option>
                        <option value="medium"><?= $localization->get('medium') ?></option>
                        <option value="large"><?= $localization->get('large') ?></option>
                    </select>
                </div>
            </div>
            
            <!-- Botões de Ação -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-secondary btn-sm" onclick="resetThemeSettings()">
                            <i class="fas fa-undo"></i> <?= $localization->get('reset') ?? 'Resetar' ?>
                        </button>
                        
                        <button class="btn btn-primary btn-sm" onclick="saveThemeSettings()">
                            <i class="fas fa-save"></i> <?= $localization->get('save') ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para inicializar as configurações -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Definir valores iniciais dos seletores
    const currentSettings = <?= json_encode($localization->getCurrentSettings()) ?>;
    
    if (currentSettings.theme) {
        document.getElementById('theme-select').value = currentSettings.theme;
    }
    
    if (currentSettings.contrast) {
        document.getElementById('contrast-select').value = currentSettings.contrast;
    }
    
    if (currentSettings.language) {
        document.getElementById('language-select').value = currentSettings.language;
    }
    
    if (currentSettings.font_size) {
        document.getElementById('font-size-select').value = currentSettings.font_size;
    }
});

// Função para resetar configurações
function resetThemeSettings() {
    if (window.themeManager) {
        window.themeManager.resetToDefaults();
    }
}

// Função para salvar configurações
function saveThemeSettings() {
    if (window.themeManager) {
        window.themeManager.saveToServer();
        
        // Mostrar mensagem de sucesso
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-check-circle"></i> 
            <?= $localization->get('operation_success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Inserir no topo da página
        document.body.insertBefore(alert, document.body.firstChild);
        
        // Remover automaticamente após 3 segundos
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 3000);
    }
}
</script>

<!-- Estilos específicos para o painel de configurações -->
<style>
.theme-settings-panel {
    max-width: 600px;
    margin: 0 auto;
}

.theme-settings-panel .card {
    border: 1px solid var(--border-color);
    background-color: var(--bg-card);
}

.theme-settings-panel .card-header {
    background-color: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}

.theme-settings-panel .form-label {
    color: var(--text-primary);
    font-weight: 500;
}

.theme-settings-panel .form-select {
    background-color: var(--bg-primary);
    border-color: var(--border-color);
    color: var(--text-primary);
}

.theme-settings-panel .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.theme-settings-panel .btn {
    transition: all 0.3s ease;
}

.theme-settings-panel .btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

/* Responsividade */
@media (max-width: 768px) {
    .theme-settings-panel {
        margin: 0 1rem;
    }
    
    .theme-settings-panel .d-flex.justify-content-center.gap-2 {
        flex-wrap: wrap;
    }
    
    .theme-settings-panel .btn {
        margin-bottom: 0.5rem;
    }
}
</style>
