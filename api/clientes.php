<?php

/**
 * API de Clientes - LJ-OS
 * Endpoints para CRUD de clientes
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Carregar dependências
require_once __DIR__ . '/../src/Database/Database.php';
require_once __DIR__ . '/../src/Models/Cliente.php';
require_once __DIR__ . '/../src/Auth/JWTAuth.php';

use LJOS\Models\Cliente;
use LJOS\Auth\JWTAuth;

// Verificar autenticação
$auth = new JWTAuth();
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não autenticado',
        'timestamp' => date('c')
    ]);
    exit();
}

// Verificar permissão
$user = $auth->getCurrentUser();
$tokenPayload = $auth->decodeToken($auth->extractTokenFromHeader());

if (!$auth->hasPermission($tokenPayload, 'clientes.read')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado',
        'timestamp' => date('c')
    ]);
    exit();
}

try {
    $clienteModel = new Cliente();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGet($clienteModel);
            break;
            
        case 'POST':
            handlePost($clienteModel, $auth);
            break;
            
        case 'PUT':
            handlePut($clienteModel, $auth);
            break;
            
        case 'DELETE':
            handleDelete($clienteModel, $auth);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método não permitido',
                'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE']
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno: ' . $e->getMessage(),
        'timestamp' => date('c')
    ]);
}

/**
 * Processa requisições GET
 */
function handleGet(Cliente $clienteModel): void
{
    $id = $_GET['id'] ?? null;
    $search = $_GET['search'] ?? null;
    $tipo = $_GET['tipo'] ?? null;
    $cidade = $_GET['cidade'] ?? null;
    $estado = $_GET['estado'] ?? null;
    $segmento = $_GET['segmento'] ?? null;
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 15);
    
    if ($id) {
        // Buscar cliente específico
        $cliente = $clienteModel->find($id);
        
        if (!$cliente) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Cliente não encontrado',
                'timestamp' => date('c')
            ]);
            return;
        }
        
        // Buscar dados relacionados
        $veiculos = $clienteModel->findWithVeiculos($id);
        $historico = $clienteModel->findWithHistorico($id);
        $agendamentos = $clienteModel->findWithAgendamentos($id);
        
        $cliente['veiculos'] = $veiculos;
        $cliente['historico'] = $historico;
        $cliente['agendamentos'] = $agendamentos;
        
        echo json_encode([
            'success' => true,
            'message' => 'Cliente encontrado com sucesso',
            'data' => $cliente,
            'timestamp' => date('c')
        ]);
        
    } elseif ($search) {
        // Busca textual
        $clientes = $clienteModel->search($search);
        
        echo json_encode([
            'success' => true,
            'message' => 'Busca realizada com sucesso',
            'data' => $clientes,
            'total' => count($clientes),
            'timestamp' => date('c')
        ]);
        
    } elseif ($segmento) {
        // Busca por segmento
        $clientes = $clienteModel->findPorSegmento($segmento);
        
        echo json_encode([
            'success' => true,
            'message' => 'Clientes por segmento obtidos com sucesso',
            'data' => $clientes,
            'segmento' => $segmento,
            'total' => count($clientes),
            'timestamp' => date('c')
        ]);
        
    } else {
        // Listar clientes com filtros
        $conditions = [];
        
        if ($tipo) {
            $conditions[] = ['tipo_pessoa', '=', $tipo];
        }
        
        if ($cidade) {
            $conditions[] = ['cidade', '=', $cidade];
        }
        
        if ($estado) {
            $conditions[] = ['estado', '=', $estado];
        }
        
        if (!empty($conditions)) {
            $clientes = $clienteModel->whereMultiple($conditions);
        } else {
            $clientes = $clienteModel->paginate($page, $limit);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Clientes obtidos com sucesso',
            'data' => $clientes,
            'timestamp' => date('c')
        ]);
    }
}

/**
 * Processa requisições POST
 */
function handlePost(Cliente $clienteModel, JWTAuth $auth): void
{
    // Verificar permissão de criação
    $tokenPayload = $auth->decodeToken($auth->extractTokenFromHeader());
    if (!$auth->hasPermission($tokenPayload, 'clientes.create')) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acesso negado para criação',
            'timestamp' => date('c')
        ]);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    // Validações básicas
    if (empty($input['nome'])) {
        throw new Exception('Nome é obrigatório');
    }
    
    if (empty($input['cpf_cnpj'])) {
        throw new Exception('CPF/CNPJ é obrigatório');
    }
    
    if (empty($input['telefone'])) {
        throw new Exception('Telefone é obrigatório');
    }
    
    // Verificar se CPF/CNPJ já existe
    $existente = $clienteModel->findByCpfCnpj($input['cpf_cnpj']);
    if ($existente) {
        throw new Exception('CPF/CNPJ já cadastrado');
    }
    
    // Verificar se telefone já existe
    $existente = $clienteModel->findByTelefone($input['telefone']);
    if ($existente) {
        throw new Exception('Telefone já cadastrado');
    }
    
    // Verificar se email já existe (se fornecido)
    if (!empty($input['email'])) {
        $existente = $clienteModel->findByEmail($input['email']);
        if ($existente) {
            throw new Exception('Email já cadastrado');
        }
    }
    
    // Preparar dados
    $dados = [
        'nome' => trim($input['nome']),
        'tipo_pessoa' => $input['tipo_pessoa'] ?? 'PF',
        'cpf_cnpj' => trim($input['cpf_cnpj']),
        'rg_ie' => $input['rg_ie'] ?? null,
        'telefone' => trim($input['telefone']),
        'email' => $input['email'] ?? null,
        'endereco' => $input['endereco'] ?? null,
        'cep' => $input['cep'] ?? null,
        'cidade' => $input['cidade'] ?? null,
        'estado' => $input['estado'] ?? null,
        'data_nascimento' => $input['data_nascimento'] ?? null,
        'observacoes' => $input['observacoes'] ?? null,
        'programa_fidelidade' => $input['programa_fidelidade'] ?? false,
        'pontos_fidelidade' => $input['pontos_fidelidade'] ?? 0
    ];
    
    // Criar cliente
    $id = $clienteModel->create($dados);
    
    // Buscar cliente criado
    $cliente = $clienteModel->find($id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cliente criado com sucesso',
        'data' => $cliente,
        'id' => $id,
        'timestamp' => date('c')
    ]);
}

/**
 * Processa requisições PUT
 */
function handlePut(Cliente $clienteModel, JWTAuth $auth): void
{
    // Verificar permissão de atualização
    $tokenPayload = $auth->decodeToken($auth->extractTokenFromHeader());
    if (!$auth->hasPermission($tokenPayload, 'clientes.update')) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acesso negado para atualização',
            'timestamp' => date('c')
        ]);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception('ID do cliente é obrigatório');
    }
    
    $id = (int)$input['id'];
    
    // Verificar se cliente existe
    $cliente = $clienteModel->find($id);
    if (!$cliente) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Cliente não encontrado',
            'timestamp' => date('c')
        ]);
        return;
    }
    
    // Preparar dados para atualização
    $dados = [];
    
    if (isset($input['nome'])) {
        $dados['nome'] = trim($input['nome']);
    }
    
    if (isset($input['rg_ie'])) {
        $dados['rg_ie'] = $input['rg_ie'];
    }
    
    if (isset($input['telefone'])) {
        $dados['telefone'] = trim($input['telefone']);
    }
    
    if (isset($input['email'])) {
        $dados['email'] = $input['email'];
    }
    
    if (isset($input['endereco'])) {
        $dados['endereco'] = $input['endereco'];
    }
    
    if (isset($input['cep'])) {
        $dados['cep'] = $input['cep'];
    }
    
    if (isset($input['cidade'])) {
        $dados['cidade'] = $input['cidade'];
    }
    
    if (isset($input['estado'])) {
        $dados['estado'] = $input['estado'];
    }
    
    if (isset($input['data_nascimento'])) {
        $dados['data_nascimento'] = $input['data_nascimento'];
    }
    
    if (isset($input['observacoes'])) {
        $dados['observacoes'] = $input['observacoes'];
    }
    
    if (isset($input['programa_fidelidade'])) {
        $dados['programa_fidelidade'] = (bool)$input['programa_fidelidade'];
    }
    
    if (isset($input['pontos_fidelidade'])) {
        $dados['pontos_fidelidade'] = (int)$input['pontos_fidelidade'];
    }
    
    if (empty($dados)) {
        throw new Exception('Nenhum dado fornecido para atualização');
    }
    
    // Atualizar cliente
    $success = $clienteModel->update($id, $dados);
    
    if (!$success) {
        throw new Exception('Erro ao atualizar cliente');
    }
    
    // Buscar cliente atualizado
    $cliente = $clienteModel->find($id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Cliente atualizado com sucesso',
        'data' => $cliente,
        'timestamp' => date('c')
    ]);
}

/**
 * Processa requisições DELETE
 */
function handleDelete(Cliente $clienteModel, JWTAuth $auth): void
{
    // Verificar permissão de exclusão
    $tokenPayload = $auth->decodeToken($auth->extractTokenFromHeader());
    if (!$auth->hasPermission($tokenPayload, 'clientes.delete')) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acesso negado para exclusão',
            'timestamp' => date('c')
        ]);
        return;
    }
    
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        throw new Exception('ID do cliente é obrigatório');
    }
    
    $id = (int)$id;
    
    // Verificar se cliente existe
    $cliente = $clienteModel->find($id);
    if (!$cliente) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Cliente não encontrado',
            'timestamp' => date('c')
        ]);
        return;
    }
    
    // Excluir cliente
    $success = $clienteModel->delete($id);
    
    if (!$success) {
        throw new Exception('Erro ao excluir cliente');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Cliente excluído com sucesso',
        'id' => $id,
        'timestamp' => date('c')
    ]);
}
