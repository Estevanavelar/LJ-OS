<?php
/**
 * Teste do Instalador CLI
 */

echo "🧪 Testando instalador CLI...\n\n";

// Carregar autoloader
require_once __DIR__ . '/autoload.php';

try {
    // Testar função de verificação de requisitos
    echo "📋 Testando verificação de requisitos...\n";
    
    if (function_exists('checkRequirements')) {
        $config = [
            'app' => ['name' => 'LJ-OS'],
            'database' => ['type' => 'sqlite'],
            'logging' => ['level' => 'info']
        ];
        
        $requirements = checkRequirements($config);
        echo "✅ Requisitos verificados: " . ($requirements['success'] ? 'SUCESSO' : 'FALHA') . "\n";
        
        if (!$requirements['success']) {
            echo "❌ Problemas encontrados:\n";
            foreach ($requirements['errors'] as $error) {
                echo "   - {$error}\n";
            }
        }
    } else {
        echo "❌ Função checkRequirements não encontrada\n";
    }
    
    // Testar função de configuração de banco
    echo "\n📋 Testando configuração de banco...\n";
    
    if (function_exists('setupDatabase')) {
        $result = setupDatabase();
        echo "✅ Banco configurado: " . ($result['success'] ? 'SUCESSO' : 'FALHA') . "\n";
        
        if (!$result['success']) {
            echo "❌ Problemas encontrados:\n";
            foreach ($result['errors'] as $error) {
                echo "   - {$error}\n";
            }
        }
    } else {
        echo "❌ Função setupDatabase não encontrada\n";
    }
    
    // Testar função de criação de usuário admin
    echo "\n📋 Testando criação de usuário admin...\n";
    
    if (function_exists('createAdminUser')) {
        $result = createAdminUser();
        echo "✅ Usuário admin criado: " . ($result['success'] ? 'SUCESSO' : 'FALHA') . "\n";
        
        if (!$result['success']) {
            echo "❌ Problemas encontrados:\n";
            foreach ($result['errors'] as $error) {
                echo "   - {$error}\n";
            }
        }
    } else {
        echo "❌ Função createAdminUser não encontrada\n";
    }
    
    echo "\n✅ Instalador CLI testado com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    
    // Verificar se é erro de sintaxe SQL
    if (strpos($e->getMessage(), 'syntax error') !== false) {
        echo "\n🔍 Erro de sintaxe SQL detectado!\n";
        echo "Verifique as consultas SQL no instalador CLI.\n";
    }
}

echo "\n✅ Teste concluído!\n";
?>
