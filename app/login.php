<?php
/**
 * LJ-OS - Página de Login
 */

// Carregar autoloader
require_once __DIR__ . '/../autoload.php';

// Carregar sistema de localização
$localization = LJOS\Utils\Localization::getInstance();

// Aplicar configurações de tema e idioma
$localization->applySettings();

// Verificar se já está logado
session_start();
$token = $_SESSION['token'] ?? $_COOKIE['token'] ?? null;

if ($token) {
    // Se já tem token, redirecionar para dashboard
    header('Location: /app/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html <?= $localization->getHtmlAttributes() ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $localization->get('app_name') ?> - <?= $localization->get('login') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Temas CSS -->
    <link href="../app/assets/css/themes.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .password-toggle:hover {
            color: #667eea;
        }
        
        .theme-controls {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
        }
        
        .theme-controls .btn {
            margin: 0.25rem;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <!-- Controles de Tema -->
    <div class="theme-controls">
        <button class="btn btn-outline-primary theme-toggle" title="<?= $localization->get('theme') ?>">
            <i class="fas fa-moon"></i>
        </button>
        <button class="btn btn-outline-secondary contrast-toggle" title="<?= $localization->get('contrast') ?>">
            <i class="fas fa-adjust"></i>
        </button>
        <button class="btn btn-outline-info language-toggle" title="<?= $localization->get('language') ?>">
            <i class="fas fa-flag"></i>
        </button>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <!-- Header -->
                    <div class="login-header">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-tools"></i> <?= $localization->get('app_name') ?>
                        </h1>
                        <p class="mb-0 opacity-75"><?= $localization->get('app_description') ?></p>
                    </div>
                    
                    <!-- Body -->
                    <div class="login-body">
                        <form id="loginForm">
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> <?= $localization->get('email') ?>
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="seu@email.com" required>
                            </div>
                            
                            <!-- Senha -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> <?= $localization->get('password') ?>
                                </label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="<?= $localization->get('password') ?>" required>
                                    <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                                </div>
                            </div>
                            
                            <!-- Lembrar de mim -->
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    <?= $localization->get('remember_me') ?>
                                </label>
                            </div>
                            
                            <!-- Botão de Login -->
                            <button type="submit" class="btn btn-login btn-primary w-100">
                                <i class="fas fa-sign-in-alt"></i> <?= $localization->get('login') ?>
                            </button>
                        </form>
                        
                        <!-- Links -->
                        <div class="text-center mt-3">
                            <a href="#" class="text-muted small">
                                <?= $localization->get('forgot_password') ?>
                            </a>
                        </div>
                        
                        <!-- Alertas -->
                        <div id="alerts"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Manager -->
    <script src="../app/assets/js/theme-manager.js"></script>
    
    <script>
        // Toggle de senha
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash password-toggle';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye password-toggle';
            }
        }
        
        // Login
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            try {
                const response = await fetch('../app/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email: email,
                        password: password,
                        remember: remember
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Salvar token
                    if (data.data.token) {
                        localStorage.setItem('auth_token', data.data.token);
                        if (remember) {
                            sessionStorage.setItem('auth_token', data.data.token);
                        }
                    }
                    
                    // Redirecionar para dashboard
                    window.location.href = '/app/dashboard.php';
                } else {
                    showAlert('danger', data.message || '<?= $localization->get('login_error') ?>');
                }
            } catch (error) {
                console.error('Erro no login:', error);
                showAlert('danger', '<?= $localization->get('error') ?>: ' + error.message);
            }
        });
        
        // Função para mostrar alertas
        function showAlert(type, message) {
            const alertsContainer = document.getElementById('alerts');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            alertsContainer.innerHTML = '';
            alertsContainer.appendChild(alert);
            
            // Remover automaticamente após 5 segundos
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 5000);
        }
        
        // Verificar se já está logado
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
            if (token) {
                window.location.href = 'dashboard.php';
            }
        });
    </script>
</body>
</html>
