<?php
/**
 * Página de Login
 * LJ-OS Sistema para Lava Jato
 */

require_once 'includes/functions.php';

// Se já estiver logado, redirecionar para o dashboard
if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
    header('Location: dashboard.php');
    exit();
}

$erro = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $email = sanitizar($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Por favor, preencha todos os campos.';
    } else {
        try {
            $db = getDB();
            
            // Buscar usuário
            $sql = "SELECT id_usuario, nome, email, senha, nivel_acesso, status 
                    FROM usuarios 
                    WHERE email = ? AND status = 'ativo'";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
                $_SESSION['usuario_logado'] = true;
                
                // Atualizar último login
                $sql_update = "UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?";
                $stmt_update = $db->prepare($sql_update);
                $stmt_update->execute([$usuario['id_usuario']]);
                
                // Registrar log
                registrarLog('Login realizado', 'usuarios', $usuario['id_usuario']);
                
                // Redirecionar para o dashboard
                header('Location: dashboard.php?sucesso=Login realizado com sucesso!');
                exit();
                
            } else {
                $erro = 'Email ou senha incorretos.';
            }
            
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $erro = 'Erro interno do sistema. Tente novamente.';
        }
    }
}

$nome_empresa = obterConfiguracao('nome_empresa', 'Lava Jato VeltaCar');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $nome_empresa; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- CSS específico para login -->
    <style>
        body {
            background: linear-gradient(135deg, #bdbf90 0%, #e7e9c4 50%, #feae4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(53, 53, 43, 0.2);
            padding: 3rem;
            max-width: 400px;
            width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #bdbf90, #ec6c2b, #feae4b);
        }
        
        .login-logo {
            margin-bottom: 2rem;
        }
        
        .login-logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #bdbf90, #ec6c2b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 32px rgba(189, 191, 144, 0.3);
        }
        
        .login-title {
            color: #35352b;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        
        .login-form {
            text-align: left;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #35352b;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e7e9c4;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #bdbf90;
            box-shadow: 0 0 0 3px rgba(189, 191, 144, 0.1);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .form-control {
            padding-right: 3rem;
        }
        
        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .input-icon:hover {
            color: #ec6c2b;
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #bdbf90, #ec6c2b);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(189, 191, 144, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .login-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e7e9c4;
            color: #666;
            font-size: 0.8rem;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #666;
        }
        
        .demo-credentials strong {
            color: #35352b;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .login-logo-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .login-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div class="login-logo-icon">
                <i class="fas fa-car-wash"></i>
            </div>
            <h1 class="login-title"><?php echo $nome_empresa; ?></h1>
            <p class="login-subtitle">Sistema de Gestão</p>
        </div>
        
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control" 
                       required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="Digite seu email">
            </div>
            
            <div class="form-group">
                <label for="senha" class="form-label">
                    <i class="fas fa-lock"></i> Senha
                </label>
                <div class="input-group">
                    <input type="password" 
                           id="senha" 
                           name="senha" 
                           class="form-control" 
                           required 
                           placeholder="Digite sua senha">
                    <i class="fas fa-eye input-icon" id="togglePassword"></i>
                </div>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Entrar no Sistema
            </button>
        </form>
        
        <div class="demo-credentials">
            <strong>Credenciais de Demonstração:</strong><br>
            Email: admin@lavajato.com<br>
            Senha: admin123
        </div>
        
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $nome_empresa; ?>. Todos os direitos reservados.</p>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('senha');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
        
        // Auto-focus on email field
        document.getElementById('email').focus();
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value.trim();
            
            if (!email || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos.');
                return false;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('.btn-login');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrando...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html> 