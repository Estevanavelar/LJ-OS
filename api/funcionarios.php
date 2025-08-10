<?php
/**
 * API de Funcionários
 * LJ-OS Sistema para Lava Jato
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

// Verificar se o usuário está logado
if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}

// Validar CSRF para métodos que modificam estado
$unsafeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', $unsafeMethods, true)) {
    csrf_verificar_api();
}

// Obter conexão com o banco de dados
$pdo = getDB();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'estatisticas':
            getEstatisticas();
            break;
        case 'listar':
            listarFuncionarios();
            break;
        case 'presenca':
            getPresenca();
            break;
        case 'produtividade':
            getProdutividade();
            break;
        case 'vendas':
            getVendas();
            break;
        case 'visualizar':
            visualizarFuncionario();
            break;
        case 'editar':
            editarFuncionario();
            break;
        case 'excluir':
            excluirFuncionario();
            break;
        default:
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                criarFuncionario();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                atualizarFuncionario();
            } else {
                http_response_code(400);
                echo json_encode(['erro' => 'Ação não reconhecida']);
            }
    }
} catch (Exception $e) {
    error_log("Erro na API de funcionários: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno do servidor']);
}

function getEstatisticas() {
    global $pdo;
    
    try {
        // Funcionários ativos
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM funcionarios WHERE status = 'ativo'");
        $stmt->execute();
        $ativos = $stmt->fetch()['total'];
        
        // Presentes hoje
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT f.id_funcionario) as total 
            FROM funcionarios f 
            JOIN presenca_funcionarios p ON f.id_funcionario = p.id_funcionario 
            WHERE f.status = 'ativo' 
            AND DATE(p.data) = CURDATE() 
            AND p.tipo = 'entrada'
        ");
        $stmt->execute();
        $presentes_hoje = $stmt->fetch()['total'];
        
        // Carros atendidos hoje
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM ordens_servico 
            WHERE DATE(data_abertura) = CURDATE() 
            AND status = 'finalizada'
        ");
        $stmt->execute();
        $carros_atendidos = $stmt->fetch()['total'];
        
        // Vendas do mês
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(valor_total), 0) as total 
            FROM ordens_servico 
            WHERE MONTH(data_abertura) = MONTH(CURDATE()) 
            AND YEAR(data_abertura) = YEAR(CURDATE()) 
            AND status = 'finalizada'
        ");
        $stmt->execute();
        $vendas_mes = $stmt->fetch()['total'];
        
        echo json_encode([
            'sucesso' => true,
            'estatisticas' => [
                'ativos' => $ativos,
                'presentes_hoje' => $presentes_hoje,
                'carros_atendidos' => $carros_atendidos,
                'vendas_mes' => $vendas_mes
            ]
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao buscar estatísticas: ' . $e->getMessage());
    }
}

function listarFuncionarios() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM funcionarios 
            ORDER BY nome ASC
        ");
        $stmt->execute();
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'funcionarios' => $funcionarios
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao listar funcionários: ' . $e->getMessage());
    }
}

function getPresenca() {
    global $pdo;
    
    $data = $_GET['data'] ?? date('Y-m-d');
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.*,
                f.nome as funcionario_nome,
                TIME_FORMAT(p.hora, '%H:%i') as hora_formatada,
                CASE 
                    WHEN p.tipo = 'entrada' THEN TIME_FORMAT(p.hora, '%H:%i')
                    ELSE NULL 
                END as entrada,
                CASE 
                    WHEN p.tipo = 'saida' THEN TIME_FORMAT(p.hora, '%H:%i')
                    ELSE NULL 
                END as saida,
                CASE 
                    WHEN p.tipo = 'saida' AND p_entrada.hora IS NOT NULL 
                    THEN TIMEDIFF(p.hora, p_entrada.hora)
                    ELSE NULL 
                END as total_horas,
                CASE 
                    WHEN p.tipo = 'entrada' AND TIME(p.hora) > '08:00:00' THEN 'atraso'
                    WHEN p.tipo = 'saida' AND TIME(p.hora) < '17:00:00' THEN 'saida_antecipada'
                    ELSE 'presente'
                END as status
            FROM presenca_funcionarios p
            JOIN funcionarios f ON p.id_funcionario = f.id_funcionario
            LEFT JOIN presenca_funcionarios p_entrada ON p.id_funcionario = p_entrada.id_funcionario 
                AND DATE(p.data) = DATE(p_entrada.data) 
                AND p_entrada.tipo = 'entrada'
            WHERE DATE(p.data) = ?
            ORDER BY f.nome, p.hora
        ");
        $stmt->execute([$data]);
        $presencas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'presencas' => $presencas
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao buscar presença: ' . $e->getMessage());
    }
}

function getProdutividade() {
    global $pdo;
    
    $funcionario = $_GET['funcionario'] ?? '';
    $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
    $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
    
    try {
        $where = "WHERE os.data_abertura BETWEEN ? AND ? AND os.status = 'finalizada'";
        $params = [$dataInicio, $dataFim];
        
        if ($funcionario) {
            $where .= " AND os.id_funcionario = ?";
            $params[] = $funcionario;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                f.id_funcionario,
                f.nome as funcionario_nome,
                COUNT(os.id_os) as carros_atendidos,
                SUM(TIMESTAMPDIFF(HOUR, os.data_abertura, os.data_fechamento)) as horas_trabalhadas,
                ROUND((COUNT(os.id_os) / DATEDIFF(?, ?)) * 100, 2) as eficiencia,
                SUM(os.valor_total) as valor_gerado
            FROM funcionarios f
            LEFT JOIN ordens_servico os ON f.id_funcionario = os.id_funcionario
            $where
            GROUP BY f.id_funcionario, f.nome
            ORDER BY carros_atendidos DESC
        ");
        
        $params = array_merge($params, [$dataFim, $dataInicio]);
        $stmt->execute($params);
        $produtividade = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'produtividade' => $produtividade
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao buscar produtividade: ' . $e->getMessage());
    }
}

function getVendas() {
    global $pdo;
    
    $funcionario = $_GET['funcionario'] ?? '';
    $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
    $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
    
    try {
        $where = "WHERE v.data_venda BETWEEN ? AND ?";
        $params = [$dataInicio, $dataFim];
        
        if ($funcionario) {
            $where .= " AND v.id_funcionario = ?";
            $params[] = $funcionario;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                v.id_venda,
                f.nome as funcionario_nome,
                COUNT(vp.id_produto) as produtos_vendidos,
                SUM(vp.quantidade * vp.preco_unitario) as valor_total,
                SUM(vp.quantidade * vp.preco_unitario * f.comissao / 100) as comissao,
                v.data_venda
            FROM vendas v
            JOIN funcionarios f ON v.id_funcionario = f.id_funcionario
            JOIN vendas_produtos vp ON v.id_venda = vp.id_venda
            $where
            GROUP BY v.id_venda, f.nome, v.data_venda
            ORDER BY v.data_venda DESC
        ");
        $stmt->execute($params);
        $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'sucesso' => true,
            'vendas' => $vendas
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao buscar vendas: ' . $e->getMessage());
    }
}

function criarFuncionario() {
    global $pdo;
    
    $nome = $_POST['nome'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $cpf = $_POST['cpf'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $email = $_POST['email'] ?? '';
    $data_admissao = $_POST['data_admissao'] ?? '';
    $salario = $_POST['salario'] ?? 0;
    $comissao = $_POST['comissao'] ?? 0;
    $endereco = $_POST['endereco'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    
    if (!$nome || !$cargo || !$cpf || !$telefone || !$data_admissao) {
        http_response_code(400);
        echo json_encode(['erro' => 'Campos obrigatórios não preenchidos']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO funcionarios (
                nome, cargo, cpf, telefone, email, data_admissao, 
                salario, comissao, endereco, observacoes, status, data_cadastro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo', NOW())
        ");
        
        $stmt->execute([
            $nome, $cargo, $cpf, $telefone, $email, $data_admissao,
            $salario, $comissao, $endereco, $observacoes
        ]);
        
        $id = $pdo->lastInsertId();
        
        // Registrar log
        registrarLog("Funcionário criado", $_SESSION['usuario_id'], null, $id, [
            'nome' => $nome,
            'cargo' => $cargo
        ]);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Funcionário criado com sucesso',
            'id' => $id
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao criar funcionário: ' . $e->getMessage());
    }
}

function atualizarFuncionario() {
    global $pdo;
    
    $input = file_get_contents('php://input');
    parse_str($input, $data);
    
    $id = $data['id'] ?? '';
    $nome = $data['nome'] ?? '';
    $cargo = $data['cargo'] ?? '';
    $cpf = $data['cpf'] ?? '';
    $telefone = $data['telefone'] ?? '';
    $email = $data['email'] ?? '';
    $data_admissao = $data['data_admissao'] ?? '';
    $salario = $data['salario'] ?? 0;
    $comissao = $data['comissao'] ?? 0;
    $endereco = $data['endereco'] ?? '';
    $observacoes = $data['observacoes'] ?? '';
    
    if (!$id || !$nome || !$cargo || !$cpf || !$telefone || !$data_admissao) {
        http_response_code(400);
        echo json_encode(['erro' => 'Campos obrigatórios não preenchidos']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE funcionarios SET 
                nome = ?, cargo = ?, cpf = ?, telefone = ?, email = ?, 
                data_admissao = ?, salario = ?, comissao = ?, endereco = ?, 
                observacoes = ?, data_atualizacao = NOW()
            WHERE id_funcionario = ?
        ");
        
        $stmt->execute([
            $nome, $cargo, $cpf, $telefone, $email, $data_admissao,
            $salario, $comissao, $endereco, $observacoes, $id
        ]);
        
        // Registrar log
        registrarLog("Funcionário atualizado", $_SESSION['usuario_id'], null, $id, [
            'nome' => $nome,
            'cargo' => $cargo
        ]);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Funcionário atualizado com sucesso'
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao atualizar funcionário: ' . $e->getMessage());
    }
}

function visualizarFuncionario() {
    global $pdo;
    
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do funcionário não informado']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM funcionarios WHERE id_funcionario = ?
        ");
        $stmt->execute([$id]);
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$funcionario) {
            http_response_code(404);
            echo json_encode(['erro' => 'Funcionário não encontrado']);
            return;
        }
        
        echo json_encode([
            'sucesso' => true,
            'funcionario' => $funcionario
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao visualizar funcionário: ' . $e->getMessage());
    }
}

function excluirFuncionario() {
    global $pdo;
    
    $id = $_GET['id'] ?? '';
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do funcionário não informado']);
        return;
    }
    
    try {
        // Verificar se o funcionário tem registros relacionados
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM ordens_servico WHERE id_funcionario = ?
        ");
        $stmt->execute([$id]);
        $total_os = $stmt->fetch()['total'];
        
        if ($total_os > 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'Não é possível excluir funcionário com ordens de serviço vinculadas']);
            return;
        }
        
        // Excluir funcionário
        $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id_funcionario = ?");
        $stmt->execute([$id]);
        
        // Registrar log
        registrarLog("Funcionário excluído", $_SESSION['usuario_id'], null, $id);
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Funcionário excluído com sucesso'
        ]);
    } catch (Exception $e) {
        throw new Exception('Erro ao excluir funcionário: ' . $e->getMessage());
    }
}
?> 