<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LJ-OS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .login-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .login-form {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 15px;
            font-size: 16px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .loading {
            display: none;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-car-wash"></i> LJ-OS</h1>
            <p>Sistema de Gestão para Lava Jato</p>
        </div>
        
        <div class="login-form">
            <div id="alert-container"></div>
            
            <form id="loginForm">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                    <label for="email">Email</label>
                </div>
                
                <div class="form-floating position-relative">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                    <label for="senha">Senha</label>
                    <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="lembrar">
                    <label class="form-check-label" for="lembrar">
                        Lembrar de mim
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login" id="btnLogin">
                    <span class="btn-text">Entrar</span>
                    <span class="loading">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Entrando...
                    </span>
                </button>
            </form>
            
            <div class="footer-links">
                <a href="#" id="esqueciSenha">Esqueci minha senha</a>
                <a href="#" id="ajuda">Ajuda</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const senhaInput = document.getElementById('senha');
            const passwordToggle = document.getElementById('passwordToggle');
            const btnLogin = document.getElementById('btnLogin');
            const alertContainer = document.getElementById('alertContainer');
            
            // Toggle de visibilidade da senha
            passwordToggle.addEventListener('click', function() {
                const type = senhaInput.type === 'password' ? 'text' : 'password';
                senhaInput.type = type;
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            // Login
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const email = emailInput.value.trim();
                const senha = senhaInput.value;
                
                if (!email || !senha) {
                    showAlert('Por favor, preencha todos os campos.', 'danger');
                    return;
                }
                
                // Mostrar loading
                setLoading(true);
                
                try {
                    const response = await fetch('../api/auth.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'login',
                            email: email,
                            senha: senha
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Salvar tokens
                        localStorage.setItem('access_token', data.data.access_token);
                        localStorage.setItem('refresh_token', data.data.refresh_token);
                        localStorage.setItem('user', JSON.stringify(data.data.user));
                        
                        // Salvar email se "lembrar" estiver marcado
                        if (document.getElementById('lembrar').checked) {
                            localStorage.setItem('remembered_email', email);
                        } else {
                            localStorage.removeItem('remembered_email');
                        }
                        
                        showAlert('Login realizado com sucesso! Redirecionando...', 'success');
                        
                        // Redirecionar para dashboard
                        setTimeout(() => {
                            window.location.href = 'dashboard.php';
                        }, 1500);
                        
                    } else {
                        showAlert(data.message || 'Erro no login', 'danger');
                    }
                    
                } catch (error) {
                    console.error('Erro:', error);
                    showAlert('Erro de conexão. Tente novamente.', 'danger');
                } finally {
                    setLoading(false);
                }
            });
            
            // Esqueci minha senha
            document.getElementById('esqueciSenha').addEventListener('click', function(e) {
                e.preventDefault();
                showAlert('Entre em contato com o administrador para redefinir sua senha.', 'info');
            });
            
            // Ajuda
            document.getElementById('ajuda').addEventListener('click', function(e) {
                e.preventDefault();
                showAlert('Para suporte técnico, entre em contato com a equipe de desenvolvimento.', 'info');
            });
            
            // Carregar email salvo
            const rememberedEmail = localStorage.getItem('remembered_email');
            if (rememberedEmail) {
                emailInput.value = rememberedEmail;
                document.getElementById('lembrar').checked = true;
            }
            
            // Funções auxiliares
            function showAlert(message, type) {
                alertContainer.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
            }
            
            function setLoading(loading) {
                if (loading) {
                    btnLogin.disabled = true;
                    btnLogin.querySelector('.btn-text').style.display = 'none';
                    btnLogin.querySelector('.loading').style.display = 'inline-block';
                } else {
                    btnLogin.disabled = false;
                    btnLogin.querySelector('.btn-text').style.display = 'inline';
                    btnLogin.querySelector('.loading').style.display = 'none';
                }
            }
            
            // Verificar se já está logado
            const token = localStorage.getItem('access_token');
            if (token) {
                // Verificar se token ainda é válido
                fetch('../api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        action: 'me'
                    })
                }).then(response => {
                    if (response.ok) {
                        // Token válido, redirecionar
                        window.location.href = 'dashboard.php';
                    } else {
                        // Token inválido, limpar
                        localStorage.removeItem('access_token');
                        localStorage.removeItem('refresh_token');
                        localStorage.removeItem('user');
                    }
                }).catch(() => {
                    // Erro de conexão, limpar tokens
                    localStorage.removeItem('access_token');
                    localStorage.removeItem('refresh_token');
                    localStorage.removeItem('user');
                });
            }
        });
    </script>
</body>
</html>
