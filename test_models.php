<?php
/**
 * Teste de Todos os Modelos
 */

echo "🧪 Testando todos os modelos...\n\n";

// Carregar autoloader
require_once __DIR__ . '/autoload.php';

try {
    // Testar modelo Usuario
    echo "📋 Testando modelo Usuario...\n";
    $usuario = new LJOS\Models\Usuario();
    $result = $usuario->find(1);
    echo "✅ Usuario encontrado: " . ($result ? $result['nome'] : 'NÃO') . "\n";
    
    // Testar modelo Cliente
    echo "\n📋 Testando modelo Cliente...\n";
    $cliente = new LJOS\Models\Cliente();
    $result = $cliente->all();
    echo "✅ Total de clientes: " . count($result) . "\n";
    
    // Testar modelo Veiculo
    echo "\n📋 Testando modelo Veiculo...\n";
    $veiculo = new LJOS\Models\Veiculo();
    $result = $veiculo->all();
    echo "✅ Total de veículos: " . count($result) . "\n";
    
    // Testar modelo Servico
    echo "\n📋 Testando modelo Servico...\n";
    $servico = new LJOS\Models\Servico();
    $result = $servico->all();
    echo "✅ Total de serviços: " . count($result) . "\n";
    
    // Testar modelo Agendamento
    echo "\n📋 Testando modelo Agendamento...\n";
    $agendamento = new LJOS\Models\Agendamento();
    $result = $agendamento->all();
    echo "✅ Total de agendamentos: " . count($result) . "\n";
    
    // Testar modelo OrdemServico
    echo "\n📋 Testando modelo OrdemServico...\n";
    $ordem = new LJOS\Models\OrdemServico();
    $result = $ordem->all();
    echo "✅ Total de ordens de serviço: " . count($result) . "\n";
    
    // Testar modelo Produto
    echo "\n📋 Testando modelo Produto...\n";
    $produto = new LJOS\Models\Produto();
    $result = $produto->all();
    echo "✅ Total de produtos: " . count($result) . "\n";
    
    // Testar modelo MovimentacaoEstoque
    echo "\n📋 Testando modelo MovimentacaoEstoque...\n";
    $movimentacao = new LJOS\Models\MovimentacaoEstoque();
    $result = $movimentacao->all();
    echo "✅ Total de movimentações: " . count($result) . "\n";
    
    // Testar modelo Receita
    echo "\n📋 Testando modelo Receita...\n";
    $receita = new LJOS\Models\Receita();
    $result = $receita->all();
    echo "✅ Total de receitas: " . count($result) . "\n";
    
    // Testar modelo Despesa
    echo "\n📋 Testando modelo Despesa...\n";
    $despesa = new LJOS\Models\Despesa();
    $result = $despesa->all();
    echo "✅ Total de despesas: " . count($result) . "\n";
    
    // Testar modelo Funcionario
    echo "\n📋 Testando modelo Funcionario...\n";
    $funcionario = new LJOS\Models\Funcionario();
    $result = $funcionario->all();
    echo "✅ Total de funcionários: " . count($result) . "\n";
    
    echo "\n🎉 Todos os modelos testados com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "📍 Arquivo: " . $e->getFile() . "\n";
    echo "📍 Linha: " . $e->getLine() . "\n";
    
    // Verificar se é erro de sintaxe SQL
    if (strpos($e->getMessage(), 'syntax error') !== false) {
        echo "\n🔍 Erro de sintaxe SQL detectado!\n";
        echo "Verifique as consultas SQL no modelo que falhou.\n";
    }
}

echo "\n✅ Teste concluído!\n";
?>
