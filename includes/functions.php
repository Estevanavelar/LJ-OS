<?php
/**
 * Funções auxiliares do sistema
 * LJ-OS Sistema para Lava Jato
 */

// Função para verificar se a sessão está ativa (não inicia mais sessão)
function iniciarSessaoSegura() {
    // A sessão já é iniciada em config/config.php
    // Esta função agora apenas verifica se está ativa
    return (session_status() === PHP_SESSION_ACTIVE);
}

// Incluir configuração do banco de dados
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/apis.php';

// Headers de segurança básicos
function aplicarHeadersSeguros() {
    if (headers_sent()) { return; }
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: no-referrer-when-downgrade');
    header('X-XSS-Protection: 0');

    if ((getenv('HSTS_ENABLED') === 'true') && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }

    if (getenv('CSP_ENABLED') === 'true') {
        $csp = "default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; frame-ancestors 'self';";
        header("Content-Security-Policy: $csp");
    }
}

aplicarHeadersSeguros();

// Helpers de autenticação
function estaLogado(): bool {
    // A sessão já foi iniciada em config/config.php
    return (session_status() === PHP_SESSION_ACTIVE) && 
           isset($_SESSION['usuario_id']) && 
           !empty($_SESSION['usuario_logado']);
}

/**
 * Função para verificar se o usuário está logado (redireciona)
 */
function verificarLogin() {
    if (!estaLogado()) {
        header('Location: login.php');
        exit();
    }
}

// CSRF - funções de segurança
function csrf_token() {
    // A sessão já foi iniciada em config/config.php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verificar() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enviado = $_POST['_csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $enviado)) {
            http_response_code(403);
            exit('Falha de verificação CSRF');
        }
    }
}

// CSRF para APIs (aceita header X-CSRF-Token ou campo _csrf no JSON)
function csrf_verificar_api() {
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($metodo === 'GET' || $metodo === 'HEAD' || $metodo === 'OPTIONS') {
        return; // safe methods
    }

    $tokenHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $token = $tokenHeader;

    if (!$token) {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data) && isset($data['_csrf'])) {
                $token = $data['_csrf'];
            }
        } else {
            $token = $_POST['_csrf'] ?? '';
        }
    }

    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['erro' => 'Falha de verificação CSRF']);
        exit();
    }
}

/**
 * Função para verificar nível de acesso do usuário
 */
function verificarNivelAcesso($niveis_permitidos = []) {
    verificarLogin();
    
    if (!empty($niveis_permitidos) && !in_array($_SESSION['nivel_acesso'], $niveis_permitidos)) {
        header('Location: dashboard.php?erro=acesso_negado');
        exit();
    }
}

/**
 * Função para verificar permissões do usuário
 */
function verificarPermissao($modulo, $funcionalidade = 'visualizar') {
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }

    try {
        $db = getDB();
        // Verificar permissão direta do usuário
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM permissoes 
            WHERE id_usuario = ? AND modulo = ? AND funcionalidade = ? AND ativo = 1
        ");
        $stmt->execute([$_SESSION['usuario_id'], $modulo, $funcionalidade]);
        $permissaoDireta = ($stmt->fetch()['total'] ?? 0) > 0;
        
        if ($permissaoDireta) {
            return true;
        }
        
        // Verificar permissão via perfil
        $stmt = $db->prepare("
            SELECT COUNT(*) as total 
            FROM permissoes_perfil pp
            JOIN usuarios u ON pp.id_perfil = u.id_perfil
            WHERE u.id_usuario = ? AND pp.modulo = ? AND pp.funcionalidade = ? AND pp.ativo = 1
        ");
        $stmt->execute([$_SESSION['usuario_id'], $modulo, $funcionalidade]);
        $permissaoPerfil = ($stmt->fetch()['total'] ?? 0) > 0;
        
        return $permissaoPerfil;
    } catch (Exception $e) {
        error_log("Erro ao verificar permissão: " . $e->getMessage());
        return false;
    }
}

/**
 * Função para obter permissões do usuário
 */
function obterPermissoesUsuario($id_usuario) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT modulo, funcionalidade 
            FROM permissoes 
            WHERE id_usuario = ? AND ativo = 1
        ");
        $stmt->execute([$id_usuario]);
        $permissoesDiretas = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $stmt = $db->prepare("
            SELECT pp.modulo, pp.funcionalidade 
            FROM permissoes_perfil pp
            JOIN usuarios u ON pp.id_perfil = u.id_perfil
            WHERE u.id_usuario = ? AND pp.ativo = 1
        ");
        $stmt->execute([$id_usuario]);
        $permissoesPerfil = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        return array_merge($permissoesDiretas, $permissoesPerfil);
    } catch (Exception $e) {
        error_log("Erro ao obter permissões: " . $e->getMessage());
        return [];
    }
}

/**
 * Função para sanitizar dados de entrada
 */
function sanitizar($dados) {
    if (is_array($dados)) {
        return array_map('sanitizar', $dados);
    }
    return htmlspecialchars(trim($dados), ENT_QUOTES, 'UTF-8');
}

/**
 * Função para validar CPF
 */
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
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
    // Remove caracteres não numéricos
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    
    // Verifica se tem 14 dígitos
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        return false;
    }
    
    // Calcula o primeiro dígito verificador
    $soma = 0;
    $peso = 2;
    for ($i = 11; $i >= 0; $i--) {
        $soma += $cnpj[$i] * $peso;
        $peso = ($peso == 9) ? 2 : $peso + 1;
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Calcula o segundo dígito verificador
    $soma = 0;
    $peso = 2;
    for ($i = 12; $i >= 0; $i--) {
        $soma += $cnpj[$i] * $peso;
        $peso = ($peso == 9) ? 2 : $peso + 1;
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica se os dígitos calculados conferem
    return ($cnpj[12] == $dv1 && $cnpj[13] == $dv2);
}

/**
 * Função para formatar CPF/CNPJ
 */
function formatarCpfCnpj($documento) {
    $documento = preg_replace('/[^0-9]/', '', $documento);
    
    if (strlen($documento) == 11) {
        // CPF
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documento);
    } elseif (strlen($documento) == 14) {
        // CNPJ
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documento);
    }
    
    return $documento;
}

/**
 * Função para formatar telefone
 */
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    if (strlen($telefone) == 11) {
        // Celular com 9 dígitos
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) == 10) {
        // Telefone fixo
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    
    return $telefone;
}

/**
 * Função para formatar moeda
 */
function formatarMoeda($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Função para converter moeda para decimal
 */
function converterMoedaParaDecimal($valor) {
    // Remove símbolos de moeda e espaços
    $valor = str_replace(['R$', ' '], '', $valor);
    // Substitui vírgula por ponto
    $valor = str_replace(',', '.', $valor);
    // Remove pontos que não sejam o separador decimal
    $valor = preg_replace('/\.(?=.*\.)/', '', $valor);
    
    return floatval($valor);
}

/**
 * Função para gerar código único
 */
function gerarCodigo($prefixo = '', $tamanho = 8) {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codigo = $prefixo;
    
    for ($i = 0; $i < $tamanho; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    return $codigo;
}

/**
 * Função para upload de arquivos
 */
function uploadArquivo($arquivo, $diretorio, $tipos_permitidos = ['jpg', 'jpeg', 'png', 'pdf']) {
    // Verifica se o arquivo foi enviado
    if (!isset($arquivo) || $arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['sucesso' => false, 'erro' => 'Erro no upload do arquivo'];
    }
    
    // Verifica o tipo do arquivo
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $tipos_permitidos)) {
        return ['sucesso' => false, 'erro' => 'Tipo de arquivo não permitido'];
    }
    
    // Gera nome único para o arquivo
    $nome_arquivo = uniqid() . '.' . $extensao;
    $caminho_completo = $diretorio . '/' . $nome_arquivo;
    
    // Cria o diretório se não existir
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0755, true);
    }
    
    // Move o arquivo para o diretório de destino
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
        return ['sucesso' => true, 'arquivo' => $nome_arquivo, 'caminho' => $caminho_completo];
    } else {
        return ['sucesso' => false, 'erro' => 'Erro ao salvar o arquivo'];
    }
}

/**
 * Função para registrar log do sistema
 */
function registrarLog($acao, $tabela_afetada = null, $id_registro = null, $dados_anteriores = null, $dados_novos = null) {
    try {
        $db = getDB();
        
        $sql = "INSERT INTO logs_sistema (id_usuario, acao, tabela_afetada, id_registro, dados_anteriores, dados_novos, ip_usuario, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $_SESSION['usuario_id'] ?? null,
            $acao,
            $tabela_afetada,
            $id_registro,
            $dados_anteriores ? json_encode($dados_anteriores) : null,
            $dados_novos ? json_encode($dados_novos) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

/**
 * Função para enviar notificação via WhatsApp (placeholder)
 */
function enviarWhatsApp($telefone, $mensagem) {
    // Implementar integração com API do WhatsApp
    // Esta é uma função placeholder que deve ser implementada conforme a API escolhida
    
    try {
        // Exemplo de implementação com API fictícia
        $dados = [
            'telefone' => $telefone,
            'mensagem' => $mensagem
        ];
        
        // Aqui seria feita a chamada para a API do WhatsApp
        // return chamarAPIWhatsApp($dados);
        
        // Por enquanto, apenas registra no log
        registrarLog("Tentativa de envio WhatsApp", null, null, null, $dados);
        
        return ['sucesso' => true, 'mensagem' => 'Mensagem enviada com sucesso'];
        
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    }
}

/**
 * Função para enviar SMS (placeholder)
 */
function enviarSMS($telefone, $mensagem) {
    // Implementar integração com API de SMS
    // Esta é uma função placeholder que deve ser implementada conforme a API escolhida
    
    try {
        $dados = [
            'telefone' => $telefone,
            'mensagem' => $mensagem
        ];
        
        // Aqui seria feita a chamada para a API de SMS
        // return chamarAPISMS($dados);
        
        // Por enquanto, apenas registra no log
        registrarLog("Tentativa de envio SMS", null, null, null, $dados);
        
        return ['sucesso' => true, 'mensagem' => 'SMS enviado com sucesso'];
        
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    }
}

/**
 * Função para calcular pontos de fidelidade
 */
function calcularPontosFidelidade($valor_compra) {
    // 1 ponto para cada R$ 10,00 gastos
    return floor($valor_compra / 10);
}

/**
 * Função para aplicar desconto de cupom
 */
function aplicarCupomDesconto($codigo_cupom, $valor_total) {
    try {
        $db = getDB();
        
        // Buscar cupom válido
        $sql = "SELECT * FROM cupons_desconto 
                WHERE codigo = ? 
                AND status = 'ativo' 
                AND data_inicio <= CURDATE() 
                AND data_validade >= CURDATE() 
                AND (usos_maximos = 0 OR usos_atuais < usos_maximos)
                AND valor_minimo_compra <= ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$codigo_cupom, $valor_total]);
        $cupom = $stmt->fetch();
        
        if (!$cupom) {
            return ['sucesso' => false, 'erro' => 'Cupom inválido ou expirado'];
        }
        
        // Calcular desconto
        if ($cupom['tipo_desconto'] == 'porcentagem') {
            $desconto = ($valor_total * $cupom['valor_desconto']) / 100;
        } else {
            $desconto = $cupom['valor_desconto'];
        }
        
        // Garantir que o desconto não seja maior que o valor total
        $desconto = min($desconto, $valor_total);
        
        return [
            'sucesso' => true,
            'cupom' => $cupom,
            'desconto' => $desconto,
            'valor_final' => $valor_total - $desconto
        ];
        
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => 'Erro ao aplicar cupom: ' . $e->getMessage()];
    }
}

/**
 * Função para obter configuração do sistema
 */
function obterConfiguracao($chave, $valor_padrao = null) {
    try {
        $db = getDB();
        
        $sql = "SELECT valor FROM configuracoes WHERE chave = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$chave]);
        $resultado = $stmt->fetch();
        
        return $resultado ? $resultado['valor'] : $valor_padrao;
        
    } catch (Exception $e) {
        return $valor_padrao;
    }
}

/**
 * Função para definir configuração do sistema
 */
function definirConfiguracao($chave, $valor, $descricao = null, $tipo = 'texto', $categoria = null) {
    try {
        $db = getDB();
        
        // Para SQLite - usar INSERT OR REPLACE
        $sql = "INSERT OR REPLACE INTO configuracoes (chave, valor, updated_at) 
                VALUES (?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([$chave, $valor]);
        
    } catch (Exception $e) {
        error_log("Erro ao definir configuração: " . $e->getMessage());
        return false;
    }
}

/**
 * Funções utilitárias de data/hora
 */
function formatarData($data)
{
    if (empty($data)) {
        return '';
    }
    $ts = is_numeric($data) ? (int)$data : strtotime((string)$data);
    if ($ts === false) {
        return '';
    }
    return date('d/m/Y', $ts);
}

function formatarDataHora($data)
{
    if (empty($data)) {
        return '';
    }
    $ts = is_numeric($data) ? (int)$data : strtotime((string)$data);
    if ($ts === false) {
        return '';
    }
    return date('d/m/Y H:i', $ts);
}

?> 