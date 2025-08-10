<?php
/**
 * Área do Cliente - Acesso Direto
 * LJ-OS Sistema para Lava Jato
 */

// Carregar utilitários centrais (sessão segura, CSRF, DB)
require_once __DIR__ . '/../includes/functions.php';

// Verificar se já está logado
if (isset($_SESSION['cliente_id'])) {
    header('Location: dashboard.php');
    exit();
}

$erro = '';
$cpf_cnpj = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    csrf_verificar();

    $cpf_cnpj_limpo = preg_replace('/[^0-9]/', '', $_POST['cpf_cnpj']);
    
    if (strlen($cpf_cnpj_limpo) < 11) {
        $erro = 'CPF/CNPJ inválido';
    } else {
        try {
            $db = getDB();
            
            // Buscar por CPF/CNPJ com ou sem formatação
            $stmt = $db->prepare("SELECT id_cliente, nome, tipo_pessoa, status FROM clientes WHERE (cpf_cnpj = ? OR cpf_cnpj = ?) AND status = 'ativo'");
            $stmt->execute([$cpf_cnpj_limpo, $_POST['cpf_cnpj']]);
            $cliente = $stmt->fetch();
            
            if ($cliente) {
                // Criar sessão do cliente
                $_SESSION['cliente_id'] = $cliente['id_cliente'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                $_SESSION['cliente_tipo'] = $cliente['tipo_pessoa'];
                $_SESSION['cliente_acesso'] = time();
                
                // Log de acesso (se função existir)
                if (function_exists('registrarLogAcesso')) {
                    $stmt = $db->prepare("INSERT INTO logs_acesso_cliente (id_cliente, ip_acesso, user_agent) VALUES (?, ?, ?)");
                    $stmt->execute([$cliente['id_cliente'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
                }
                
                header('Location: dashboard.php');
                exit();
            } else {
                $erro = 'Cliente não encontrado ou inativo';
            }
        } catch (Exception $e) {
            $erro = 'Erro ao verificar cliente';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cliente - LJ-OS Sistema para Lava Jato</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #bdbf90;
            --secondary-color: #35352b;
            --accent-color: #ec6c2b;
            --accent-light: #feae4b;
            --background-light: #e7e9c4;
            --text-dark: #35352b;
            --text-light: #ffffff;
            --border-color: #d1d3a7;
            --shadow-color: rgba(53, 53, 43, 0.1);
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;
            --border-radius-xl: 24px;
        }
        
        body {
            background: linear-gradient(135deg, var(--background-light) 0%, var(--primary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: var(--text-light);
            border-radius: var(--border-radius-xl);
            box-shadow: 0 20px 40px var(--shadow-color);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: var(--text-light);
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header .logo {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .login-header p {
            opacity: 0.9;
            margin: 0;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-md);
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(236, 108, 43, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-light) 100%);
            border: none;
            border-radius: var(--border-radius-md);
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(236, 108, 43, 0.3);
        }
        
        .alert {
            border-radius: var(--border-radius-md);
            border: none;
            padding: 15px 20px;
        }
        
        .features {
            background: var(--background-light);
            padding: 30px;
            text-align: center;
        }
        
        .feature-item {
            margin-bottom: 20px;
        }
        
        .feature-item i {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 10px;
        }
        
        .feature-item h4 {
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .feature-item p {
            color: var(--text-dark);
            opacity: 0.8;
            font-size: 0.9rem;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .login-card {
                margin: 20px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-car-wash"></i>
                </div>
                <h1>Área do Cliente</h1>
                <p>LJ-OS Sistema para Lava Jato</p>
            </div>
            
            <div class="login-body">
                <h3 class="text-center mb-4">Acesso Direto</h3>
                <p class="text-center text-muted mb-4">
                    Digite seu CPF ou CNPJ para acessar sua área
                </p>
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $erro; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <?php echo csrf_field(); ?>
                    <div class="form-floating">
                        <input type="text" 
                               class="form-control" 
                               id="cpf_cnpj" 
                               name="cpf_cnpj" 
                               placeholder="000.000.000-00"
                               value="<?php echo htmlspecialchars($cpf_cnpj); ?>"
                               required>
                        <label for="cpf_cnpj">CPF ou CNPJ</label>
                        <div class="invalid-feedback">
                            Digite um CPF ou CNPJ válido
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Acessar Minha Área
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i>
                        Seus dados estão seguros conosco
                    </small>
                </div>
            </div>
            
            <div class="features">
                <div class="row">
                    <div class="col-6">
                        <div class="feature-item">
                            <i class="fas fa-calendar-check"></i>
                            <h4>Agendamentos</h4>
                            <p>Agende seus serviços</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-item">
                            <i class="fas fa-history"></i>
                            <h4>Histórico</h4>
                            <p>Veja todos os serviços</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-item">
                            <i class="fas fa-coins"></i>
                            <h4>Créditos</h4>
                            <p>Cashback e descontos</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-item">
                            <i class="fas fa-car"></i>
                            <h4>Veículos</h4>
                            <p>Gerencie sua frota</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Validação do formulário
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Máscara para CPF/CNPJ
        document.addEventListener('DOMContentLoaded', function() {
            const cpfCnpjInput = document.getElementById('cpf_cnpj');
            
            cpfCnpjInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                
                if (value.length <= 11) {
                    // CPF
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                } else {
                    // CNPJ
                    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                }
                
                e.target.value = value;
            });
        });
    </script>
</body>
</html> 