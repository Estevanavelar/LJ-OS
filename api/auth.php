<?php

/**
 * API de Autenticação - LJ-OS
 * Endpoints para login, logout e refresh de tokens
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido',
        'allowed_methods' => ['POST']
    ]);
    exit();
}

// Carregar dependências
require_once __DIR__ . '/../src/Database/Database.php';
require_once __DIR__ . '/../src/Models/Usuario.php';
require_once __DIR__ . '/../src/Auth/JWTAuth.php';

use LJOS\Auth\JWTAuth;
use LJOS\Models\Usuario;

try {
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
            
        case 'refresh':
            handleRefresh($input);
            break;
            
        case 'logout':
            handleLogout($input);
            break;
            
        case 'me':
            handleMe($input);
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
 * Processa login do usuário
 */
function handleLogin(array $input): void
{
    $email = $input['email'] ?? '';
    $senha = $input['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        throw new Exception('Email e senha são obrigatórios');
    }
    
    $auth = new JWTAuth();
    $result = $auth->authenticate($email, $senha);
    
    echo json_encode([
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'data' => $result,
        'timestamp' => date('c')
    ]);
}

/**
 * Processa refresh do token
 */
function handleRefresh(array $input): void
{
    $refreshToken = $input['refresh_token'] ?? '';
    
    if (empty($refreshToken)) {
        throw new Exception('Refresh token é obrigatório');
    }
    
    $auth = new JWTAuth();
    $newToken = $auth->refreshToken($refreshToken);
    
    echo json_encode([
        'success' => true,
        'message' => 'Token renovado com sucesso',
        'data' => [
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ],
        'timestamp' => date('c')
    ]);
}

/**
 * Processa logout do usuário
 */
function handleLogout(array $input): void
{
    $token = $input['token'] ?? '';
    
    if (empty($token)) {
        throw new Exception('Token é obrigatório');
    }
    
    $auth = new JWTAuth();
    $auth->invalidateToken($token);
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout realizado com sucesso',
        'timestamp' => date('c')
    ]);
}

/**
 * Retorna dados do usuário atual
 */
function handleMe(array $input): void
{
    $auth = new JWTAuth();
    $user = $auth->getCurrentUser();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não autenticado',
            'timestamp' => date('c')
        ]);
        return;
    }
    
    // Remover senha dos dados retornados
    unset($user['senha']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Dados do usuário obtidos com sucesso',
        'data' => $user,
        'timestamp' => date('c')
    ]);
}
