<?php
/**
 * Debug do Erro SQL
 */

echo "🔍 Debugando erro SQL...\n\n";

// Carregar autoloader
require_once __DIR__ . '/autoload.php';

try {
    // Testar conexão com banco
    $db = LJOS\Database\Database::getInstance();
    echo "✅ Conexão com banco estabelecida\n";
    
    // Verificar se a tabela usuarios existe
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ Tabela 'usuarios' encontrada\n";
        
        // Verificar estrutura da tabela
        $stmt = $db->query("PRAGMA table_info(usuarios)");
        $columns = $stmt->fetchAll();
        
        echo "📋 Estrutura da tabela 'usuarios':\n";
        foreach ($columns as $column) {
            echo "   - {$column['name']} ({$column['type']})\n";
        }
        
        // Testar consulta simples
        echo "\n🧪 Testando consulta simples...\n";
        $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
        $result = $stmt->fetch();
        echo "✅ Total de usuários: {$result['total']}\n";
        
        // Testar consulta com WHERE
        echo "\n🧪 Testando consulta com WHERE...\n";
        $stmt = $db->query("SELECT id_usuario, nome, email FROM usuarios WHERE status = 'ATIVO'");
        $result = $stmt->fetchAll();
        echo "✅ Usuários ativos encontrados: " . count($result) . "\n";
        
    } else {
        echo "❌ Tabela 'usuarios' não encontrada\n";
        
        // Verificar todas as tabelas
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll();
        
        echo "📋 Tabelas existentes:\n";
        foreach ($tables as $table) {
            echo "   - {$table['name']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    
    // Verificar se é erro de sintaxe SQL
    if (strpos($e->getMessage(), 'syntax error') !== false) {
        echo "\n🔍 Possível erro de sintaxe SQL detectado!\n";
        echo "Verifique as consultas SQL nos modelos.\n";
    }
}

echo "\n✅ Debug concluído!\n";
?>
