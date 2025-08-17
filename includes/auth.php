
<?php
/**
 * Sistema de Autenticação e Autorização Avançado
 */

class AuthManager {
    private static $instance = null;
    private $db;
    private $security;
    
    private function __construct() {
        $this->db = getDBManager();
        $this->security = SecurityManager::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Autentica usuário
     */
    public function login($email, $password, $remember = false) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Rate limiting para login
        if (!$this->security->checkRateLimit('login_attempts', $ip, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
            $this->security->logSecurity('LOGIN_RATE_LIMITED', ['email' => $email, 'ip' => $ip], 'WARNING');
            return ['success' => false, 'message' => 'Muitas tentativas de login. Tente novamente em 15 minutos.'];
        }
        
        try {
            $stmt = $this->db->query(
                "SELECT u.*, p.nome as perfil_nome, p.nivel FROM usuarios u 
                 LEFT JOIN perfis_acesso p ON u.id_perfil = p.id_perfil 
                 WHERE u.email = ? AND u.ativo = 1",
                [$email]
            );
            
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['senha'])) {
                $this->security->logSecurity('LOGIN_FAILED', ['email' => $email, 'ip' => $ip], 'WARNING');
                return ['success' => false, 'message' => 'Email ou senha incorretos'];
            }
            
            // Verificar se a conta está bloqueada
            if ($user['status'] === 'bloqueado') {
                $this->security->logSecurity('LOGIN_BLOCKED_ACCOUNT', ['user_id' => $user['id_usuario']], 'WARNING');
                return ['success' => false, 'message' => 'Conta bloqueada. Contate o administrador.'];
            }
            
            // Atualizar último login
            $this->db->query(
                "UPDATE usuarios SET ultimo_login = NOW(), tentativas_login = 0 WHERE id_usuario = ?",
                [$user['id_usuario']]
            );
            
            // Criar sessão
            $this->createSession($user, $remember);
            
            $this->security->logSecurity('LOGIN_SUCCESS', ['user_id' => $user['id_usuario']], 'INFO');
            
            return ['success' => true, 'user' => $user];
            
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno. Tente novamente.'];
        }
    }
    
    /**
     * Cria sessão do usuário
     */
    private function createSession($user, $remember = false) {
        // Regenerar ID da sessão para segurança
        session_regenerate_id(true);
        
        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nome'] = $user['nome'];
        $_SESSION['usuario_email'] = $user['email'];
        $_SESSION['usuario_perfil'] = $user['perfil_nome'] ?? 'usuario';
        $_SESSION['usuario_nivel'] = $user['nivel'] ?? 1;
        $_SESSION['usuario_logado'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Cookie de "lembrar-me"
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 dias
            
            setcookie('remember_token', $token, $expires, '/', '', true, true);
            
            // Salvar token no banco
            $this->db->query(
                "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)",
                [$user['id_usuario'], hash('sha256', $token), date('Y-m-d H:i:s', $expires)]
            );
        }
    }
    
    /**
     * Verifica se usuário está logado
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['usuario_logado']) || !$_SESSION['usuario_logado']) {
            return $this->checkRememberToken();
        }
        
        // Verificar timeout da sessão
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    /**
     * Verifica token de "lembrar-me"
     */
    private function checkRememberToken() {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        $token = $_COOKIE['remember_token'];
        $hashed_token = hash('sha256', $token);
        
        $stmt = $this->db->query(
            "SELECT rt.*, u.* FROM remember_tokens rt 
             JOIN usuarios u ON rt.user_id = u.id_usuario 
             WHERE rt.token = ? AND rt.expires_at > NOW() AND u.ativo = 1",
            [$hashed_token]
        );
        
        $result = $stmt->fetch();
        
        if ($result) {
            // Renovar sessão
            $this->createSession($result, true);
            return true;
        }
        
        // Token inválido, remover cookie
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }
    
    /**
     * Faz logout
     */
    public function logout() {
        $user_id = $_SESSION['usuario_id'] ?? null;
        
        // Remover token de "lembrar-me"
        if (isset($_COOKIE['remember_token'])) {
            $token = hash('sha256', $_COOKIE['remember_token']);
            $this->db->query("DELETE FROM remember_tokens WHERE token = ?", [$token]);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Destruir sessão
        session_destroy();
        
        if ($user_id) {
            $this->security->logSecurity('LOGOUT', ['user_id' => $user_id], 'INFO');
        }
    }
    
    /**
     * Verifica permissão
     */
    public function hasPermission($modulo, $acao) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $user_id = $_SESSION['usuario_id'];
        
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM permissoes_usuario pu
             JOIN permissoes p ON pu.id_permissao = p.id_permissao
             WHERE pu.id_usuario = ? AND p.modulo = ? AND p.acao = ?",
            [$user_id, $modulo, $acao]
        );
        
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Verifica se é admin
     */
    public function isAdmin() {
        return isset($_SESSION['usuario_nivel']) && $_SESSION['usuario_nivel'] >= 5;
    }
    
    /**
     * Obtém dados do usuário atual
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->query(
            "SELECT u.*, p.nome as perfil_nome FROM usuarios u 
             LEFT JOIN perfis_acesso p ON u.id_perfil = p.id_perfil 
             WHERE u.id_usuario = ?",
            [$_SESSION['usuario_id']]
        );
        
        return $stmt->fetch();
    }
}

// Funções helper globais
function auth() {
    return AuthManager::getInstance();
}

function isLoggedIn() {
    return auth()->isLoggedIn();
}

function hasPermission($modulo, $acao) {
    return auth()->hasPermission($modulo, $acao);
}

function isAdmin() {
    return auth()->isAdmin();
}

function requireLogin($redirect = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requirePermission($modulo, $acao, $redirect = 'dashboard.php') {
    if (!hasPermission($modulo, $acao)) {
        security_log('PERMISSION_DENIED', ['modulo' => $modulo, 'acao' => $acao], 'WARNING');
        header("Location: $redirect?error=permission_denied");
        exit;
    }
}

function requireAdmin($redirect = 'dashboard.php') {
    if (!isAdmin()) {
        security_log('ADMIN_ACCESS_DENIED', [], 'WARNING');
        header("Location: $redirect?error=admin_required");
        exit;
    }
}

function currentUser() {
    return auth()->getCurrentUser();
}
