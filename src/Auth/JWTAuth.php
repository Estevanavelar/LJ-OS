<?php

namespace LJOS\Auth;

use LJOS\Models\Usuario;
use Exception;

/**
 * Sistema de Autenticação JWT
 * 
 * @package LJOS\Auth
 * @author LJ-OS Team
 * @version 1.0.0
 */
class JWTAuth
{
    private $secret;
    private $algorithm = 'HS256';
    private $expiration = 3600; // 1 hora
    private $refreshExpiration = 604800; // 7 dias
    
    public function __construct()
    {
        $this->secret = $this->getSecret();
    }
    
    /**
     * Obtém chave secreta do sistema
     */
    private function getSecret(): string
    {
        $config = require_once __DIR__ . '/../../config/config.php';
        return $config['jwt']['secret'] ?? 'lj-os-secret-key-2024';
    }
    
    /**
     * Gera token JWT
     */
    public function generateToken(array $payload): string
    {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ]);
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->expiration;
        $payload['nbf'] = time();
        
        $base64Header = $this->base64UrlEncode($header);
        $base64Payload = $this->base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret, true);
        $base64Signature = $this->base64UrlEncode($signature);
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * Gera refresh token
     */
    public function generateRefreshToken(array $payload): string
    {
        $payload['type'] = 'refresh';
        $payload['exp'] = time() + $this->refreshExpiration;
        
        return $this->generateToken($payload);
    }
    
    /**
     * Valida token JWT
     */
    public function validateToken(string $token): array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Token inválido');
        }
        
        [$header, $payload, $signature] = $parts;
        
        // Verificar assinatura
        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, $this->secret, true);
        $expectedSignature = $this->base64UrlEncode($expectedSignature);
        
        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Assinatura inválida');
        }
        
        // Decodificar payload
        $payloadData = json_decode($this->base64UrlDecode($payload), true);
        
        if (!$payloadData) {
            throw new Exception('Payload inválido');
        }
        
        // Verificar expiração
        if (isset($payloadData['exp']) && time() > $payloadData['exp']) {
            throw new Exception('Token expirado');
        }
        
        // Verificar tempo de início
        if (isset($payloadData['nbf']) && time() < $payloadData['nbf']) {
            throw new Exception('Token ainda não válido');
        }
        
        return $payloadData;
    }
    
    /**
     * Renova token usando refresh token
     */
    public function refreshToken(string $refreshToken): string
    {
        try {
            $payload = $this->validateToken($refreshToken);
            
            if (!isset($payload['type']) || $payload['type'] !== 'refresh') {
                throw new Exception('Token não é um refresh token');
            }
            
            // Remover campos específicos do refresh
            unset($payload['type'], $payload['exp'], $payload['iat'], $payload['nbf']);
            
            return $this->generateToken($payload);
            
        } catch (Exception $e) {
            throw new Exception('Refresh token inválido: ' . $e->getMessage());
        }
    }
    
    /**
     * Decodifica token sem validação
     */
    public function decodeToken(string $token): array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new Exception('Token inválido');
        }
        
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        
        if (!$payload) {
            throw new Exception('Payload inválido');
        }
        
        return $payload;
    }
    
    /**
     * Codifica string para base64url
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decodifica string de base64url
     */
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
    
    /**
     * Autentica usuário e retorna token
     */
    public function authenticate(string $email, string $senha): array
    {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByEmail($email);
        
        if (!$usuario) {
            throw new Exception('Usuário não encontrado');
        }
        
        if ($usuario['status'] !== 'ATIVO') {
            throw new Exception('Usuário inativo');
        }
        
        if (!$usuarioModel->verificarSenha($senha, $usuario['senha'])) {
            throw new Exception('Senha incorreta');
        }
        
        // Atualizar último login
        $usuarioModel->updateUltimoLogin($usuario['id_usuario']);
        
        // Gerar tokens
        $payload = [
            'user_id' => $usuario['id_usuario'],
            'email' => $usuario['email'],
            'nivel_acesso' => $usuario['nivel_acesso'],
            'nome' => $usuario['nome']
        ];
        
        $accessToken = $this->generateToken($payload);
        $refreshToken = $this->generateRefreshToken($payload);
        
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->expiration,
            'user' => [
                'id' => $usuario['id_usuario'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'nivel_acesso' => $usuario['nivel_acesso'],
                'status' => $usuario['status']
            ]
        ];
    }
    
    /**
     * Verifica se usuário tem permissão
     */
    public function hasPermission(array $tokenPayload, string $permission): bool
    {
        if (!isset($tokenPayload['nivel_acesso'])) {
            return false;
        }
        
        $nivel = $tokenPayload['nivel_acesso'];
        
        // Mapeamento de permissões por nível
        $permissions = [
            'ADMIN' => ['*'], // Admin tem todas as permissões
            'GERENTE' => [
                'clientes.*', 'veiculos.*', 'agendamentos.*', 'ordens_servico.*',
                'estoque.*', 'financeiro.*', 'funcionarios.*', 'relatorios.*'
            ],
            'ATENDENTE' => [
                'clientes.read', 'clientes.create', 'clientes.update',
                'veiculos.read', 'veiculos.create', 'veiculos.update',
                'agendamentos.*', 'ordens_servico.*'
            ],
            'FUNCIONARIO' => [
                'ordens_servico.read', 'ordens_servico.update',
                'estoque.read', 'estoque.update'
            ]
        ];
        
        if (!isset($permissions[$nivel])) {
            return false;
        }
        
        $userPermissions = $permissions[$nivel];
        
        // Verificar permissão wildcard
        if (in_array('*', $userPermissions)) {
            return true;
        }
        
        // Verificar permissão específica
        foreach ($userPermissions as $userPermission) {
            if ($this->matchPermission($permission, $userPermission)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Verifica se permissão corresponde ao padrão
     */
    private function matchPermission(string $permission, string $pattern): bool
    {
        if ($pattern === '*') {
            return true;
        }
        
        if ($pattern === $permission) {
            return true;
        }
        
        // Verificar padrões como "clientes.*"
        if (strpos($pattern, '.*') !== false) {
            $base = str_replace('.*', '', $pattern);
            return strpos($permission, $base . '.') === 0;
        }
        
        return false;
    }
    
    /**
     * Extrai token do header Authorization
     */
    public function extractTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if (!$authHeader) {
            return null;
        }
        
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return null;
        }
        
        return substr($authHeader, 7);
    }
    
    /**
     * Obtém usuário atual do token
     */
    public function getCurrentUser(): ?array
    {
        try {
            $token = $this->extractTokenFromHeader();
            
            if (!$token) {
                return null;
            }
            
            $payload = $this->validateToken($token);
            
            if (!isset($payload['user_id'])) {
                return null;
            }
            
            $usuarioModel = new Usuario();
            return $usuarioModel->find($payload['user_id']);
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Verifica se usuário está autenticado
     */
    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }
    
    /**
     * Verifica se usuário tem nível de acesso específico
     */
    public function hasRole(string $role): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        return $user['nivel_acesso'] === $role;
    }
    
    /**
     * Verifica se usuário tem pelo menos um dos níveis especificados
     */
    public function hasAnyRole(array $roles): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        return in_array($user['nivel_acesso'], $roles);
    }
    
    /**
     * Invalida token (logout)
     */
    public function invalidateToken(string $token): bool
    {
        // Em uma implementação real, você pode adicionar o token a uma blacklist
        // Por enquanto, apenas retornamos true
        return true;
    }
}
