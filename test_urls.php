<?php
/**
 * Teste de URLs do Sistema LJ-OS
 */

echo "🧪 Testando URLs do Sistema LJ-OS...\n\n";

// Verificar se o arquivo de URLs existe
if (file_exists(__DIR__ . '/config/urls.php')) {
    echo "✅ Arquivo config/urls.php encontrado\n";
    require_once __DIR__ . '/config/urls.php';
    
    echo "📋 URLs configuradas:\n";
    echo "   BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NÃO DEFINIDA') . "\n";
    echo "   APP_URL: " . (defined('APP_URL') ? APP_URL : 'NÃO DEFINIDA') . "\n";
    echo "   API_URL: " . (defined('API_URL') ? API_URL : 'NÃO DEFINIDA') . "\n";
    echo "   ASSETS_URL: " . (defined('ASSETS_URL') ? ASSETS_URL : 'NÃO DEFINIDA') . "\n";
} else {
    echo "❌ Arquivo config/urls.php não encontrado\n";
    echo "   Execute o instalador primeiro: install_web.php\n";
}

echo "\n📁 Estrutura de diretórios:\n";
echo "   Raiz: " . __DIR__ . "\n";
echo "   App: " . __DIR__ . "/app\n";
echo "   Config: " . __DIR__ . "/config\n";
echo "   Src: " . __DIR__ . "/src\n";

echo "\n🔗 URLs de teste:\n";
echo "   Instalador: " . (isset($_SERVER['HTTP_HOST']) ? "http://{$_SERVER['HTTP_HOST']}" : 'localhost') . "/LJ-OS/install_web.php\n";
echo "   Login: " . (isset($_SERVER['HTTP_HOST']) ? "http://{$_SERVER['HTTP_HOST']}" : 'localhost') . "/LJ-OS/app/login.php\n";
echo "   Dashboard: " . (isset($_SERVER['HTTP_HOST']) ? "http://{$_SERVER['HTTP_HOST']}" : 'localhost') . "/LJ-OS/app/dashboard.php\n";

echo "\n🎯 Para acessar o sistema:\n";
echo "   1. Acesse: http://localhost/LJ-OS/install_web.php\n";
echo "   2. Configure a URL base: http://localhost/LJ-OS\n";
echo "   3. Após instalação, acesse: http://localhost/LJ-OS/app/\n";

echo "\n✅ Teste de URLs concluído!\n";
?>
