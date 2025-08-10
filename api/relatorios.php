<?php
/**
 * LJ-OS Sistema para Lava Jato
 * API - Sistema de Relatórios
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Verificar login
verificarLogin();

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $input['action'] ?? '';

// Validar CSRF para métodos que modificam estado
$unsafeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', $unsafeMethods, true)) {
    csrf_verificar_api();
}

try {
    switch ($action) {
        case 'dados_grafico_faturamento':
            dadosGraficoFaturamento();
            break;
            
        case 'dados_grafico_servicos':
            dadosGraficoServicos();
            break;
            
        case 'listar_relatorios':
            listarRelatorios();
            break;
            
        case 'gerar_relatorio':
            gerarRelatorio($input);
            break;
            
        case 'relatorio_completo':
            relatorioCompleto($_GET);
            break;
            
        case 'exportar_dados':
            exportarDados($_GET);
            break;
            
        case 'baixar':
            baixarRelatorio($_GET);
            break;
            
        case 'excluir_relatorio':
            excluirRelatorio($input);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}

function dadosGraficoFaturamento() {
    global $pdo;
    
    // Buscar dados dos últimos 12 meses
    $dados = [];
    $labels = [];
    $faturamento = [];
    
    for ($i = 11; $i >= 0; $i--) {
        $mes = date('Y-m', strtotime("-$i months"));
        $mesFormatado = date('M/Y', strtotime("-$i months"));
        
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(valor_total), 0) as faturamento
            FROM ordens_servico 
            WHERE DATE_FORMAT(data_abertura, '%Y-%m') = ? AND status = 'finalizada'
        ");
        $stmt->execute([$mes]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $labels[] = $mesFormatado;
        $faturamento[] = floatval($resultado['faturamento']);
    }
    
    echo json_encode([
        'success' => true,
        'dados' => [
            'labels' => $labels,
            'faturamento' => $faturamento
        ]
    ]);
}

function dadosGraficoServicos() {
    global $pdo;
    
    // Buscar serviços mais vendidos do último mês
    $mesAtual = date('Y-m');
    
    $stmt = $pdo->prepare("
        SELECT s.nome_servico, COUNT(*) as quantidade
        FROM os_servicos oss
        JOIN servicos s ON oss.id_servico = s.id_servico
        JOIN ordens_servico os ON oss.id_os = os.id_os
        WHERE DATE_FORMAT(os.data_abertura, '%Y-%m') = ? AND os.status = 'finalizada'
        GROUP BY s.id_servico, s.nome_servico
        ORDER BY quantidade DESC
        LIMIT 5
    ");
    $stmt->execute([$mesAtual]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $valores = [];
    
    foreach ($servicos as $servico) {
        $labels[] = $servico['nome'];
        $valores[] = intval($servico['quantidade']);
    }
    
    echo json_encode([
        'success' => true,
        'dados' => [
            'labels' => $labels,
            'valores' => $valores
        ]
    ]);
}

function listarRelatorios() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id_relatorio, tipo, data_inicio, data_fim, formato, status, data_geracao
        FROM relatorios 
        ORDER BY data_geracao DESC 
        LIMIT 50
    ");
    $stmt->execute();
    $relatorios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'relatorios' => $relatorios
    ]);
}

function gerarRelatorio($data) {
    global $pdo;
    
    // Validar dados obrigatórios
    if (empty($data['tipo']) || empty($data['data_inicio']) || empty($data['data_fim']) || empty($data['formato'])) {
        echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
        return;
    }
    
    $pdo->beginTransaction();
    
    try {
        // Registrar solicitação de relatório
        $stmt = $pdo->prepare("
            INSERT INTO relatorios (tipo, data_inicio, data_fim, formato, agrupamento, 
                                   apenas_concluidos, apenas_pagos, incluir_graficos, 
                                   observacoes, status, data_geracao)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'processando', NOW())
        ");
        
        $stmt->execute([
            $data['tipo'],
            $data['data_inicio'],
            $data['data_fim'],
            $data['formato'],
            $data['agrupamento'] ?? 'mensal',
            isset($data['apenas_concluidos']) ? 1 : 0,
            isset($data['apenas_pagos']) ? 1 : 0,
            isset($data['incluir_graficos']) ? 1 : 0,
            $data['observacoes'] ?? null
        ]);
        
        $idRelatorio = $pdo->lastInsertId();
        
        // Simular processamento (em produção, isso seria feito em background)
        // Aqui apenas atualizamos o status para concluído
        $stmt = $pdo->prepare("UPDATE relatorios SET status = 'concluido' WHERE id_relatorio = ?");
        $stmt->execute([$idRelatorio]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Relatório solicitado com sucesso', 'id' => $idRelatorio]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar relatório: ' . $e->getMessage()]);
    }
}

function relatorioCompleto($params) {
    global $pdo;
    
    $dataInicio = $params['data_inicio'] ?? '';
    $dataFim = $params['data_fim'] ?? '';
    $formato = $params['formato'] ?? 'pdf';
    
    if (empty($dataInicio) || empty($dataFim)) {
        echo json_encode(['success' => false, 'message' => 'Período não informado']);
        return;
    }
    
    // Construir WHERE
    $where = "WHERE os.data_abertura BETWEEN ? AND ?";
    $paramsValues = [$dataInicio, $dataFim];
    
    // Buscar dados do relatório
    $sql = "
        SELECT 
            os.id_os,
            os.codigo_os,
            os.data_abertura,
            os.valor_total,
            os.status,
            c.nome as cliente_nome,
            c.cpf_cnpj,
            v.placa,
            v.modelo,
            v.marca,
            GROUP_CONCAT(s.nome_servico SEPARATOR ', ') as servicos
        FROM ordens_servico os
        LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
        LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
        LEFT JOIN os_servicos oss ON os.id_os = oss.id_os
        LEFT JOIN servicos s ON oss.id_servico = s.id_servico
        $where
        GROUP BY os.id_os
        ORDER BY os.data_abertura DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($paramsValues);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estatísticas
    $totalOS = count($dados);
    $totalFaturamento = array_sum(array_column($dados, 'valor_total'));
    $osConcluidas = count(array_filter($dados, function($item) { return $item['status'] === 'finalizada'; }));
    
    if ($formato === 'csv') {
        exportarRelatorioCSV($dados, $dataInicio, $dataFim, $totalOS, $totalFaturamento, $osConcluidas);
    } else {
        // Para PDF e Excel, retornar dados para processamento
        echo json_encode([
            'success' => true,
            'dados' => $dados,
            'estatisticas' => [
                'total_os' => $totalOS,
                'total_faturamento' => $totalFaturamento,
                'os_concluidas' => $osConcluidas,
                'taxa_conclusao' => $totalOS > 0 ? ($osConcluidas / $totalOS) * 100 : 0
            ]
        ]);
    }
}

function exportarDados($params) {
    global $pdo;
    
    $dataInicio = $params['data_inicio'] ?? '';
    $dataFim = $params['data_fim'] ?? '';
    $tipo = $params['tipo'] ?? '';
    $formato = $params['formato'] ?? 'csv';
    
    if (empty($dataInicio) || empty($dataFim)) {
        echo json_encode(['success' => false, 'message' => 'Período não informado']);
        return;
    }
    
    switch ($tipo) {
        case 'vendas':
            exportarDadosVendas($dataInicio, $dataFim, $formato);
            break;
        case 'clientes':
            exportarDadosClientes($dataInicio, $dataFim, $formato);
            break;
        case 'servicos':
            exportarDadosServicos($dataInicio, $dataFim, $formato);
            break;
        case 'agendamentos':
            exportarDadosAgendamentos($dataInicio, $dataFim, $formato);
            break;
        default:
            exportarDadosCompletos($dataInicio, $dataFim, $formato);
    }
}

function exportarDadosVendas($dataInicio, $dataFim, $formato) {
    global $pdo;
    
    $sql = "
        SELECT 
            os.codigo_os,
            os.data_abertura,
            os.valor_total,
            os.status,
            c.nome as cliente,
            c.cpf_cnpj,
            v.placa,
            v.modelo,
            v.marca,
            GROUP_CONCAT(s.nome_servico SEPARATOR ', ') as servicos
        FROM ordens_servico os
        LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
        LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
        LEFT JOIN os_servicos oss ON os.id_os = oss.id_os
        LEFT JOIN servicos s ON oss.id_servico = s.id_servico
        WHERE os.data_abertura BETWEEN ? AND ?
        GROUP BY os.id_os
        ORDER BY os.data_abertura DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dataInicio, $dataFim]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    exportarCSV($dados, 'vendas', $dataInicio, $dataFim);
}

function exportarDadosClientes($dataInicio, $dataFim, $formato) {
    global $pdo;
    
    $sql = "
        SELECT 
            c.nome,
            c.cpf_cnpj,
            c.email,
            c.telefone,
            c.data_cadastro,
            COUNT(DISTINCT os.id_os) as total_os,
            COALESCE(SUM(os.valor_total), 0) as valor_total,
            COUNT(DISTINCT v.id_veiculo) as total_veiculos
        FROM clientes c
        LEFT JOIN ordens_servico os ON c.id_cliente = os.id_cliente 
            AND os.data_abertura BETWEEN ? AND ?
        LEFT JOIN veiculos v ON c.id_cliente = v.id_cliente
        WHERE c.data_cadastro BETWEEN ? AND ?
        GROUP BY c.id_cliente
        ORDER BY valor_total DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dataInicio, $dataFim, $dataInicio, $dataFim]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    exportarCSV($dados, 'clientes', $dataInicio, $dataFim);
}

function exportarDadosServicos($dataInicio, $dataFim, $formato) {
    global $pdo;
    
    $sql = "
        SELECT 
            s.nome_servico,
            s.descricao,
            s.preco,
            COUNT(oss.id_os_servico) as quantidade_vendida,
            COALESCE(SUM(oss.subtotal), 0) as faturamento_total,
            AVG(oss.preco_unitario) as preco_medio
        FROM servicos s
        LEFT JOIN os_servicos oss ON s.id_servico = oss.id_servico
        LEFT JOIN ordens_servico os ON oss.id_os = os.id_os 
            AND os.data_abertura BETWEEN ? AND ?
        WHERE s.data_cadastro BETWEEN ? AND ?
        GROUP BY s.id_servico
        ORDER BY quantidade_vendida DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dataInicio, $dataFim, $dataInicio, $dataFim]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    exportarCSV($dados, 'servicos', $dataInicio, $dataFim);
}

function exportarDadosAgendamentos($dataInicio, $dataFim, $formato) {
    global $pdo;
    
    $sql = "
        SELECT 
            a.data_agendamento,
            a.hora_entrega_estimada,
            a.status,
            c.nome as cliente,
            c.cpf_cnpj,
            v.placa,
            v.modelo,
            v.marca,
            s.nome_servico as servicos
        FROM agendamentos a
        LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
        LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
        LEFT JOIN servicos s ON a.id_servico = s.id_servico
        WHERE a.data_agendamento BETWEEN ? AND ?
        ORDER BY a.data_agendamento, a.hora_entrega_estimada
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dataInicio, $dataFim]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    exportarCSV($dados, 'agendamentos', $dataInicio, $dataFim);
}

function exportarDadosCompletos($dataInicio, $dataFim, $formato) {
    global $pdo;
    
    // Relatório completo com todas as informações
    $sql = "
        SELECT 
            'OS' as tipo_registro,
            os.codigo_os as identificador,
            os.data_abertura as data,
            os.valor_total as valor,
            os.status,
            c.nome as cliente,
            v.placa as veiculo,
            GROUP_CONCAT(s.nome_servico SEPARATOR ', ') as detalhes
        FROM ordens_servico os
        LEFT JOIN clientes c ON os.id_cliente = c.id_cliente
        LEFT JOIN veiculos v ON os.id_veiculo = v.id_veiculo
        LEFT JOIN os_servicos oss ON os.id_os = oss.id_os
        LEFT JOIN servicos s ON oss.id_servico = s.id_servico
        WHERE os.data_abertura BETWEEN ? AND ?
        GROUP BY os.id_os
        
        UNION ALL
        
        SELECT 
            'AGENDAMENTO' as tipo_registro,
            CONCAT('AG', a.id_agendamento) as identificador,
            a.data_agendamento as data,
            0 as valor,
            a.status,
            c.nome as cliente,
            v.placa as veiculo,
            s.nome_servico as detalhes
        FROM agendamentos a
        LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
        LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
        LEFT JOIN servicos s ON a.id_servico = s.id_servico
        WHERE a.data_agendamento BETWEEN ? AND ?
        
        ORDER BY data DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dataInicio, $dataFim, $dataInicio, $dataFim]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    exportarCSV($dados, 'dados_completos', $dataInicio, $dataFim);
}

function exportarCSV($dados, $tipo, $dataInicio, $dataFim) {
    // Configurar headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_' . $tipo . '_' . $dataInicio . '_' . $dataFim . '.csv"');
    
    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if (!empty($dados)) {
        // Cabeçalho
        fputcsv($output, array_keys($dados[0]), ';');
        
        // Dados
        foreach ($dados as $linha) {
            $linhaFormatada = array_map(function($valor) {
                if (is_numeric($valor)) {
                    return number_format($valor, 2, ',', '.');
                }
                return $valor;
            }, $linha);
            fputcsv($output, $linhaFormatada, ';');
        }
    }
    
    fclose($output);
}

function baixarRelatorio($params) {
    global $pdo;
    
    $id = $params['id'] ?? 0;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID do relatório não informado']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM relatorios WHERE id_relatorio = ?");
    $stmt->execute([$id]);
    $relatorio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$relatorio) {
        echo json_encode(['success' => false, 'message' => 'Relatório não encontrado']);
        return;
    }
    
    if ($relatorio['status'] !== 'concluido') {
        echo json_encode(['success' => false, 'message' => 'Relatório ainda não foi processado']);
        return;
    }
    
    // Aqui você implementaria a lógica para gerar e baixar o arquivo
    // Por enquanto, apenas retornamos os dados do relatório
    echo json_encode([
        'success' => true,
        'relatorio' => $relatorio,
        'message' => 'Relatório disponível para download'
    ]);
}

function excluirRelatorio($data) {
    global $pdo;
    
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID do relatório não informado']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM relatorios WHERE id_relatorio = ?");
    $result = $stmt->execute([$data['id']]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Relatório excluído com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir relatório']);
    }
}
?> 