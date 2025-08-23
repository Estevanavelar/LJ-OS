/**
 * LJ-OS - Gerenciador de Temas e Idiomas
 */

class ThemeManager {
    constructor() {
        this.currentTheme = 'light';
        this.currentContrast = 'normal';
        this.currentLanguage = 'pt-BR';
        this.currentFontSize = 'medium';
        
        this.init();
    }
    
    init() {
        this.loadSettings();
        this.setupEventListeners();
        this.applySettings();
    }
    
    /**
     * Carrega as configurações salvas
     */
    loadSettings() {
        // Carregar do localStorage
        this.currentTheme = localStorage.getItem('lj_os_theme') || 'light';
        this.currentContrast = localStorage.getItem('lj_os_contrast') || 'normal';
        this.currentLanguage = localStorage.getItem('lj_os_language') || 'pt-BR';
        this.currentFontSize = localStorage.getItem('lj_os_font_size') || 'medium';
        
        // Aplicar ao HTML
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        document.documentElement.setAttribute('data-contrast', this.currentContrast);
        document.documentElement.setAttribute('data-font-size', this.currentFontSize);
        document.documentElement.setAttribute('lang', this.currentLanguage);
    }
    
    /**
     * Configura os event listeners
     */
    setupEventListeners() {
        // Toggle de tema
        const themeToggles = document.querySelectorAll('.theme-toggle');
        themeToggles.forEach(toggle => {
            toggle.addEventListener('click', () => this.toggleTheme());
        });
        
        // Toggle de contraste
        const contrastToggles = document.querySelectorAll('.contrast-toggle');
        contrastToggles.forEach(toggle => {
            toggle.addEventListener('click', () => this.toggleContrast());
        });
        
        // Toggle de idioma
        const languageToggles = document.querySelectorAll('.language-toggle');
        languageToggles.forEach(toggle => {
            toggle.addEventListener('click', () => this.toggleLanguage());
        });
        
        // Toggle de tamanho da fonte
        const fontSizeToggles = document.querySelectorAll('.font-size-toggle');
        fontSizeToggles.forEach(toggle => {
            toggle.addEventListener('click', () => this.toggleFontSize());
        });
        
        // Seletores de configuração
        const themeSelect = document.getElementById('theme-select');
        if (themeSelect) {
            themeSelect.addEventListener('change', (e) => this.setTheme(e.target.value));
        }
        
        const contrastSelect = document.getElementById('contrast-select');
        if (contrastSelect) {
            contrastSelect.addEventListener('change', (e) => this.setContrast(e.target.value));
        }
        
        const languageSelect = document.getElementById('language-select');
        if (languageSelect) {
            languageSelect.addEventListener('change', (e) => this.setLanguage(e.target.value));
        }
        
        const fontSizeSelect = document.getElementById('font-size-select');
        if (fontSizeSelect) {
            fontSizeSelect.addEventListener('change', (e) => this.setFontSize(e.target.value));
        }
    }
    
    /**
     * Alterna entre tema claro e escuro
     */
    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.setTheme(newTheme);
    }
    
    /**
     * Define o tema
     */
    setTheme(theme) {
        if (['light', 'dark'].includes(theme)) {
            this.currentTheme = theme;
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('lj_os_theme', theme);
            
            // Atualizar seletores
            this.updateSelectors();
            
            // Disparar evento personalizado
            this.dispatchThemeChangeEvent();
            
            // Salvar no servidor se necessário
            this.saveToServer();
        }
    }
    
    /**
     * Alterna entre contrastes
     */
    toggleContrast() {
        const contrasts = ['low', 'normal', 'high'];
        const currentIndex = contrasts.indexOf(this.currentContrast);
        const nextIndex = (currentIndex + 1) % contrasts.length;
        this.setContrast(contrasts[nextIndex]);
    }
    
    /**
     * Define o contraste
     */
    setContrast(contrast) {
        if (['low', 'normal', 'high'].includes(contrast)) {
            this.currentContrast = contrast;
            document.documentElement.setAttribute('data-contrast', contrast);
            localStorage.setItem('lj_os_contrast', contrast);
            
            // Atualizar seletores
            this.updateSelectors();
            
            // Disparar evento personalizado
            this.dispatchContrastChangeEvent();
            
            // Salvar no servidor se necessário
            this.saveToServer();
        }
    }
    
    /**
     * Alterna entre idiomas
     */
    toggleLanguage() {
        const newLanguage = this.currentLanguage === 'pt-BR' ? 'en-US' : 'pt-BR';
        this.setLanguage(newLanguage);
    }
    
    /**
     * Define o idioma
     */
    setLanguage(language) {
        if (['pt-BR', 'en-US'].includes(language)) {
            this.currentLanguage = language;
            document.documentElement.setAttribute('lang', language);
            localStorage.setItem('lj_os_language', language);
            
            // Atualizar seletores
            this.updateSelectors();
            
            // Recarregar a página para aplicar as traduções
            this.reloadPage();
        }
    }
    
    /**
     * Alterna entre tamanhos de fonte
     */
    toggleFontSize() {
        const sizes = ['small', 'medium', 'large'];
        const currentIndex = sizes.indexOf(this.currentFontSize);
        const nextIndex = (currentIndex + 1) % sizes.length;
        this.setFontSize(sizes[nextIndex]);
    }
    
    /**
     * Define o tamanho da fonte
     */
    setFontSize(fontSize) {
        if (['small', 'medium', 'large'].includes(fontSize)) {
            this.currentFontSize = fontSize;
            document.documentElement.setAttribute('data-font-size', fontSize);
            localStorage.setItem('lj_os_font_size', fontSize);
            
            // Atualizar seletores
            this.updateSelectors();
            
            // Disparar evento personalizado
            this.dispatchFontSizeChangeEvent();
            
            // Salvar no servidor se necessário
            this.saveToServer();
        }
    }
    
    /**
     * Atualiza os seletores na interface
     */
    updateSelectors() {
        // Atualizar tema
        const themeSelect = document.getElementById('theme-select');
        if (themeSelect) {
            themeSelect.value = this.currentTheme;
        }
        
        // Atualizar contraste
        const contrastSelect = document.getElementById('contrast-select');
        if (contrastSelect) {
            contrastSelect.value = this.currentContrast;
        }
        
        // Atualizar idioma
        const languageSelect = document.getElementById('language-select');
        if (languageSelect) {
            languageSelect.value = this.currentLanguage;
        }
        
        // Atualizar tamanho da fonte
        const fontSizeSelect = document.getElementById('font-size-select');
        if (fontSizeSelect) {
            fontSizeSelect.value = this.currentFontSize;
        }
        
        // Atualizar ícones dos toggles
        this.updateToggleIcons();
    }
    
    /**
     * Atualiza os ícones dos toggles
     */
    updateToggleIcons() {
        // Toggle de tema
        const themeToggles = document.querySelectorAll('.theme-toggle i');
        themeToggles.forEach(icon => {
            if (this.currentTheme === 'light') {
                icon.className = 'fas fa-moon';
                icon.title = 'Alternar para modo escuro';
            } else {
                icon.className = 'fas fa-sun';
                icon.title = 'Alternar para modo claro';
            }
        });
        
        // Toggle de contraste
        const contrastToggles = document.querySelectorAll('.contrast-toggle i');
        contrastToggles.forEach(icon => {
            if (this.currentContrast === 'high') {
                icon.className = 'fas fa-adjust';
                icon.title = 'Contraste alto';
            } else if (this.currentContrast === 'normal') {
                icon.className = 'fas fa-adjust';
                icon.title = 'Contraste normal';
            } else {
                icon.className = 'fas fa-adjust';
                icon.title = 'Contraste baixo';
            }
        });
        
        // Toggle de idioma
        const languageToggles = document.querySelectorAll('.language-toggle i');
        languageToggles.forEach(icon => {
            if (this.currentLanguage === 'pt-BR') {
                icon.className = 'fas fa-flag';
                icon.title = 'Português';
            } else {
                icon.className = 'fas fa-flag';
                icon.title = 'English';
            }
        });
    }
    
    /**
     * Aplica as configurações
     */
    applySettings() {
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        document.documentElement.setAttribute('data-contrast', this.currentContrast);
        document.documentElement.setAttribute('data-font-size', this.currentFontSize);
        document.documentElement.setAttribute('lang', this.currentLanguage);
        
        this.updateToggleIcons();
    }
    
    /**
     * Salva as configurações no servidor
     */
    saveToServer() {
        const settings = {
            theme: this.currentTheme,
            contrast: this.currentContrast,
            language: this.currentLanguage,
            font_size: this.currentFontSize
        };
        
        // Enviar para o servidor via AJAX
        fetch('/api/settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + this.getAuthToken()
            },
            body: JSON.stringify(settings)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Configurações salvas no servidor');
            }
        })
        .catch(error => {
            console.error('Erro ao salvar configurações:', error);
        });
    }
    
    /**
     * Obtém o token de autenticação
     */
    getAuthToken() {
        return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token') || '';
    }
    
    /**
     * Recarrega a página
     */
    reloadPage() {
        // Salvar configurações antes de recarregar
        this.saveToServer();
        
        // Aguardar um pouco para salvar
        setTimeout(() => {
            window.location.reload();
        }, 100);
    }
    
    /**
     * Dispara eventos personalizados
     */
    dispatchThemeChangeEvent() {
        const event = new CustomEvent('themeChange', {
            detail: { theme: this.currentTheme }
        });
        document.dispatchEvent(event);
    }
    
    dispatchContrastChangeEvent() {
        const event = new CustomEvent('contrastChange', {
            detail: { contrast: this.currentContrast }
        });
        document.dispatchEvent(event);
    }
    
    dispatchFontSizeChangeEvent() {
        const event = new CustomEvent('fontSizeChange', {
            detail: { fontSize: this.currentFontSize }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Obtém as configurações atuais
     */
    getCurrentSettings() {
        return {
            theme: this.currentTheme,
            contrast: this.currentContrast,
            language: this.currentLanguage,
            font_size: this.currentFontSize
        };
    }
    
    /**
     * Reseta para as configurações padrão
     */
    resetToDefaults() {
        this.setTheme('light');
        this.setContrast('normal');
        this.setLanguage('pt-BR');
        this.setFontSize('medium');
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Exportar para uso em outros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}
