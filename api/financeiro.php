<?php
/**
 * LJ-OS Sistema para Lava Jato
 * API - Gestão Financeira
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

// Validar CSRF para métodos que modificam estado
$unsafeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', $unsafeMethods, true)) {
    csrf_verificar_api();
}

// Obter conexão com o banco de dados
$pdo = getDB();

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? $input['action'] ?? '';

try {
    switch ($action) {
        case 'listar':
            listarMovimentacoes($input);
            break;
            
        case 'buscar':
            buscarMovimentacao($input);
            break;
            
        case 'salvar_movimentacao':
            salvarMovimentacao($input);
            break;
            
        case 'toggle_status':
            toggleStatusMovimentacao($input);
            break;
            
        case 'excluir':
            excluirMovimentacao($input);
            break;
            
        case 'dados_grafico_fluxo':
            dadosGraficoFluxo();
            break;
            
        case 'dados_grafico_receitas_despesas':
            dadosGraficoReceitasDespesas();
            break;
            
        case 'relatorio':
            gerarRelatorio($_GET);
            break;
            
        case 'exportar':
            exportarDados($_GET);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}

function listarMovimentacoes($data) {
    global $pdo;
    
    $pagina = $data['pagina'] ?? 1;
    $itensPorPagina = 20;
    $offset = ($pagina - 1) * $itensPorPagina;
    
    // Construir WHERE
    $where = [];
    $params = [];
    
    if (!empty($data['tipo'])) {
        $where[] = "mf.tipo = ?";
        $params[] = $data['tipo'];
    }
    
    if (!empty($data['categoria'])) {
        $where[] = "mf.id_categoria = ?";
        $params[] = $data['categoria'];
    }
    
    if (!empty($data['data_inicio'])) {
        $where[] = "mf.data_movimentacao >= ?";
        $params[] = $data['data_inicio'];
    }
    
    if (!empty($data['data_fim'])) {
        $where[] = "mf.data_movimentacao <= ?";
        $params[] = $data['data_fim'];
    }
    
    if (!empty($data['busca'])) {
        $where[] = "(mf.descricao LIKE ? OR mf.observacoes LIKE ?)";
        $busca = '%' . $data['busca'] . '%';
        $params[] = $busca;
        $params[] = $busca;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Contar total
    $sqlCount = "SELECT COUNT(*) as total FROM movimentacoes_financeiras mf LEFT JOIN categorias_financeiras cf ON mf.id_categoria = cf.id_categoria $whereClause";
    $stmt = $pdo->prepare($sqlCount);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Buscar movimentações
    $sql = "SELECT mf.*, cf.nome as categoria_nome 
            FROM movimentacoes_financeiras mf 
            LEFT JOIN categorias_financeiras cf ON mf.id_categoria = cf.id_categoria 
            $whereClause 
            ORDER BY mf.data_movimentacao DESC 
            LIMIT $itensPorPagina OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'movimentacoes' => $movimentacoes,
        'total' => $total,
        'pagina' => $pagina,
        'total_paginas' => ceil($total / $itensPorPagina)
    ]);
}

function buscarMovimentacao($data) {
    global $pdo;
    
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID da movimentação não informado']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT mf.*, cf.nome as categoria_nome 
        FROM movimentacoes_financeiras mf 
        LEFT JOIN categorias_financeiras cf ON mf.id_categoria = cf.id_categoria 
        WHERE mf.id_movimentacao = ?
    ");
    $stmt->execute([$data['id']]);
    $movimentacao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($movimentacao) {
        echo json_encode(['success' => true, 'movimentacao' => $movimentacao]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Movimentação não encontrada']);
    }
}

function salvarMovimentacao($data) {
    global $pdo;
    
    // Validar dados obrigatórios
    if (empty($data['tipo']) || empty($data['descricao']) || empty($data['valor']) || empty($data['data_movimentacao'])) {
        echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
        return;
    }
    
    $pdo->beginTransaction();
    
    try {
        if (empty($data['id'])) {
            // Nova movimentação
            $stmt = $pdo->prepare("
                INSERT INTO movimentacoes_financeiras (tipo, id_categoria, descricao, valor, data_movimentacao, 
                                                      forma_pagamento, status, observacoes, data_cadastro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['tipo'],
                $data['id_categoria'] ?: null,
                $data['descricao'],
                $data['valor'],
                $data['data_movimentacao'],
                $data['forma_pagamento'] ?? null,
                $data['status'] ?? 'pago',
                $data['observacoes'] ?? null
            ]);
            
        } else {
            // Atualizar movimentação
            $stmt = $pdo->prepare("
                UPDATE movimentacoes_financeiras SET 
                    tipo = ?, id_categoria = ?, descricao = ?, valor = ?, 
                    data_movimentacao = ?, forma_pagamento = ?, status = ?, observacoes = ?
                WHERE id_movimentacao = ?
            ");
            
            $stmt->execute([
                $data['tipo'],
                $data['id_categoria'] ?: null,
                $data['descricao'],
                $data['valor'],
                $data['data_movimentacao'],
                $data['forma_pagamento'] ?? null,
                $data['status'] ?? 'pago',
                $data['observacoes'] ?? null,
                $data['id']
            ]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Movimentação salva com sucesso']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar movimentação: ' . $e->getMessage()]);
    }
}

function toggleStatusMovimentacao($data) {
    global $pdo;
    
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID da movimentação não informado']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE movimentacoes_financeiras SET status = ? WHERE id_movimentacao = ?");
    $result = $stmt->execute([$data['status'], $data['id']]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Status alterado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar status']);
    }
}

function excluirMovimentacao($data) {
    global $pdo;
    
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID da movimentação não informado']);
        return;
    }
    
    $stmt = $pdo->prepare("DELETE FROM movimentacoes_financeiras WHERE id_movimentacao = ?");
    $result = $stmt->execute([$data['id']]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Movimentação excluída com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir movimentação']);
    }
}

function dadosGraficoFluxo() {
    global $pdo;
    
    // Buscar dados dos últimos 6 meses
    $dados = [];
    $labels = [];
    $receitas = [];
    $despesas = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $mes = date('Y-m', strtotime("-$i months"));
        $mesFormatado = date('M/Y', strtotime("-$i months"));
        
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) as receitas,
                COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) as despesas
            FROM movimentacoes_financeiras 
            WHERE DATE_FORMAT(data_movimentacao, '%Y-%m') = ?
        ");
        $stmt->execute([$mes]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $labels[] = $mesFormatado;
        $receitas[] = floatval($resultado['receitas']);
        $despesas[] = floatval($resultado['despesas']);
    }
    
    echo json_encode([
        'success' => true,
        'dados' => [
            'labels' => $labels,
            'receitas' => $receitas,
            'despesas' => $despesas
        ]
    ]);
}

function dadosGraficoReceitasDespesas() {
    global $pdo;
    
    // Buscar dados do mês atual
    $mesAtual = date('Y-m');
    
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END), 0) as receitas,
            COALESCE(SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END), 0) as despesas
        FROM movimentacoes_financeiras 
        WHERE DATE_FORMAT(data_movimentacao, '%Y-%m') = ?
    ");
    $stmt->execute([$mesAtual]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'dados' => [
            'receitas' => floatval($resultado['receitas']),
            'despesas' => floatval($resultado['despesas'])
        ]
    ]);
}

function gerarRelatorio($params) {
    global $pdo;
    
    $tipo = $params['tipo'] ?? '';
    $dataInicio = $params['data_inicio'] ?? '';
    $dataFim = $params['data_fim'] ?? '';
    
    // Construir WHERE
    $where = [];
    $paramsValues = [];
    
    if (!empty($dataInicio)) {
        $where[] = "mf.data_movimentacao >= ?";
        $paramsValues[] = $dataInicio;
    }
    
    if (!empty($dataFim)) {
        $where[] = "mf.data_movimentacao <= ?";
        $paramsValues[] = $dataFim;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    switch ($tipo) {
        case 'fluxo_caixa':
            $sql = "SELECT 
                        DATE_FORMAT(mf.data_movimentacao, '%Y-%m') as mes,
                        SUM(CASE WHEN mf.tipo = 'receita' THEN mf.valor ELSE 0 END) as receitas,
                        SUM(CASE WHEN mf.tipo = 'despesa' THEN mf.valor ELSE 0 END) as despesas,
                        SUM(CASE WHEN mf.tipo = 'receita' THEN mf.valor ELSE -mf.valor END) as saldo
                    FROM movimentacoes_financeiras mf 
                    $whereClause 
                    GROUP BY DATE_FORMAT(mf.data_movimentacao, '%Y-%m')
                    ORDER BY mes DESC";
            break;
            
        case 'receitas_categoria':
            $sql = "SELECT 
                        cf.nome as categoria,
                        SUM(mf.valor) as total,
                        COUNT(*) as quantidade
                    FROM movimentacoes_financeiras mf 
                    LEFT JOIN categorias_financeiras cf ON mf.id_categoria = cf.id_categoria 
                    WHERE mf.tipo = 'receita' $whereClause
                    GROUP BY cf.id_categoria, cf.nome
                    ORDER BY total DESC";
            break;
            
        case 'despesas_categoria':
            $sql = "SELECT 
                        cf.nome as categoria,
                        SUM(mf.valor) as total,
                        COUNT(*) as quantidade
                    FROM movimentacoes_financeiras mf 
                    LEFT JOIN categorias_financeiras cf ON mf.id_categoria = cf.id_categoria 
                    WHERE mf.tipo = 'despesa' $whereClause
                    GROUP BY cf.id_categoria, cf.nome
                    ORDER BY total DESC";
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Tipo de relatório não reconhecido']);
            return;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($paramsValues);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_' . $tipo . '_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    if ($tipo === 'fluxo_caixa') {
        // Cabeçalho
        fputcsv($output, ['Mês', 'Receitas', 'Despesas', 'Saldo'], ';');
        
        // Dados
        foreach ($dados as $linha) {
            fputcsv($output, [
                $linha['mes'],
                number_format($linha['receitas'], 2, ',', '.'),
                number_format($linha['despesas'], 2, ',', '.'),
                number_format($linha['saldo'], 2, ',', '.')
            ], ';');
        }
    } else {
        // Cabeçalho
        fputcsv($output, ['Categoria', 'Total', 'Quantidade'], ';');
        
        // Dados
        foreach ($dados as $linha) {
            fputcsv($output, [
                $linha['categoria'] ?? 'Sem categoria',
                number_format($linha['total'], 2, ',', '.'),
                $linha['quantidade']
            ], ';');
        }
    }
    
    fclose($output);
}

function exportarDados($params) {
    global $pdo;
    
    // Construir WHERE
    $where = [];
    $paramsValues = [];
    
    if (!empty($params['tipo'])) {
        $where[] = "mf.tipo = ?";
        $paramsValues[] = $params['tipo'];
    }
    
    if (!empty($params['categoria'])) {
        $where[] = "mf.id_categoria = ?";
        $paramsValues[] = $params['categoria'];
    }
    
    if (!empty($params['data_inicio'])) {
        $where[] = "mf.data_movimentacao >= ?";
        $paramsValues[] = $params['data_inicio'];
    }
    
    if (!empty($params['data_fim'])) {
        $where[] = "mf.data_movimentacao <= ?";
        $paramsValues[] = $params['data_fim'];
    }
    
    if (!empty($params['busca'])) {
        $where[] = "(mf.descricao LIKE ? OR mf.observacoes LIKE ?)";
        $busca = '%' . $params['busca'] . '%';
        $paramsValues[] = $busca;
        $paramsValues[] = $busca;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Buscar movimentações
    $sql = "SELECT mf.data_movimentacao, mf.descricao, cf.nome as categoria, mf.tipo, 
                   mf.valor, mf.forma_pagamento, mf.status, mf.observacoes
            FROM movimentacoes_financeiras mf 
            LEFT JOIN categorias_financeiras cf ON mf.id_categoria = cf.id_categoria 
            $whereClause 
            ORDER BY mf.data_movimentacao DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($paramsValues);
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="movimentacoes_financeiras_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalho
    fputcsv($output, [
        'Data', 'Descrição', 'Categoria', 'Tipo', 'Valor', 
        'Forma Pagamento', 'Status', 'Observações'
    ], ';');
    
    // Dados
    foreach ($movimentacoes as $mov) {
        fputcsv($output, [
            date('d/m/Y', strtotime($mov['data_movimentacao'])),
            $mov['descricao'],
            $mov['categoria'] ?? '',
            ucfirst($mov['tipo']),
            number_format($mov['valor'], 2, ',', '.'),
            $mov['forma_pagamento'] ? ucfirst(str_replace('_', ' ', $mov['forma_pagamento'])) : '',
            ucfirst($mov['status']),
            $mov['observacoes'] ?? ''
        ], ';');
    }
    
    fclose($output);
}
?> 