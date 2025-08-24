<?php
/**
 * Teste da API
 */

echo "🧪 Testando API...\n\n";

// Simular requisição GET para a API
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['id'] = '1';

// Carregar autoloader
require_once __DIR__ . '/autoload.php';

try {
    // Testar API de clientes
    echo "📋 Testando API de clientes...\n";
    
    // Simular autenticação
    $auth = new LJOS\Auth\JWTAuth();
    
    // Criar modelo de cliente
    $clienteModel = new LJOS\Models\Cliente();
    
    // Testar busca por ID
    echo "   - Testando busca por ID...\n";
    $cliente = $clienteModel->find(1);
    echo "   ✅ Cliente encontrado: " . ($cliente ? 'SIM' : 'NÃO') . "\n";
    
    // Testar busca por CPF/CNPJ
    echo "   - Testando busca por CPF/CNPJ...\n";
    $cliente = $clienteModel->findByCpfCnpj('12345678901');
    echo "   ✅ Cliente por CPF: " . ($cliente ? 'SIM' : 'NÃO') . "\n";
    
    // Testar busca por telefone
    echo "   - Testando busca por telefone...\n";
    $cliente = $clienteModel->findByTelefone('11999999999');
    echo "   ✅ Cliente por telefone: " . ($cliente ? 'SIM' : 'NÃO') . "\n";
    
    // Testar busca por email
    echo "   - Testando busca por email...\n";
    $cliente = $clienteModel->findByEmail('cliente@teste.com');
    echo "   ✅ Cliente por email: " . ($cliente ? 'SIM' : 'NÃO') . "\n";
    
    // Testar busca por tipo de pessoa
    echo "   - Testando busca por tipo de pessoa...\n";
    $clientes = $clienteModel->findByTipoPessoa('PF');
    echo "   ✅ Clientes PF: " . count($clientes) . "\n";
    
    // Testar busca por cidade
    echo "   - Testando busca por cidade...\n";
    $clientes = $clienteModel->findByCidade('São Paulo');
    echo "   ✅ Clientes por cidade: " . count($clientes) . "\n";
    
    // Testar busca por estado
    echo "   - Testando busca por estado...\n";
    $clientes = $clienteModel->findByEstado('SP');
    echo "   ✅ Clientes por estado: " . count($clientes) . "\n";
    
    // Testar busca com múltiplas condições
    echo "   - Testando busca com múltiplas condições...\n";
    $conditions = [
        ['tipo_pessoa', '=', 'PF'],
        ['status', '=', 'ATIVO']
    ];
    $clientes = $clienteModel->whereMultiple($conditions);
    echo "   ✅ Clientes com condições: " . count($clientes) . "\n";
    
    echo "\n✅ API testada com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    
    // Verificar se é erro de sintaxe SQL
    if (strpos($e->getMessage(), 'syntax error') !== false) {
        echo "\n🔍 Erro de sintaxe SQL detectado!\n";
        echo "Verifique as consultas SQL na API.\n";
    }
}

echo "\n✅ Teste concluído!\n";
?>
