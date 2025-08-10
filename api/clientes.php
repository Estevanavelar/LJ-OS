<?php
/**
 * API para Clientes
 * LJ-OS Sistema para Lava Jato
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Verificar se o usuário está logado
if (!estaLogado()) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit();
}

// Validar CSRF para métodos que modificam estado
$unsafeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', $unsafeMethods, true)) {
    csrf_verificar_api();
}

$acao = $_GET['acao'] ?? $_POST['acao'] ?? '';

try {
    $db = getDB();
    
    switch ($acao) {
        case 'buscar':
            $id_cliente = $_GET['id'] ?? null;
            if (!$id_cliente) {
                throw new Exception('ID do cliente não informado');
            }
            
            $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id_cliente]);
            $cliente = $stmt->fetch();
            
            if (!$cliente) {
                throw new Exception('Cliente não encontrado');
            }
            
            echo json_encode(['sucesso' => true, 'cliente' => $cliente]);
            break;
            
        case 'listar':
            $busca = sanitizar($_GET['busca'] ?? '');
            $tipo_pessoa = sanitizar($_GET['tipo_pessoa'] ?? '');
            $status = sanitizar($_GET['status'] ?? 'ativo');
            
            $where_conditions = ["status = ?"];
            $params = [$status];
            
            if (!empty($busca)) {
                $where_conditions[] = "(nome LIKE ? OR cpf_cnpj LIKE ? OR telefone LIKE ?)";
                $params[] = "%$busca%";
                $params[] = "%$busca%";
                $params[] = "%$busca%";
            }
            
            if (!empty($tipo_pessoa)) {
                $where_conditions[] = "tipo_pessoa = ?";
                $params[] = $tipo_pessoa;
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            $sql = "SELECT * FROM clientes WHERE $where_clause ORDER BY nome ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll();
            
            echo json_encode(['sucesso' => true, 'clientes' => $clientes]);
            break;
            
        case 'toggle_status':
            $input = json_decode(file_get_contents('php://input'), true);
            $id_cliente = $input['id_cliente'] ?? null;
            $status = $input['status'] ?? null;
            
            if (!$id_cliente || !$status) {
                throw new Exception('Dados incompletos');
            }
            
            $sql = "UPDATE clientes SET status = ? WHERE id_cliente = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$status, $id_cliente]);
            
            registrarLog("Status do cliente alterado para $status", 'clientes', $id_cliente);
            
            echo json_encode(['sucesso' => true, 'mensagem' => 'Status alterado com sucesso']);
            break;
            
        case 'consultar_cep':
            $cep = sanitizar($_GET['cep'] ?? '');
            if (empty($cep)) {
                throw new Exception('CEP não informado');
            }
            
            // Remover caracteres não numéricos
            $cep = preg_replace('/\D/', '', $cep);
            
            if (strlen($cep) !== 8) {
                throw new Exception('CEP deve ter 8 dígitos');
            }
            
            // Consultar via CEP
            $url = "https://viacep.com.br/ws/{$cep}/json/";
            $response = @file_get_contents($url);
            if ($response === false) {
                throw new Exception('Erro ao consultar CEP');
            }
            $data = json_decode($response, true);
            
            if (!$data || isset($data['erro'])) {
                throw new Exception('CEP não encontrado');
            }
            
            echo json_encode(['sucesso' => true, 'endereco' => $data]);
            break;
            
        case 'consultar_cnpj':
            $cnpj = sanitizar($_GET['cnpj'] ?? '');
            if (empty($cnpj)) {
                throw new Exception('CNPJ não informado');
            }
            
            // Remover caracteres não numéricos
            $cnpj = preg_replace('/\D/', '', $cnpj);
            
            if (strlen($cnpj) !== 14) {
                throw new Exception('CNPJ deve ter 14 dígitos');
            }
            
            // Consultar via API pública do CNPJ
            $url = "https://publica.cnpj.ws/cnpj/{$cnpj}";
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'LJ-OS-Sistema/1.0'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                throw new Exception('Erro ao consultar CNPJ');
            }
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['estabelecimento'])) {
                throw new Exception('CNPJ não encontrado');
            }
            
            echo json_encode(['sucesso' => true, 'empresa' => $data]);
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => $e->getMessage()]);
}
?> 