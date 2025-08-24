<?php
/**
 * LJ-OS - Instalador Web
 * 
 * Interface web para instalação automática do sistema
 */

// Verificar se já está instalado
if (file_exists(__DIR__ . '/.installed')) {
    header('Location: /');
    exit();
}

// Processar formulário de instalação
$step = $_POST['step'] ?? 1;
$errors = [];
$success = false;
$baseUrl = '';
$dbConfig = [];
$adminConfig = [];

if ($_POST && $step == 2) {
    // Obter dados do formulário
    $baseUrl = $_POST['base_url'] ?? '';
    $dbConfig = [
        'host' => $_POST['db_host'] ?? 'localhost',
        'name' => $_POST['db_name'] ?? '',
        'user' => $_POST['db_user'] ?? '',
        'password' => $_POST['db_password'] ?? ''
    ];
    $adminConfig = [
        'email' => $_POST['admin_email'] ?? '',
        'password' => $_POST['admin_password'] ?? ''
    ];
    
    $result = processInstallation($baseUrl, $dbConfig, $adminConfig);
    if ($result['success']) {
        $success = true;
        // Marcar como instalado
        file_put_contents(__DIR__ . '/.installed', date('Y-m-d H:i:s'));
    } else {
        $errors = $result['errors'];
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LJ-OS - Instalação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .install-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .step-indicator { width: 100%; height: 4px; background: #e9ecef; border-radius: 2px; }
        .step-progress { height: 100%; background: linear-gradient(90deg, #28a745, #20c997); border-radius: 2px; transition: width 0.3s; }
        .feature-icon { font-size: 2rem; color: #28a745; }
        .requirement-item { padding: 10px; border-radius: 8px; margin-bottom: 10px; }
        .requirement-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .requirement-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .form-section h5 { color: #495057; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="install-card shadow-lg p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <h1 class="display-4 text-primary mb-3">
                            <i class="fas fa-tools"></i> LJ-OS
                        </h1>
                        <p class="lead text-muted">Sistema de Gestão para Oficinas</p>
                        <div class="step-indicator">
                            <div class="step-progress" style="width: <?= $step == 1 ? '50%' : '100%' ?>"></div>
                        </div>
                    </div>

                    <?php if ($step == 1): ?>
                        <!-- Passo 1: Verificação de Requisitos -->
                        <div class="text-center mb-4">
                            <h3><i class="fas fa-clipboard-check"></i> Verificação de Requisitos</h3>
                            <p class="text-muted">Verificando se seu servidor atende aos requisitos mínimos</p>
                        </div>

                        <?php $requirements = checkSystemRequirements(); ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="requirement-item <?= $requirements['php'] ? 'requirement-success' : 'requirement-error' ?>">
                                    <i class="fas fa-<?= $requirements['php'] ? 'check' : 'times' ?>"></i>
                                    <strong>PHP 8.0+</strong><br>
                                    <small>Versão atual: <?= PHP_VERSION ?></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="requirement-item <?= $requirements['pdo'] ? 'requirement-success' : 'requirement-error' ?>">
                                    <i class="fas fa-<?= $requirements['pdo'] ? 'check' : 'times' ?>"></i>
                                    <strong>PDO SQLite</strong><br>
                                    <small>Extensão de banco de dados</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="requirement-item <?= $requirements['writable'] ? 'requirement-success' : 'requirement-error' ?>">
                                    <i class="fas fa-<?= $requirements['writable'] ? 'check' : 'times' ?>"></i>
                                    <strong>Permissões de Escrita</strong><br>
                                    <small>Diretório atual</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="requirement-item <?= $requirements['extensions'] ? 'requirement-success' : 'requirement-error' ?>">
                                    <i class="fas fa-<?= $requirements['extensions'] ? 'check' : 'times' ?>"></i>
                                    <strong>Extensões PHP</strong><br>
                                    <small>JSON, OpenSSL, MBString</small>
                                </div>
                            </div>
                        </div>

                        <?php if ($requirements['php'] && $requirements['pdo'] && $requirements['writable'] && $requirements['extensions']): ?>
                            <div class="text-center">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Todos os requisitos foram atendidos!</strong>
                                </div>
                                
                                <!-- Configuração da Instalação -->
                                <div class="row justify-content-center mt-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <i class="fas fa-cog"></i> Configuração da Instalação
                                                </h5>
                                                <p class="card-text text-muted">
                                                    Configure os parâmetros necessários para a instalação do sistema.
                                                </p>
                                                
                                                <form method="post" id="configForm">
                                                    <input type="hidden" name="step" value="2">
                                                    
                                                    <!-- Configuração do Banco de Dados -->
                                                    <div class="form-section">
                                                        <h5><i class="fas fa-database"></i> Configuração do Banco de Dados</h5>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="db_host" class="form-label">
                                                                    <strong>Host do Banco:</strong>
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">
                                                                        <i class="fas fa-server"></i>
                                                                    </span>
                                                                    <input type="text" 
                                                                           class="form-control" 
                                                                           id="db_host" 
                                                                           name="db_host" 
                                                                           placeholder="localhost"
                                                                           value="<?= htmlspecialchars($dbConfig['host'] ?? 'localhost') ?>"
                                                                           required>
                                                                </div>
                                                                <div class="form-text">
                                                                    <i class="fas fa-info-circle"></i>
                                                                    Endereço do servidor de banco de dados
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="db_name" class="form-label">
                                                                    <strong>Nome do Banco:</strong>
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">
                                                                        <i class="fas fa-database"></i>
                                                                    </span>
                                                                    <input type="text" 
                                                                           class="form-control" 
                                                                           id="db_name" 
                                                                           name="db_name" 
                                                                           placeholder="lj_os_db"
                                                                           value="<?= htmlspecialchars($dbConfig['name'] ?? 'lj_os_db') ?>"
                                                                           required>
                                                                </div>
                                                                <div class="form-text">
                                                                    <i class="fas fa-info-circle"></i>
                                                                    Nome do banco de dados
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="db_user" class="form-label">
                                                                    <strong>Usuário do Banco:</strong>
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">
                                                                        <i class="fas fa-user"></i>
                                                                    </span>
                                                                    <input type="text" 
                                                                           class="form-control" 
                                                                           id="db_user" 
                                                                           name="db_user" 
                                                                           placeholder="root"
                                                                           value="<?= htmlspecialchars($dbConfig['user'] ?? 'root') ?>"
                                                                           required>
                                                                </div>
                                                                <div class="form-text">
                                                                    <i class="fas fa-info-circle"></i>
                                                                    Usuário para conexão com o banco
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="db_password" class="form-label">
                                                                    <strong>Senha do Banco:</strong>
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">
                                                                        <i class="fas fa-lock"></i>
                                                                    </span>
                                                                    <input type="password" 
                                                                           class="form-control" 
                                                                           id="db_password" 
                                                                           name="db_password" 
                                                                           placeholder=""
                                                                           value="<?= htmlspecialchars($dbConfig['password'] ?? '') ?>">
                                                                </div>
                                                                <div class="form-text">
                                                                    <i class="fas fa-info-circle"></i>
                                                                    Senha do usuário do banco (deixe em branco se não houver)
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Configuração do Usuário Administrador -->
                                                    <div class="form-section">
                                                        <h5><i class="fas fa-user-shield"></i> Usuário Administrador</h5>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label for="admin_email" class="form-label">
                                                                    <strong>E-mail do Administrador:</strong>
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">
                                                                        <i class="fas fa-envelope"></i>
                                                                    </span>
                                                                    <input type="email" 
                                                                           class="form-control" 
                                                                           id="admin_email" 
                                                                           name="admin_email" 
                                                                           placeholder="admin@lj-os.com"
                                                                           value="<?= htmlspecialchars($adminConfig['email'] ?? 'admin@lj-os.com') ?>"
                                                                           required>
                                                                </div>
                                                                <div class="form-text">
                                                                    <i class="fas fa-info-circle"></i>
                                                                    E-mail para acesso ao sistema
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-md-6 mb-3">
                                                                <label for="admin_password" class="form-label">
                                                                    <strong>Senha do Administrador:</strong>
                                                                </label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text">
                                                                        <i class="fas fa-lock"></i>
                                                                    </span>
                                                                    <input type="password" 
                                                                           class="form-control" 
                                                                           id="admin_password" 
                                                                           name="admin_password" 
                                                                           placeholder="admin123"
                                                                           value="<?= htmlspecialchars($adminConfig['password'] ?? 'admin123') ?>"
                                                                           required>
                                                                </div>
                                                                <div class="form-text">
                                                                    <i class="fas fa-info-circle"></i>
                                                                    Senha para acesso ao sistema
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Configuração da URL do Sistema -->
                                                    <div class="form-section">
                                                        <h5><i class="fas fa-globe"></i> Configuração da URL do Sistema</h5>
                                                        
                                                        <div class="mb-3">
                                                            <label for="base_url" class="form-label">
                                                                <strong>URL Raiz do Sistema:</strong>
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">
                                                                    <i class="fas fa-link"></i>
                                                                </span>
                                                                <input type="url" 
                                                                       class="form-control" 
                                                                       id="base_url" 
                                                                       name="base_url" 
                                                                       placeholder="http://localhost/LJ-OS"
                                                                       value="<?= htmlspecialchars($baseUrl ?: 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/LJ-OS') ?>"
                                                                       required>
                                                            </div>
                                                            <div class="form-text">
                                                                <i class="fas fa-info-circle"></i>
                                                                Exemplos: 
                                                                <code>http://localhost/LJ-OS</code>, 
                                                                <code>https://seudominio.com</code>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-grid gap-2">
                                                        <button type="submit" class="btn btn-primary btn-lg">
                                                            <i class="fas fa-rocket"></i> Iniciar Instalação
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Alguns requisitos não foram atendidos.</strong>
                                <p class="mb-0">Corrija os problemas listados acima antes de continuar com a instalação.</p>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($step == 2): ?>
                        <!-- Passo 2: Processando Instalação -->
                        <?php if ($success): ?>
                            <div class="text-center">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h3>Instalação Concluída com Sucesso!</h3>
                                    <p class="lead">O sistema LJ-OS foi instalado e configurado corretamente.</p>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-database fa-2x text-primary mb-3"></i>
                                                <h5>Banco de Dados</h5>
                                                <p class="text-muted">Configurado e funcionando</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-user-shield fa-2x text-success mb-3"></i>
                                                <h5>Usuário Admin</h5>
                                                <p class="text-muted">Criado com sucesso</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="<?= htmlspecialchars($baseUrl) ?>/app/" class="btn btn-success btn-lg">
                                        <i class="fas fa-sign-in-alt"></i> Acessar Sistema
                                    </a>
                                </div>
                                
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>Credenciais de Acesso:</strong><br>
                                        E-mail: <?= htmlspecialchars($adminConfig['email']) ?><br>
                                        Senha: <?= htmlspecialchars($adminConfig['password']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Erro durante a instalação:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="text-center">
                                <a href="?step=1" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Voltar e Corrigir
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Validação do Formulário -->
    <script>
        document.getElementById('configForm').addEventListener('submit', function(e) {
            const requiredFields = ['db_host', 'db_name', 'db_user', 'admin_email', 'admin_password', 'base_url'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    </script>
</body>
</html>

<?php
/**
 * Verifica os requisitos do sistema
 */
function checkSystemRequirements(): array
{
    $requirements = [];
    
    // PHP 8.0+
    $requirements['php'] = version_compare(PHP_VERSION, '8.0.0', '>=');
    
    // PDO SQLite
    $requirements['pdo'] = extension_loaded('pdo') && extension_loaded('pdo_sqlite');
    
    // Permissões de escrita
    $requirements['writable'] = is_writable(__DIR__);
    
    // Extensões necessárias
    $extensions = ['json', 'openssl', 'mbstring'];
    $requirements['extensions'] = true;
    foreach ($extensions as $ext) {
        if (!extension_loaded($ext)) {
            $requirements['extensions'] = false;
            break;
        }
    }
    
    return $requirements;
}

/**
 * Processa a instalação
 */
function processInstallation(string $baseUrl = '', array $dbConfig = [], array $adminConfig = []): array
{
    $errors = [];
    
    try {
        // Validar URL base
        if (empty($baseUrl)) {
            $errors[] = 'URL raiz é obrigatória';
            return ['success' => false, 'errors' => $errors];
        }
        
        // Validar formato da URL
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'URL raiz inválida. Use um formato válido (ex: http://localhost/LJ-OS)';
            return ['success' => false, 'errors' => $errors];
        }
        
        // Validar configurações do banco
        if (empty($dbConfig['host']) || empty($dbConfig['name']) || empty($dbConfig['user'])) {
            $errors[] = 'Configurações do banco de dados são obrigatórias';
            return ['success' => false, 'errors' => $errors];
        }
        
        // Validar configurações do admin
        if (empty($adminConfig['email']) || empty($adminConfig['password'])) {
            $errors[] = 'Configurações do usuário administrador são obrigatórias';
            return ['success' => false, 'errors' => $errors];
        }
        
        // Validar e-mail do admin
        if (!filter_var($adminConfig['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'E-mail do administrador inválido';
            return ['success' => false, 'errors' => $errors];
        }
        
        // Remover barra final se existir
        $baseUrl = rtrim($baseUrl, '/');
        
        // Carregar autoloader
        if (file_exists(__DIR__ . '/autoload.php')) {
            require_once __DIR__ . '/autoload.php';
        } else {
            $errors[] = 'Arquivo autoload.php não encontrado';
            return ['success' => false, 'errors' => $errors];
        }
        
        // Criar diretórios
        $directories = ['database', 'logs', 'cache', 'tmp', 'uploads'];
        foreach ($directories as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (!is_dir($path)) {
                if (!mkdir($path, 0755, true)) {
                    $errors[] = "Não foi possível criar o diretório: $dir";
                }
            }
        }
        
        // Configurar banco
        try {
            $db = LJOS\Database\Database::getInstance();
            $connection = $db->getConnection();
            
            // Verificar se as tabelas foram criadas
            $stmt = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
            if (!$stmt->fetch()) {
                $errors[] = 'Tabelas do banco não foram criadas';
            }
        } catch (Exception $e) {
            $errors[] = 'Erro ao conectar ao banco: ' . $e->getMessage();
        }
        
        // Criar usuário admin se não existir
        if (empty($errors)) {
            try {
                $stmt = $connection->query("SELECT id_usuario FROM usuarios WHERE email = '" . addslashes($adminConfig['email']) . "'");
                if (!$stmt->fetch()) {
                    $senha = password_hash($adminConfig['password'], PASSWORD_DEFAULT);
                    $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, status, data_cadastro) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $connection->prepare($sql);
                    $stmt->execute([
                        'Administrador',
                        $adminConfig['email'],
                        $senha,
                        'ADMIN',
                        'ATIVO',
                        date('Y-m-d H:i:s')
                    ]);
                }
            } catch (Exception $e) {
                $errors[] = 'Erro ao criar usuário admin: ' . $e->getMessage();
            }
        }
        
        // Criar arquivo de configuração com a URL base
        if (empty($errors)) {
            try {
                $configContent = "<?php\n";
                $configContent .= "/**\n";
                $configContent .= " * Configuração da URL Base do LJ-OS\n";
                $configContent .= " * Gerado automaticamente durante a instalação\n";
                $configContent .= " */\n\n";
                $configContent .= "define('BASE_URL', '" . addslashes($baseUrl) . "');\n";
                $configContent .= "define('APP_URL', BASE_URL . '/app');\n";
                $configContent .= "define('API_URL', BASE_URL . '/app/api');\n";
                $configContent .= "define('ASSETS_URL', BASE_URL . '/app/assets');\n";
                $configContent .= "define('COMPONENTS_URL', BASE_URL . '/app/components');\n";
                
                $configFile = __DIR__ . '/config/urls.php';
                $configDir = dirname($configFile);
                
                if (!is_dir($configDir)) {
                    mkdir($configDir, 0755, true);
                }
                
                if (file_put_contents($configFile, $configContent) === false) {
                    $errors[] = 'Erro ao criar arquivo de configuração de URLs';
                }
            } catch (Exception $e) {
                $errors[] = 'Erro ao criar arquivo de configuração: ' . $e->getMessage();
            }
        }
        
        // Criar arquivo de configuração do banco
        if (empty($errors)) {
            try {
                $dbConfigContent = "<?php\n";
                $dbConfigContent .= "/**\n";
                $dbConfigContent .= " * Configuração do Banco de Dados do LJ-OS\n";
                $dbConfigContent .= " * Gerado automaticamente durante a instalação\n";
                $dbConfigContent .= " */\n\n";
                $dbConfigContent .= "define('DB_HOST', '" . addslashes($dbConfig['host']) . "');\n";
                $dbConfigContent .= "define('DB_NAME', '" . addslashes($dbConfig['name']) . "');\n";
                $dbConfigContent .= "define('DB_USER', '" . addslashes($dbConfig['user']) . "');\n";
                $dbConfigContent .= "define('DB_PASSWORD', '" . addslashes($dbConfig['password']) . "');\n";
                
                $dbConfigFile = __DIR__ . '/config/database.php';
                $dbConfigDir = dirname($dbConfigFile);
                
                if (!is_dir($dbConfigDir)) {
                    mkdir($dbConfigDir, 0755, true);
                }
                
                if (file_put_contents($dbConfigFile, $dbConfigContent) === false) {
                    $errors[] = 'Erro ao criar arquivo de configuração do banco';
                }
            } catch (Exception $e) {
                $errors[] = 'Erro ao criar arquivo de configuração do banco: ' . $e->getMessage();
            }
        }
        
    } catch (Exception $e) {
        $errors[] = 'Erro geral: ' . $e->getMessage();
    }
    
    if (empty($errors)) {
        return ['success' => true, 'errors' => []];
    } else {
        return ['success' => false, 'errors' => $errors];
    }
}
?>
