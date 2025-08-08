<?php
/**
 * API para Agendamentos
 * LJ-OS Sistema para Lava Jato
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/functions.php';

// Verificar se o usuário está logado
if (!verificarLogin()) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit();
}

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

try {
    $db = getDB();
    
    switch ($acao) {
        case 'buscar':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception('ID do agendamento é obrigatório');
            
            $stmt = $db->prepare("
                SELECT a.*, c.nome as nome_cliente, c.telefone, v.placa, v.marca, v.modelo, s.nome_servico, s.preco
                FROM agendamentos a
                LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
                LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
                LEFT JOIN servicos s ON a.id_servico = s.id_servico
                WHERE a.id_agendamento = ?
            ");
            $stmt->execute([$id]);
            $agendamento = $stmt->fetch();
            
            if (!$agendamento) throw new Exception('Agendamento não encontrado');
            
            echo json_encode(['sucesso' => true, 'agendamento' => $agendamento]);
            break;
            
        case 'listar':
            $data = $_GET['data'] ?? '';
            $cliente = $_GET['cliente'] ?? '';
            $status = $_GET['status'] ?? '';
            $mes = $_GET['mes'] ?? '';
            $ano = $_GET['ano'] ?? '';
            
            $where = "WHERE 1=1";
            $params = [];
            
            if (!empty($data)) {
                $where .= " AND DATE(a.data_agendamento) = ?";
                $params[] = $data;
            }
            
            if (!empty($cliente)) {
                $where .= " AND c.nome LIKE ?";
                $params[] = "%$cliente%";
            }
            
            if (!empty($status)) {
                $where .= " AND a.status = ?";
                $params[] = $status;
            }
            
            if (!empty($mes) && !empty($ano)) {
                $where .= " AND MONTH(a.data_agendamento) = ? AND YEAR(a.data_agendamento) = ?";
                $params[] = $mes;
                $params[] = $ano;
            }
            
            $sql = "
                SELECT a.*, c.nome as nome_cliente, c.telefone, v.placa, v.marca, v.modelo, s.nome_servico, s.preco
                FROM agendamentos a
                LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
                LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
                LEFT JOIN servicos s ON a.id_servico = s.id_servico
                $where
                ORDER BY a.data_agendamento ASC
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $agendamentos = $stmt->fetchAll();
            
            echo json_encode(['sucesso' => true, 'agendamentos' => $agendamentos]);
            break;
            
        case 'alterar_status':
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if (!$id) throw new Exception('ID do agendamento é obrigatório');
            if (!in_array($status, ['pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado'])) {
                throw new Exception('Status inválido');
            }
            
            $stmt = $db->prepare("UPDATE agendamentos SET status = ? WHERE id_agendamento = ?");
            $stmt->execute([$status, $id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Status alterado com sucesso']);
            } else {
                throw new Exception('Agendamento não encontrado');
            }
            break;
            
        case 'verificar_disponibilidade':
            $data = $_GET['data'] ?? '';
            $hora = $_GET['hora'] ?? '';
            $id_agendamento = (int)($_GET['id_agendamento'] ?? 0);
            
            if (empty($data) || empty($hora)) throw new Exception('Data e hora são obrigatórias');
            
            $data_hora = $data . ' ' . $hora;
            
            $sql = "SELECT id_agendamento FROM agendamentos WHERE data_agendamento = ? AND status != 'cancelado'";
            $params = [$data_hora];
            
            if ($id_agendamento > 0) {
                $sql .= " AND id_agendamento != ?";
                $params[] = $id_agendamento;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $ocupado = $stmt->rowCount() > 0;
            echo json_encode(['sucesso' => true, 'ocupado' => $ocupado]);
            break;
            
        case 'agendamentos_hoje':
            $hoje = date('Y-m-d');
            
            $stmt = $db->prepare("
                SELECT a.*, c.nome as nome_cliente, c.telefone, v.placa, s.nome_servico
                FROM agendamentos a
                LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
                LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
                LEFT JOIN servicos s ON a.id_servico = s.id_servico
                WHERE DATE(a.data_agendamento) = ? AND a.status IN ('pendente', 'confirmado')
                ORDER BY a.data_agendamento ASC
            ");
            $stmt->execute([$hoje]);
            $agendamentos = $stmt->fetchAll();
            
            echo json_encode(['sucesso' => true, 'agendamentos' => $agendamentos]);
            break;
            
        case 'agendamentos_semana':
            $inicio_semana = date('Y-m-d', strtotime('monday this week'));
            $fim_semana = date('Y-m-d', strtotime('sunday this week'));
            
            $stmt = $db->prepare("
                SELECT a.*, c.nome as nome_cliente, v.placa, s.nome_servico
                FROM agendamentos a
                LEFT JOIN clientes c ON a.id_cliente = c.id_cliente
                LEFT JOIN veiculos v ON a.id_veiculo = v.id_veiculo
                LEFT JOIN servicos s ON a.id_servico = s.id_servico
                WHERE DATE(a.data_agendamento) BETWEEN ? AND ? AND a.status != 'cancelado'
                ORDER BY a.data_agendamento ASC
            ");
            $stmt->execute([$inicio_semana, $fim_semana]);
            $agendamentos = $stmt->fetchAll();
            
            echo json_encode(['sucesso' => true, 'agendamentos' => $agendamentos]);
            break;
            
        case 'estatisticas':
            $mes = $_GET['mes'] ?? date('m');
            $ano = $_GET['ano'] ?? date('Y');
            
            // Total de agendamentos no mês
            $stmt = $db->prepare("
                SELECT COUNT(*) as total, 
                       SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                       SUM(CASE WHEN status = 'confirmado' THEN 1 ELSE 0 END) as confirmados,
                       SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
                       SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
                       SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados
                FROM agendamentos 
                WHERE MONTH(data_agendamento) = ? AND YEAR(data_agendamento) = ?
            ");
            $stmt->execute([$mes, $ano]);
            $stats = $stmt->fetch();
            
            echo json_encode(['sucesso' => true, 'estatisticas' => $stats]);
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?> 