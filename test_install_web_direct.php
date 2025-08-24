<?php
/**
 * Teste Direto do Instalador Web
 */

echo "🧪 Testando instalador web diretamente...\n\n";

// Simular POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['step'] = '2';
$_POST['base_url'] = 'http://localhost/LJ-OS';

try {
    // Incluir o arquivo do instalador
    echo "📋 Incluindo arquivo install_web.php...\n";
    
    // Capturar saída para não exibir HTML
    ob_start();
    include __DIR__ . '/install_web.php';
    $output = ob_get_clean();
    
    echo "✅ Arquivo incluído com sucesso\n";
    
    // Verificar se as funções foram definidas
    echo "\n📋 Verificando funções...\n";
    
    if (function_exists('checkSystemRequirements')) {
        echo "✅ Função checkSystemRequirements encontrada\n";
    } else {
        echo "❌ Função checkSystemRequirements não encontrada\n";
    }
    
    if (function_exists('processInstallation')) {
        echo "✅ Função processInstallation encontrada\n";
    } else {
        echo "❌ Função processInstallation não encontrada\n";
    }
    
    echo "\n✅ Teste direto concluído!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    
    // Verificar se é erro de sintaxe SQL
    if (strpos($e->getMessage(), 'syntax error') !== false) {
        echo "\n🔍 Erro de sintaxe SQL detectado!\n";
        echo "Verifique as consultas SQL no instalador.\n";
    }
}

echo "\n✅ Teste concluído!\n";
?>
