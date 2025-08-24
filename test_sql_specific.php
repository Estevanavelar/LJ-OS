<?php
/**
 * Teste Específico de SQL
 */

echo "🧪 Testando SQL específico...\n\n";

// Carregar autoloader
require_once __DIR__ . '/autoload.php';

try {
    // Testar conexão com banco
    echo "📋 Testando conexão com banco...\n";
    $db = LJOS\Database\Database::getInstance();
    $connection = $db->getConnection();
    echo "✅ Conexão estabelecida\n";
    
    // Testar consulta específica que pode estar causando problema
    echo "\n📋 Testando consulta específica...\n";
    
    // Testar a consulta que está na função processInstallation
    $stmt = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
    $result = $stmt->fetch();
    echo "✅ Consulta sqlite_master executada: " . ($result ? 'Tabela encontrada' : 'Tabela não encontrada') . "\n";
    
    // Testar consulta na tabela usuarios
    $stmt = $connection->query("SELECT id_usuario FROM usuarios WHERE email = 'admin@lj-os.com'");
    $result = $stmt->fetch();
    echo "✅ Consulta usuarios executada: " . ($result ? 'Usuário encontrado' : 'Usuário não encontrado') . "\n";
    
    // Testar consulta com JOIN (que pode estar causando problema)
    echo "\n📋 Testando consulta com JOIN...\n";
    
    // Simular a consulta que está no modelo Cliente
    $sql = "
        SELECT c.*, v.* 
        FROM clientes c 
        LEFT JOIN veiculos v ON c.id_cliente = v.id_cliente 
        WHERE c.id_cliente = ?
        ORDER BY v.data_cadastro DESC
    ";
    
    // Preparar a consulta
    $stmt = $connection->prepare($sql);
    $stmt->execute([1]);
    $result = $stmt->fetchAll();
    echo "✅ Consulta com JOIN executada: " . count($result) . " resultados\n";
    
    // Testar consulta com múltiplas condições
    echo "\n📋 Testando consulta com múltiplas condições...\n";
    
    $conditions = [
        ['tipo_pessoa', '=', 'PF'],
        ['status', '=', 'ATIVO']
    ];
    
    $whereClauses = [];
    $params = [];
    
    foreach ($conditions as $condition) {
        $whereClauses[] = "{$condition[0]} {$condition[1]} ?";
        $params[] = $condition[2];
    }
    
    $sql = "SELECT * FROM clientes WHERE " . implode(' AND ', $whereClauses);
    echo "SQL gerado: {$sql}\n";
    
    $stmt = $connection->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll();
    echo "✅ Consulta com múltiplas condições executada: " . count($result) . " resultados\n";
    
    echo "\n✅ Todos os testes SQL executados com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    
    // Verificar se é erro de sintaxe SQL
    if (strpos($e->getMessage(), 'syntax error') !== false) {
        echo "\n🔍 Erro de sintaxe SQL detectado!\n";
        echo "Verifique a consulta SQL que falhou.\n";
    }
}

echo "\n✅ Teste concluído!\n";
?>
