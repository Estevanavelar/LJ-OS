<?php
/**
 * Página de Logout
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/functions.php';

// Registrar log de logout se o usuário estiver logado
if (isset($_SESSION['usuario_id'])) {
    registrarLog('Logout realizado', 'usuarios', $_SESSION['usuario_id']);
}

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se desejar destruir a sessão completamente, apague também o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir a sessão
session_destroy();

// Redirecionar para a página de login
header('Location: login.php?sucesso=Logout realizado com sucesso!');
exit();
?> 