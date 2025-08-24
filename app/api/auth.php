<?php
/**
 * LJ-OS - API de Autenticação
 */

// Carregar autoloader
require_once __DIR__ . '/../../autoload.php';

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Carregar classes necessárias
use LJOS\Auth\JWTAuth;
use LJOS\Models\Usuario;

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    // Obter dados da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'login':
            handleLogin($input);
            break;
            
        case 'logout':
            handleLogout($input);
            break;
            
        case 'refresh':
            handleRefresh($input);
            break;
            
        case 'me':
            handleGetCurrentUser($input);
            break;
            
        default:
            throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}

/**
 * Processa login
 */
function handleLogin(array $input): void
{
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        throw new Exception('Email e senha são obrigatórios');
    }
    
    // Verificar usuário
    $usuario = new Usuario();
    $user = $usuario->findByEmail($email);
    
    if (!$user) {
        throw new Exception('Usuário não encontrado');
    }
    
    // Verificar senha
    if (!$usuario->verificarSenha($password, $user['senha'])) {
        throw new Exception('Senha incorreta');
    }
    
    // Verificar se usuário está ativo
    if ($user['status'] !== 'ATIVO') {
        throw new Exception('Usuário inativo');
    }
    
    // Gerar token JWT
    $auth = new JWTAuth();
    $token = $auth->generateToken([
        'user_id' => $user['id_usuario'],
        'email' => $user['email'],
        'nivel_acesso' => $user['nivel_acesso']
    ]);
    
    // Atualizar último login
    $usuario->update($user['id_usuario'], ['ultimo_login' => date('Y-m-d H:i:s')]);
    
    // Retornar resposta
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'data' => [
            'token' => $token,
            'user' => [
                'id' => $user['id_usuario'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'nivel_acesso' => $user['nivel_acesso']
            ]
        ],
        'timestamp' => date('c')
    ]);
}

/**
 * Processa logout
 */
function handleLogout(array $input): void
{
    // Em uma implementação real, você poderia invalidar o token
    // Por enquanto, apenas retornamos sucesso
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout realizado com sucesso',
        'timestamp' => date('c')
    ]);
}

/**
 * Processa refresh de token
 */
function handleRefresh(array $input): void
{
    // Obter token do header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        throw new Exception('Token não fornecido');
    }
    
    $token = $matches[1];
    
    // Verificar token atual
    $auth = new JWTAuth();
    $payload = $auth->validateToken($token);
    
    if (!$payload) {
        throw new Exception('Token inválido');
    }
    
    // Gerar novo token
    $newToken = $auth->generateToken([
        'user_id' => $payload['user_id'],
        'email' => $payload['email'],
        'nivel_acesso' => $payload['nivel_acesso']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Token renovado com sucesso',
        'data' => [
            'token' => $newToken
        ],
        'timestamp' => date('c')
    ]);
}

/**
 * Obtém dados do usuário atual
 */
function handleGetCurrentUser(array $input): void
{
    // Obter token do header
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';
    
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        throw new Exception('Token não fornecido');
    }
    
    $token = $matches[1];
    
    // Verificar token
    $auth = new JWTAuth();
    $payload = $auth->validateToken($token);
    
    if (!$payload) {
        throw new Exception('Token inválido');
    }
    
    // Obter dados do usuário
    $usuario = new Usuario();
    $user = $usuario->find($payload['user_id']);
    
    if (!$user) {
        throw new Exception('Usuário não encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $user['id_usuario'],
            'nome' => $user['nome'],
            'email' => $user['email'],
            'nivel_acesso' => $user['nivel_acesso'],
            'status' => $user['status']
        ],
        'timestamp' => date('c')
    ]);
}
