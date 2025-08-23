<?php

namespace LJOS\Utils;

/**
 * Classe de Localização e Temas
 * Gerencia idiomas, temas e configurações de acessibilidade
 */
class Localization
{
    private static $instance = null;
    private $currentLanguage = 'pt-BR';
    private $currentTheme = 'light';
    private $currentContrast = 'normal';
    private $currentFontSize = 'medium';
    private $languages = [];
    private $supportedLanguages = ['pt-BR', 'en-US'];
    private $supportedThemes = ['light', 'dark'];
    private $supportedContrasts = ['low', 'normal', 'high'];
    private $supportedFontSizes = ['small', 'medium', 'large'];
    
    private function __construct()
    {
        $this->loadUserPreferences();
        $this->loadLanguages();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Carrega as preferências do usuário
     */
    private function loadUserPreferences(): void
    {
        // Verificar se há preferências salvas
        if (isset($_COOKIE['lj_os_language'])) {
            $this->currentLanguage = $_COOKIE['lj_os_language'];
        }
        
        if (isset($_COOKIE['lj_os_theme'])) {
            $this->currentTheme = $_COOKIE['lj_os_theme'];
        }
        
        if (isset($_COOKIE['lj_os_contrast'])) {
            $this->currentContrast = $_COOKIE['lj_os_contrast'];
        }
        
        if (isset($_COOKIE['lj_os_font_size'])) {
            $this->currentFontSize = $_COOKIE['lj_os_font_size'];
        }
        
        // Validar valores
        if (!in_array($this->currentLanguage, $this->supportedLanguages)) {
            $this->currentLanguage = 'pt-BR';
        }
        
        if (!in_array($this->currentTheme, $this->supportedThemes)) {
            $this->currentTheme = 'light';
        }
        
        if (!in_array($this->currentContrast, $this->supportedContrasts)) {
            $this->currentContrast = 'normal';
        }
        
        if (!in_array($this->currentFontSize, $this->supportedFontSizes)) {
            $this->currentFontSize = 'medium';
        }
    }
    
    /**
     * Carrega os arquivos de idioma
     */
    private function loadLanguages(): void
    {
        foreach ($this->supportedLanguages as $lang) {
            $langFile = __DIR__ . "/../../app/languages/{$lang}.php";
            if (file_exists($langFile)) {
                $this->languages[$lang] = require $langFile;
            }
        }
    }
    
    /**
     * Obtém uma tradução
     */
    public function get(string $key, array $params = []): string
    {
        $translation = $this->languages[$this->currentLanguage][$key] ?? $key;
        
        // Substituir parâmetros
        foreach ($params as $param => $value) {
            $translation = str_replace(":{$param}", $value, $translation);
        }
        
        return $translation;
    }
    
    /**
     * Define o idioma atual
     */
    public function setLanguage(string $language): bool
    {
        if (in_array($language, $this->supportedLanguages)) {
            $this->currentLanguage = $language;
            setcookie('lj_os_language', $language, time() + (365 * 24 * 60 * 60), '/');
            return true;
        }
        return false;
    }
    
    /**
     * Define o tema atual
     */
    public function setTheme(string $theme): bool
    {
        if (in_array($theme, $this->supportedThemes)) {
            $this->currentTheme = $theme;
            setcookie('lj_os_theme', $theme, time() + (365 * 24 * 60 * 60), '/');
            return true;
        }
        return false;
    }
    
    /**
     * Define o contraste atual
     */
    public function setContrast(string $contrast): bool
    {
        if (in_array($contrast, $this->supportedContrasts)) {
            $this->currentContrast = $contrast;
            setcookie('lj_os_contrast', $contrast, time() + (365 * 24 * 60 * 60), '/');
            return true;
        }
        return false;
    }
    
    /**
     * Define o tamanho da fonte
     */
    public function setFontSize(string $fontSize): bool
    {
        if (in_array($fontSize, $this->supportedFontSizes)) {
            $this->currentFontSize = $fontSize;
            setcookie('lj_os_font_size', $fontSize, time() + (365 * 24 * 60 * 60), '/');
            return true;
        }
        return false;
    }
    
    /**
     * Obtém o idioma atual
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }
    
    /**
     * Obtém o tema atual
     */
    public function getCurrentTheme(): string
    {
        return $this->currentTheme;
    }
    
    /**
     * Obtém o contraste atual
     */
    public function getCurrentContrast(): string
    {
        return $this->currentContrast;
    }
    
    /**
     * Obtém o tamanho da fonte atual
     */
    public function getCurrentFontSize(): string
    {
        return $this->currentFontSize;
    }
    
    /**
     * Obtém todos os idiomas suportados
     */
    public function getSupportedLanguages(): array
    {
        return $this->supportedLanguages;
    }
    
    /**
     * Obtém todos os temas suportados
     */
    public function getSupportedThemes(): array
    {
        return $this->supportedThemes;
    }
    
    /**
     * Obtém todos os contrastes suportados
     */
    public function getSupportedContrasts(): array
    {
        return $this->supportedContrasts;
    }
    
    /**
     * Obtém todos os tamanhos de fonte suportados
     */
    public function getSupportedFontSizes(): array
    {
        return $this->supportedFontSizes;
    }
    
    /**
     * Obtém as configurações atuais para o HTML
     */
    public function getHtmlAttributes(): string
    {
        $attrs = [];
        $attrs[] = "data-theme=\"{$this->currentTheme}\"";
        $attrs[] = "data-contrast=\"{$this->currentContrast}\"";
        $attrs[] = "data-font-size=\"{$this->currentFontSize}\"";
        $attrs[] = "lang=\"{$this->currentLanguage}\"";
        
        return implode(' ', $attrs);
    }
    
    /**
     * Obtém o nome do idioma para exibição
     */
    public function getLanguageName(string $language): string
    {
        $names = [
            'pt-BR' => 'Português (Brasil)',
            'en-US' => 'English (US)'
        ];
        
        return $names[$language] ?? $language;
    }
    
    /**
     * Obtém o nome do tema para exibição
     */
    public function getThemeName(string $theme): string
    {
        return $this->get($theme === 'light' ? 'light_mode' : 'dark_mode');
    }
    
    /**
     * Obtém o nome do contraste para exibição
     */
    public function getContrastName(string $contrast): string
    {
        $keys = [
            'low' => 'low_contrast',
            'normal' => 'normal_contrast',
            'high' => 'high_contrast'
        ];
        
        return $this->get($keys[$contrast] ?? 'normal_contrast');
    }
    
    /**
     * Obtém o nome do tamanho da fonte para exibição
     */
    public function getFontSizeName(string $fontSize): string
    {
        return $this->get($fontSize);
    }
    
    /**
     * Aplica as configurações ao HTML
     */
    public function applySettings(): void
    {
        // Aplicar atributos ao HTML
        if (!headers_sent()) {
            echo "<!DOCTYPE html>\n<html " . $this->getHtmlAttributes() . ">\n";
        }
    }
    
    /**
     * Obtém todas as configurações atuais
     */
    public function getCurrentSettings(): array
    {
        return [
            'language' => $this->currentLanguage,
            'theme' => $this->currentTheme,
            'contrast' => $this->currentContrast,
            'font_size' => $this->currentFontSize
        ];
    }
    
    /**
     * Reseta as configurações para os padrões
     */
    public function resetToDefaults(): void
    {
        $this->setLanguage('pt-BR');
        $this->setTheme('light');
        $this->setContrast('normal');
        $this->setFontSize('medium');
    }
    
    private function __clone() {}
    
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
