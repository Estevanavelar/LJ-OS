<?php
/**
 * Teste do Instalador Web
 */

echo "🧪 Testando instalador web...\n\n";

// Simular POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['step'] = '2';
$_POST['base_url'] = 'http://localhost/LJ-OS';

// Carregar autoloader
require_once __DIR__ . '/autoload.php';

try {
    // Testar função de verificação de requisitos
    echo "📋 Testando verificação de requisitos...\n";
    
    if (function_exists('checkSystemRequirements')) {
        $requirements = checkSystemRequirements();
        echo "✅ Requisitos verificados: " . ($requirements['success'] ? 'SUCESSO' : 'FALHA') . "\n";
        
        if (!$requirements['success']) {
            echo "❌ Problemas encontrados:\n";
            foreach ($requirements['errors'] as $error) {
                echo "   - {$error}\n";
            }
        }
    } else {
        echo "❌ Função checkSystemRequirements não encontrada\n";
    }
    
    // Testar função de processamento de instalação
    echo "\n📋 Testando processamento de instalação...\n";
    
    if (function_exists('processInstallation')) {
        $result = processInstallation('http://localhost/LJ-OS');
        echo "✅ Instalação processada: " . ($result['success'] ? 'SUCESSO' : 'FALHA') . "\n";
        
        if (!$result['success']) {
            echo "❌ Problemas encontrados:\n";
            foreach ($result['errors'] as $error) {
                echo "   - {$error}\n";
            }
        }
    } else {
        echo "❌ Função processInstallation não encontrada\n";
    }
    
    echo "\n✅ Instalador web testado com sucesso!\n";
    
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
