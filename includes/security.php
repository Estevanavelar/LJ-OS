
<?php
/**
 * Sistema de Segurança Avançado
 * Proteção contra ataques e controle de acesso
 */

class SecurityManager {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $this->db = getDBManager();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Sanitiza entrada de dados
     */
    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Valida CSRF Token
     */
    public function validateCSRF($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Gera CSRF Token
     */
    public function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Rate limiting
     */
    public function checkRateLimit($action, $identifier, $limit = 10, $period = 3600) {
        $key = md5($action . '_' . $identifier);
        $current_time = time();
        
        // Limpar registros antigos
        $this->db->query(
            "DELETE FROM rate_limits WHERE created_at < ?",
            [$current_time - $period]
        );
        
        // Contar tentativas atuais
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM rate_limits WHERE action_key = ? AND created_at > ?",
            [$key, $current_time - $period]
        );
        
        $attempts = $stmt->fetch()['count'] ?? 0;
        
        if ($attempts >= $limit) {
            return false;
        }
        
        // Registrar tentativa
        $this->db->query(
            "INSERT INTO rate_limits (action_key, identifier, created_at) VALUES (?, ?, ?)",
            [$key, $identifier, $current_time]
        );
        
        return true;
    }
    
    /**
     * Criptografia de dados sensíveis
     */
    public function encrypt($data, $key = null) {
        if ($key === null) {
            $key = getenv('ENCRYPTION_KEY') ?: 'default_key_change_in_production';
        }
        
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', hash('sha256', $key), 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Descriptografia de dados
     */
    public function decrypt($data, $key = null) {
        if ($key === null) {
            $key = getenv('ENCRYPTION_KEY') ?: 'default_key_change_in_production';
        }
        
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', hash('sha256', $key), 0, $iv);
    }
    
    /**
     * Log de segurança
     */
    public function logSecurity($event, $details = [], $severity = 'INFO') {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'severity' => $severity,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['usuario_id'] ?? null,
            'details' => json_encode($details)
        ];
        
        try {
            $this->db->query(
                "INSERT INTO security_logs (event, severity, ip_address, user_agent, user_id, details, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$log_entry['event'], $log_entry['severity'], $log_entry['ip'], $log_entry['user_agent'], $log_entry['user_id'], $log_entry['details'], $log_entry['timestamp']]
            );
        } catch (Exception $e) {
            error_log("Erro ao registrar log de segurança: " . $e->getMessage());
        }
        
        // Log em arquivo também
        $log_message = "[{$log_entry['timestamp']}] {$log_entry['severity']}: {$log_entry['event']} - IP: {$log_entry['ip']} - Details: {$log_entry['details']}\n";
        file_put_contents(LOG_PATH . 'security.log', $log_message, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Verifica se IP está na blacklist
     */
    public function isBlacklisted($ip = null) {
        if ($ip === null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM ip_blacklist WHERE ip_address = ? AND (expires_at IS NULL OR expires_at > NOW())",
            [$ip]
        );
        
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Adiciona IP à blacklist
     */
    public function blacklistIP($ip, $reason = 'Security violation', $duration = 3600) {
        $expires_at = date('Y-m-d H:i:s', time() + $duration);
        
        $this->db->query(
            "INSERT OR REPLACE INTO ip_blacklist (ip_address, reason, expires_at, created_at) VALUES (?, ?, ?, NOW())",
            [$ip, $reason, $expires_at]
        );
        
        $this->logSecurity('IP_BLACKLISTED', ['ip' => $ip, 'reason' => $reason], 'WARNING');
    }
}

// Middleware de segurança
function checkSecurity() {
    $security = SecurityManager::getInstance();
    
    // Verificar blacklist
    if ($security->isBlacklisted()) {
        http_response_code(403);
        die('Acesso negado');
    }
    
    // Rate limiting para requisições gerais
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!$security->checkRateLimit('general_requests', $ip, 100, 60)) {
        http_response_code(429);
        die('Muitas requisições. Tente novamente em alguns minutos.');
    }
}

// Funções helper
function sanitize($data) {
    return SecurityManager::getInstance()->sanitizeInput($data);
}

function csrf_token() {
    return SecurityManager::getInstance()->generateCSRF();
}

function csrf_field() {
    $token = csrf_token();
    return "<input type='hidden' name='csrf_token' value='{$token}'>";
}

function validate_csrf($token) {
    return SecurityManager::getInstance()->validateCSRF($token);
}

function security_log($event, $details = [], $severity = 'INFO') {
    SecurityManager::getInstance()->logSecurity($event, $details, $severity);
}

// Inicializar verificação de segurança
checkSecurity();
