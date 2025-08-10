<?php
/**
 * LJ-OS Sistema para Lava Jato
 * API - Controle de Estoque
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
            listarProdutos($input);
            break;
            
        case 'buscar':
            buscarProduto($input);
            break;
            
        case 'salvar_produto':
            salvarProduto($input);
            break;
            
        case 'toggle_status':
            toggleStatusProduto($input);
            break;
            
        case 'listar_produtos_ativos':
            listarProdutosAtivos();
            break;
            
        case 'movimentar':
            movimentarEstoque($input);
            break;
            
        case 'historico':
            buscarHistorico($input);
            break;
            
        case 'exportar':
            exportarEstoque($_GET);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()]);
}

function listarProdutos($data) {
    global $pdo;
    
    $pagina = $data['pagina'] ?? 1;
    $itensPorPagina = 20;
    $offset = ($pagina - 1) * $itensPorPagina;
    
    // Construir WHERE
    $where = [];
    $params = [];
    
    if (!empty($data['categoria'])) {
        $where[] = "p.categoria = ?";
        $params[] = $data['categoria'];
    }
    
    if (!empty($data['status'])) {
        $where[] = "p.status = ?";
        $params[] = $data['status'];
    }
    
    if (!empty($data['busca'])) {
        $where[] = "(p.nome_produto LIKE ? OR p.codigo_produto LIKE ? OR p.descricao LIKE ?)";
        $busca = '%' . $data['busca'] . '%';
        $params[] = $busca;
        $params[] = $busca;
        $params[] = $busca;
    }
    
    if (!empty($data['estoque'])) {
        switch ($data['estoque']) {
            case 'baixo':
                $where[] = "p.estoque_atual <= p.estoque_minimo AND p.estoque_atual > 0";
                break;
            case 'normal':
                $where[] = "p.estoque_atual > p.estoque_minimo";
                break;
            case 'zerado':
                $where[] = "p.estoque_atual = 0";
                break;
        }
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Contar total
    $sqlCount = "SELECT COUNT(*) as total FROM produtos p $whereClause";
    $stmt = $pdo->prepare($sqlCount);
    $stmt->execute($params);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Buscar produtos
    $sql = "SELECT p.*, p.categoria as categoria_nome 
            FROM produtos p 
            $whereClause 
            ORDER BY p.nome_produto 
            LIMIT $itensPorPagina OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos,
        'total' => $total,
        'pagina' => $pagina,
        'total_paginas' => ceil($total / $itensPorPagina)
    ]);
}

function buscarProduto($data) {
    global $pdo;
    
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID do produto não informado']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT p.*, p.categoria as categoria_nome 
        FROM produtos p 
        WHERE p.id_produto = ?
    ");
    $stmt->execute([$data['id']]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($produto) {
        echo json_encode(['success' => true, 'produto' => $produto]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
    }
}

function salvarProduto($data) {
    global $pdo;
    
    // Validar dados obrigatórios
    if (empty($data['nome'])) {
        echo json_encode(['success' => false, 'message' => 'Nome do produto é obrigatório']);
        return;
    }
    
    $pdo->beginTransaction();
    
    try {
        if (empty($data['id'])) {
            // Novo produto
            $stmt = $pdo->prepare("
                INSERT INTO produtos (nome_produto, codigo_produto, categoria, unidade_medida, estoque_atual, 
                                    estoque_minimo, preco_venda, descricao, status, data_cadastro)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['nome'],
                $data['codigo'] ?? null,
                $data['categoria'] ?? null,
                $data['unidade'] ?? 'unidade',
                $data['quantidade_estoque'] ?? 0,
                $data['estoque_minimo'] ?? 0,
                $data['preco_unitario'] ?? 0,
                $data['descricao'] ?? null,
                $data['status'] ?? 'ativo'
            ]);
            
            $id = $pdo->lastInsertId();
            
            // Registrar movimentação inicial se houver estoque
            if (!empty($data['quantidade_estoque']) && $data['quantidade_estoque'] > 0) {
                $stmt = $pdo->prepare("
                    INSERT INTO movimentacoes_estoque (id_produto, tipo_movimentacao, quantidade, valor_total, motivo, data_movimentacao)
                    VALUES (?, 'entrada', ?, ?, 'Estoque inicial', NOW())
                ");
                $stmt->execute([$id, $data['quantidade_estoque'], $data['quantidade_estoque'] * ($data['preco_unitario'] ?? 0)]);
            }
            
        } else {
            // Atualizar produto
            $stmt = $pdo->prepare("
                UPDATE produtos SET 
                    nome_produto = ?, codigo_produto = ?, categoria = ?, unidade_medida = ?, 
                    estoque_minimo = ?, preco_venda = ?, descricao = ?, status = ?
                WHERE id_produto = ?
            ");
            
            $stmt->execute([
                $data['nome'],
                $data['codigo'] ?? null,
                $data['categoria'] ?? null,
                $data['unidade'] ?? 'unidade',
                $data['estoque_minimo'] ?? 0,
                $data['preco_unitario'] ?? 0,
                $data['descricao'] ?? null,
                $data['status'] ?? 'ativo',
                $data['id']
            ]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Produto salvo com sucesso']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar produto: ' . $e->getMessage()]);
    }
}

function toggleStatusProduto($data) {
    global $pdo;
    
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID do produto não informado']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE produtos SET status = ? WHERE id_produto = ?");
    $result = $stmt->execute([$data['status'], $data['id']]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Status alterado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar status']);
    }
}

function listarProdutosAtivos() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id_produto, nome_produto, estoque_atual, unidade_medida 
        FROM produtos 
        WHERE status = 'ativo' 
        ORDER BY nome_produto
    ");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'produtos' => $produtos]);
}

function movimentarEstoque($data) {
    global $pdo;
    
    // Validar dados obrigatórios
    if (empty($data['id_produto']) || empty($data['tipo']) || empty($data['quantidade'])) {
        echo json_encode(['success' => false, 'message' => 'Dados obrigatórios não informados']);
        return;
    }
    
    $pdo->beginTransaction();
    
    try {
        // Buscar produto atual
        $stmt = $pdo->prepare("SELECT estoque_atual FROM produtos WHERE id_produto = ?");
        $stmt->execute([$data['id_produto']]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produto) {
            throw new Exception('Produto não encontrado');
        }
        
        $estoqueAtual = $produto['estoque_atual'];
        $quantidade = floatval($data['quantidade']);
        
        // Calcular novo estoque
        switch ($data['tipo']) {
            case 'entrada':
                $novoEstoque = $estoqueAtual + $quantidade;
                break;
            case 'saida':
                if ($estoqueAtual < $quantidade) {
                    throw new Exception('Estoque insuficiente para esta saída');
                }
                $novoEstoque = $estoqueAtual - $quantidade;
                break;
            case 'ajuste':
                $novoEstoque = $quantidade;
                break;
            default:
                throw new Exception('Tipo de movimentação inválido');
        }
        
        // Atualizar estoque
        $stmt = $pdo->prepare("UPDATE produtos SET estoque_atual = ? WHERE id_produto = ?");
        $stmt->execute([$novoEstoque, $data['id_produto']]);
        
        // Registrar movimentação
        $stmt = $pdo->prepare("
            INSERT INTO movimentacoes_estoque (id_produto, tipo_movimentacao, quantidade, valor_total, motivo, data_movimentacao)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['id_produto'],
            $data['tipo'],
            $quantidade,
            0, // valor_total será calculado se necessário
            $data['motivo'] ?? null
        ]);
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Movimentação registrada com sucesso']);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function buscarHistorico($data) {
    global $pdo;
    
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID do produto não informado']);
        return;
    }
    
    $stmt = $pdo->prepare("
        SELECT tipo_movimentacao, quantidade, valor_total, motivo, data_movimentacao
        FROM movimentacoes_estoque 
        WHERE id_produto = ? 
        ORDER BY data_movimentacao DESC 
        LIMIT 50
    ");
    $stmt->execute([$data['id']]);
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'movimentacoes' => $movimentacoes]);
}

function exportarEstoque($params) {
    global $pdo;
    
    // Construir WHERE
    $where = [];
    $paramsValues = [];
    
    if (!empty($params['categoria'])) {
        $where[] = "p.categoria = ?";
        $paramsValues[] = $params['categoria'];
    }
    
    if (!empty($params['status'])) {
        $where[] = "p.status = ?";
        $paramsValues[] = $params['status'];
    }
    
    if (!empty($params['busca'])) {
        $where[] = "(p.nome_produto LIKE ? OR p.codigo_produto LIKE ? OR p.descricao LIKE ?)";
        $busca = '%' . $params['busca'] . '%';
        $paramsValues[] = $busca;
        $paramsValues[] = $busca;
        $paramsValues[] = $busca;
    }
    
    if (!empty($params['estoque'])) {
        switch ($params['estoque']) {
            case 'baixo':
                $where[] = "p.estoque_atual <= p.estoque_minimo AND p.estoque_atual > 0";
                break;
            case 'normal':
                $where[] = "p.estoque_atual > p.estoque_minimo";
                break;
            case 'zerado':
                $where[] = "p.estoque_atual = 0";
                break;
        }
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Buscar produtos
    $sql = "SELECT p.codigo_produto, p.nome_produto, p.categoria, p.estoque_atual, p.unidade_medida, 
                   p.estoque_minimo, p.preco_venda, p.status, p.data_cadastro
            FROM produtos p 
            $whereClause 
            ORDER BY p.nome_produto";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($paramsValues);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Configurar headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="estoque_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalho
    fputcsv($output, [
        'Código', 'Nome', 'Categoria', 'Estoque Atual', 'Unidade', 
        'Estoque Mínimo', 'Preço Unitário', 'Fornecedor', 'Status', 'Data Cadastro'
    ], ';');
    
    // Dados
    foreach ($produtos as $produto) {
        fputcsv($output, [
            $produto['codigo_produto'] ?? '',
            $produto['nome_produto'],
            $produto['categoria'] ?? '',
            $produto['estoque_atual'],
            $produto['unidade_medida'],
            $produto['estoque_minimo'],
            number_format($produto['preco_venda'], 2, ',', '.'),
            $produto['status'] === 'ativo' ? 'Ativo' : 'Inativo',
            date('d/m/Y H:i', strtotime($produto['data_cadastro']))
        ], ';');
    }
    
    fclose($output);
}
?> 