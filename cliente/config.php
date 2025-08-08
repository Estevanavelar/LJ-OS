<?php
/**
 * Configuração da Área do Cliente
 * LJ-OS Sistema para Lava Jato
 * 
 * Este arquivo deve ser configurado quando a área do cliente for instalada em subdomínio
 */

// ============================================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================================================

// Configurações do banco de dados (mesmo banco do sistema principal)
define('DB_HOST', 'localhost');
define('DB_NAME', 'lava_jato_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================================
// CONFIGURAÇÕES DO SISTEMA
// ============================================================================

// URL do sistema principal (para redirecionamentos e links)
define('SISTEMA_URL', 'http://localhost/LJ/');

// URL da área do cliente (para links internos)
define('CLIENTE_URL', 'http://localhost/LJ/cliente/');

// Nome da empresa
define('EMPRESA_NOME', 'Lava Jato');

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// ============================================================================
// CONFIGURAÇÕES DE SEGURANÇA
// ============================================================================

// Tempo de sessão em segundos (30 minutos)
define('SESSION_TIMEOUT', 1800);

// Chave secreta para sessões (altere em produção)
define('SESSION_SECRET', 'lj_os_cliente_secret_key_2024');

// ============================================================================
// CONFIGURAÇÕES DE LOG
// ============================================================================

// Habilitar logs de acesso
define('LOG_ACESSOS', true);

// Diretório de logs
define('LOG_DIR', '../logs/');

// ============================================================================
// CLASSE DE CONEXÃO COM BANCO DE DADOS
// ============================================================================

/**
 * Classe para gerenciar conexões com o banco de dados
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        try {
            // String de conexão PDO
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            // Opções de configuração do PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            // Criar conexão
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log do erro e exibição de mensagem amigável
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            die("Erro de conexão com o banco de dados. Tente novamente mais tarde.");
        }
    }
    
    /**
     * Método para obter instância única da classe (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Método para obter a conexão PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne deserialização da instância
     */
    public function __wakeup() {}
}

/**
 * Função auxiliar para obter conexão com o banco
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

// ============================================================================
// FUNÇÕES AUXILIARES
// ============================================================================

/**
 * Função para registrar logs de acesso
 */
function registrarLogAcesso($cliente_id, $acao = 'acesso') {
    if (!LOG_ACESSOS) return;
    
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO logs_acesso_cliente (id_cliente, ip_acesso, user_agent) VALUES (?, ?, ?)");
        $stmt->execute([$cliente_id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
    } catch (Exception $e) {
        error_log("Erro ao registrar log de acesso: " . $e->getMessage());
    }
}

/**
 * Função para verificar se a sessão do cliente é válida
 */
function verificarSessaoCliente() {
    if (!isset($_SESSION['cliente_id'])) {
        return false;
    }
    
    // Verificar timeout da sessão
    if (isset($_SESSION['cliente_acesso']) && (time() - $_SESSION['cliente_acesso']) > SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    
    // Atualizar tempo de acesso
    $_SESSION['cliente_acesso'] = time();
    
    return true;
}

/**
 * Função para formatar CPF/CNPJ
 */
function formatarDocumento($documento) {
    $documento = preg_replace('/[^0-9]/', '', $documento);
    
    if (strlen($documento) == 11) {
        return substr($documento, 0, 3) . '.' . substr($documento, 3, 3) . '.' . substr($documento, 6, 3) . '-' . substr($documento, 9, 2);
    } elseif (strlen($documento) == 14) {
        return substr($documento, 0, 2) . '.' . substr($documento, 2, 3) . '.' . substr($documento, 5, 3) . '/' . substr($documento, 8, 4) . '-' . substr($documento, 12, 2);
    }
    
    return $documento;
}

/**
 * Função para validar CPF
 */
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1+$/', $cpf)) {
        return false;
    }
    
    // Calcula os dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}

/**
 * Função para validar CNPJ
 */
function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/^(\d)\1+$/', $cnpj)) {
        return false;
    }
    
    // Calcula os dígitos verificadores
    for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    
    $resto = $soma % 11;
    if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
        return false;
    }
    
    for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $j;
        $j = ($j == 2) ? 9 : $j - 1;
    }
    
    $resto = $soma % 11;
    if ($cnpj[13] != ($resto < 2 ? 0 : 11 - $resto)) {
        return false;
    }
    
    return true;
}

/**
 * Função para formatar data
 */
function formatarData($data) {
    if (empty($data)) return '';
    return date('d/m/Y', strtotime($data));
}

/**
 * Função para formatar data e hora
 */
function formatarDataHora($data) {
    if (empty($data)) return '';
    return date('d/m/Y H:i', strtotime($data));
}

// ============================================================================
// CONFIGURAÇÕES DE PRODUÇÃO
// ============================================================================

// IMPORTANTE: Para instalação em produção, altere as configurações abaixo:

/*
// Exemplo para produção:
define('DB_HOST', 'localhost');
define('DB_NAME', 'lava_jato_db');
define('DB_USER', 'usuario_producao');
define('DB_PASS', 'senha_forte_producao');

define('SISTEMA_URL', 'https://seudominio.com/LJ/');
define('CLIENTE_URL', 'https://cliente.seudominio.com/');

define('SESSION_SECRET', 'chave_secreta_muito_forte_para_producao');
*/

?>
