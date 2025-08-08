<?php
/**
 * API para Veículos
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
            if (!$id) throw new Exception('ID do veículo é obrigatório');
            
            $stmt = $db->prepare("
                SELECT v.*, c.nome as nome_cliente, c.cpf_cnpj 
                FROM veiculos v 
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente 
                WHERE v.id_veiculo = ?
            ");
            $stmt->execute([$id]);
            $veiculo = $stmt->fetch();
            
            if (!$veiculo) throw new Exception('Veículo não encontrado');
            
            echo json_encode(['sucesso' => true, 'veiculo' => $veiculo]);
            break;
            
        case 'listar':
            $cliente = $_GET['cliente'] ?? '';
            $placa = $_GET['placa'] ?? '';
            $cliente_id = (int)($_GET['cliente_id'] ?? 0);
            
            $where = "WHERE v.status = 'ativo'";
            $params = [];
            
            if (!empty($cliente)) {
                $where .= " AND c.nome LIKE ?";
                $params[] = "%$cliente%";
            }
            
            if (!empty($placa)) {
                $where .= " AND v.placa LIKE ?";
                $params[] = "%$placa%";
            }
            
            if ($cliente_id > 0) {
                $where .= " AND v.id_cliente = ?";
                $params[] = $cliente_id;
            }
            
            $sql = "
                SELECT v.*, c.nome as nome_cliente, c.cpf_cnpj 
                FROM veiculos v 
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente 
                $where 
                ORDER BY v.data_cadastro DESC
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $veiculos = $stmt->fetchAll();
            
            echo json_encode(['sucesso' => true, 'veiculos' => $veiculos]);
            break;
            
        case 'toggle_status':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID do veículo é obrigatório');
            
            $stmt = $db->prepare("UPDATE veiculos SET status = 'inativo' WHERE id_veiculo = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['sucesso' => true, 'mensagem' => 'Veículo inativado com sucesso']);
            } else {
                throw new Exception('Veículo não encontrado');
            }
            break;
            
        case 'buscar_por_cliente':
            $cliente_id = (int)($_GET['cliente_id'] ?? 0);
            if (!$cliente_id) throw new Exception('ID do cliente é obrigatório');
            
            $stmt = $db->prepare("
                SELECT v.*, c.nome as nome_cliente 
                FROM veiculos v 
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente 
                WHERE v.id_cliente = ? AND v.status = 'ativo' 
                ORDER BY v.data_cadastro DESC
            ");
            $stmt->execute([$cliente_id]);
            $veiculos = $stmt->fetchAll();
            
            echo json_encode(['sucesso' => true, 'veiculos' => $veiculos]);
            break;
            
        case 'verificar_placa':
            $placa = strtoupper(sanitizar($_GET['placa'] ?? ''));
            $id_veiculo = (int)($_GET['id_veiculo'] ?? 0);
            
            if (empty($placa)) throw new Exception('Placa é obrigatória');
            
            $sql = "SELECT id_veiculo FROM veiculos WHERE placa = ? AND status = 'ativo'";
            $params = [$placa];
            
            if ($id_veiculo > 0) {
                $sql .= " AND id_veiculo != ?";
                $params[] = $id_veiculo;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $existe = $stmt->rowCount() > 0;
            echo json_encode(['sucesso' => true, 'existe' => $existe]);
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?> 