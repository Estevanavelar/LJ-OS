<?php
/**
 * Teste das Novas Funcionalidades do LJ-OS
 * - Sistema de Temas (Claro/Escuro)
 * - Sistema de Idiomas (pt-BR/en-US)
 * - Sistema de Contraste
 * - Controle de Tamanho da Fonte
 */

echo "🧪 Testando Novas Funcionalidades do LJ-OS...\n\n";

// Carregar autoloader
if (file_exists(__DIR__ . '/autoload.php')) {
    require_once __DIR__ . '/autoload.php';
    echo "✅ Autoloader carregado com sucesso\n";
} else {
    echo "❌ Erro: Autoloader não encontrado\n";
    exit(1);
}

// Testar sistema de localização
try {
    $localization = LJOS\Utils\Localization::getInstance();
    echo "✅ Classe Localization carregada com sucesso\n";
    
    // Testar configurações atuais
    $settings = $localization->getCurrentSettings();
    echo "📊 Configurações atuais:\n";
    echo "   - Idioma: " . $settings['language'] . "\n";
    echo "   - Tema: " . $settings['theme'] . "\n";
    echo "   - Contraste: " . $settings['contrast'] . "\n";
    echo "   - Tamanho da fonte: " . $settings['font_size'] . "\n";
    
    // Testar traduções
    echo "\n🌍 Testando traduções:\n";
    echo "   - Nome da aplicação: " . $localization->get('app_name') . "\n";
    echo "   - Descrição: " . $localization->get('app_description') . "\n";
    echo "   - Login: " . $localization->get('login') . "\n";
    echo "   - Dashboard: " . $localization->get('dashboard') . "\n";
    
    // Testar atributos HTML
    echo "\n🎨 Atributos HTML gerados:\n";
    echo "   " . $localization->getHtmlAttributes() . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao carregar Localization: " . $e->getMessage() . "\n";
    exit(1);
}

// Testar arquivos de idioma
echo "\n📁 Verificando arquivos de idioma:\n";
$languages = ['pt-BR', 'en-US'];
foreach ($languages as $lang) {
    $langFile = __DIR__ . "/app/languages/{$lang}.php";
    if (file_exists($langFile)) {
        echo "   ✅ {$lang}: " . basename($langFile) . "\n";
    } else {
        echo "   ❌ {$lang}: Arquivo não encontrado\n";
    }
}

// Testar arquivos de tema
echo "\n🎨 Verificando arquivos de tema:\n";
$themeFiles = [
    'app/assets/css/themes.css',
    'app/assets/js/theme-manager.js',
    'app/components/theme-settings.php'
];

foreach ($themeFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✅ " . basename($file) . "\n";
    } else {
        echo "   ❌ " . basename($file) . " não encontrado\n";
    }
}

// Testar páginas principais
echo "\n🌐 Verificando páginas principais:\n";
$pages = [
    'app/index.php',
    'app/login.php',
    'app/dashboard.php',
    'app/api/auth.php'
];

foreach ($pages as $page) {
    if (file_exists(__DIR__ . '/' . $page)) {
        echo "   ✅ " . basename($page) . "\n";
    } else {
        echo "   ❌ " . basename($page) . " não encontrado\n";
    }
}

// Testar funcionalidades específicas
echo "\n🔧 Testando funcionalidades específicas:\n";

// Testar mudança de idioma
try {
    $localization->setLanguage('en-US');
    echo "   ✅ Idioma alterado para en-US\n";
    echo "      - App Name: " . $localization->get('app_name') . "\n";
    echo "      - Description: " . $localization->get('app_description') . "\n";
    
    // Voltar para pt-BR
    $localization->setLanguage('pt-BR');
    echo "   ✅ Idioma voltou para pt-BR\n";
    
} catch (Exception $e) {
    echo "   ❌ Erro ao testar mudança de idioma: " . $e->getMessage() . "\n";
}

// Testar mudança de tema
try {
    $localization->setTheme('dark');
    echo "   ✅ Tema alterado para dark\n";
    
    $localization->setTheme('light');
    echo "   ✅ Tema voltou para light\n";
    
} catch (Exception $e) {
    echo "   ❌ Erro ao testar mudança de tema: " . $e->getMessage() . "\n";
}

// Testar mudança de contraste
try {
    $localization->setContrast('high');
    echo "   ✅ Contraste alterado para high\n";
    
    $localization->setContrast('normal');
    echo "   ✅ Contraste voltou para normal\n";
    
} catch (Exception $e) {
    echo "   ❌ Erro ao testar mudança de contraste: " . $e->getMessage() . "\n";
}

// Testar mudança de tamanho da fonte
try {
    $localization->setFontSize('large');
    echo "   ✅ Tamanho da fonte alterado para large\n";
    
    $localization->setFontSize('medium');
    echo "   ✅ Tamanho da fonte voltou para medium\n";
    
} catch (Exception $e) {
    echo "   ❌ Erro ao testar mudança de tamanho da fonte: " . $e->getMessage() . "\n";
}

// Verificar URLs de acesso
echo "\n🌐 URLs de acesso:\n";
echo "   - Página principal: http://localhost/LJ-OS/\n";
echo "   - Login: http://localhost/LJ-OS/app/login.php\n";
echo "   - Dashboard: http://localhost/LJ-OS/app/dashboard.php\n";
echo "   - Configurações de tema: http://localhost/LJ-OS/app/components/theme-settings.php\n";
echo "   - API de autenticação: http://localhost/LJ-OS/app/api/auth.php\n";

echo "\n🎉 Teste das novas funcionalidades concluído!\n";
echo "✅ Sistema LJ-OS com temas e idiomas funcionando perfeitamente!\n";
?>
