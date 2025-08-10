<?php
/**
 * Configurações de APIs Externas
 * LJ-OS Sistema para Lava Jato
 */

require_once __DIR__ . '/../src/Env.php';
\LJOS\Env::load(__DIR__ . '/../.env');

// Configurações da API de CEP (ViaCEP)
define('API_CEP_URL', \LJOS\Env::get('API_CEP_URL', 'https://viacep.com.br/ws/'));
define('API_CEP_TIMEOUT', (int) \LJOS\Env::get('API_CEP_TIMEOUT', 10));

// Configurações da API de CNPJ (API Pública)
define('API_CNPJ_URL', \LJOS\Env::get('API_CNPJ_URL', 'https://publica.cnpj.ws/cnpj/'));
define('API_CNPJ_TIMEOUT', (int) \LJOS\Env::get('API_CNPJ_TIMEOUT', 15));
define('API_CNPJ_USER_AGENT', \LJOS\Env::get('API_CNPJ_USER_AGENT', 'LJ-OS-Sistema/1.0'));

// Configurações para WhatsApp (futuro)
define('WHATSAPP_API_KEY', \LJOS\Env::get('WHATSAPP_API_KEY', ''));
define('WHATSAPP_API_URL', \LJOS\Env::get('WHATSAPP_API_URL', ''));
define('WHATSAPP_PHONE_ID', \LJOS\Env::get('WHATSAPP_PHONE_ID', ''));

// Configurações para SMS (futuro)
define('SMS_API_KEY', \LJOS\Env::get('SMS_API_KEY', ''));
define('SMS_API_URL', \LJOS\Env::get('SMS_API_URL', ''));
define('SMS_SENDER', \LJOS\Env::get('SMS_SENDER', 'LJ-OS'));

// Configurações de notificações
define('NOTIFICATIONS_ENABLED', filter_var(\LJOS\Env::get('NOTIFICATIONS_ENABLED', true), FILTER_VALIDATE_BOOLEAN));
define('NOTIFICATIONS_EMAIL', filter_var(\LJOS\Env::get('NOTIFICATIONS_EMAIL', true), FILTER_VALIDATE_BOOLEAN));
define('NOTIFICATIONS_SMS', filter_var(\LJOS\Env::get('NOTIFICATIONS_SMS', false), FILTER_VALIDATE_BOOLEAN));
define('NOTIFICATIONS_WHATSAPP', filter_var(\LJOS\Env::get('NOTIFICATIONS_WHATSAPP', false), FILTER_VALIDATE_BOOLEAN));

/**
 * Função para consultar CEP via API
 */
function consultarCEP($cep) {
    $cep = preg_replace('/\D/', '', $cep);
    
    if (strlen($cep) !== 8) {
        return ['erro' => 'CEP deve ter 8 dígitos'];
    }
    
    $url = API_CEP_URL . $cep . '/json/';
    
    $context = stream_context_create([
        'http' => [
            'timeout' => API_CEP_TIMEOUT,
            'user_agent' => API_CNPJ_USER_AGENT
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['erro' => 'Erro ao consultar CEP'];
    }
    $data = json_decode($response, true);
    
    if (!$data || isset($data['erro'])) {
        return ['erro' => 'CEP não encontrado'];
    }
    
    return ['sucesso' => true, 'dados' => $data];
}

/**
 * Função para consultar CNPJ via API
 */
function consultarCNPJ($cnpj) {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    
    if (strlen($cnpj) !== 14) {
        return ['erro' => 'CNPJ deve ter 14 dígitos'];
    }
    
    $url = API_CNPJ_URL . $cnpj;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => API_CNPJ_TIMEOUT,
            'user_agent' => API_CNPJ_USER_AGENT
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return ['erro' => 'Erro ao consultar CNPJ'];
    }
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['estabelecimento'])) {
        return ['erro' => 'CNPJ não encontrado'];
    }
    
    return ['sucesso' => true, 'dados' => $data];
}

/**
 * Função para enviar notificação WhatsApp (placeholder)
 */
function enviarNotificacaoWhatsApp($telefone, $mensagem) {
    if (!NOTIFICATIONS_WHATSAPP || empty(WHATSAPP_API_KEY)) {
        return ['erro' => 'API WhatsApp não configurada'];
    }
    
    // Implementação futura da API do WhatsApp
    return ['sucesso' => true, 'mensagem' => 'Notificação enviada'];
}

/**
 * Função para enviar notificação SMS (placeholder)
 */
function enviarNotificacaoSMS($telefone, $mensagem) {
    if (!NOTIFICATIONS_SMS || empty(SMS_API_KEY)) {
        return ['erro' => 'API SMS não configurada'];
    }
    
    // Implementação futura da API de SMS
    return ['sucesso' => true, 'mensagem' => 'SMS enviado'];
}

/**
 * Função para enviar notificação por email
 */
function enviarNotificacaoEmail($email, $assunto, $mensagem) {
    if (!NOTIFICATIONS_EMAIL) {
        return ['erro' => 'Notificações por email desabilitadas'];
    }
    
    $headers = [
        'From: ' . obterConfiguracao('email_sistema', 'noreply@lavajato.com'),
        'Reply-To: ' . obterConfiguracao('email_sistema', 'noreply@lavajato.com'),
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    $resultado = mail($email, $assunto, $mensagem, implode("\r\n", $headers));
    
    if ($resultado) {
        return ['sucesso' => true, 'mensagem' => 'Email enviado'];
    } else {
        return ['erro' => 'Erro ao enviar email'];
    }
}

/**
 * Função para validar se uma API está disponível
 */
function verificarDisponibilidadeAPI($url, $timeout = 5) {
    $context = stream_context_create([
        'http' => [
            'timeout' => $timeout,
            'method' => 'HEAD'
        ]
    ]);
    
    $resultado = @file_get_contents($url, false, $context);
    return $resultado !== false;
}

/**
 * Função para obter status das APIs
 */
function obterStatusAPIs() {
    return [
        'cep' => verificarDisponibilidadeAPI(API_CEP_URL . '01001000/json/'),
        'cnpj' => verificarDisponibilidadeAPI(API_CNPJ_URL . '00000000000191'),
        'whatsapp' => !empty(WHATSAPP_API_KEY),
        'sms' => !empty(SMS_API_KEY),
        'email' => NOTIFICATIONS_EMAIL
    ];
}
?> 