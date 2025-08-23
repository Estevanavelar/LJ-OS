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

if ($_POST && $step == 2) {
    $result = processInstallation();
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
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
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

                        <?php if (array_sum($requirements) == count($requirements)): ?>
                            <div class="text-center">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Todos os requisitos foram atendidos!</strong>
                                </div>
                                <form method="post">
                                    <input type="hidden" name="step" value="2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-rocket"></i> Iniciar Instalação
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Alguns requisitos não foram atendidos.</strong>
                                <p class="mb-0">Por favor, configure seu servidor antes de continuar.</p>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($step == 2 && !$success): ?>
                        <!-- Passo 2: Instalação -->
                        <div class="text-center mb-4">
                            <h3><i class="fas fa-cogs"></i> Instalando Sistema</h3>
                            <p class="text-muted">Configurando banco de dados e criando estrutura</p>
                        </div>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Erro durante a instalação:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="text-center">
                            <form method="post">
                                <input type="hidden" name="step" value="2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-redo"></i> Tentar Novamente
                                </button>
                            </form>
                        </div>

                    <?php elseif ($success): ?>
                        <!-- Instalação Concluída -->
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle feature-icon"></i>
                            </div>
                            <h3 class="text-success">Instalação Concluída com Sucesso!</h3>
                            <p class="text-muted">O sistema LJ-OS foi configurado e está pronto para uso.</p>
                            
                            <div class="alert alert-info text-start">
                                <h6><i class="fas fa-info-circle"></i> Credenciais de Acesso:</h6>
                                <ul class="mb-0">
                                    <li><strong>Email:</strong> admin@lj-os.com</li>
                                    <li><strong>Senha:</strong> admin123</li>
                                </ul>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> Altere a senha do administrador após o primeiro login!
                            </div>

                            <a href="/" class="btn btn-success btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Acessar Sistema
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Features -->
                    <div class="mt-5 pt-4 border-top">
                        <h5 class="text-center mb-4">Recursos do Sistema</h5>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <i class="fas fa-users feature-icon"></i>
                                <p class="small">Gestão de Clientes</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-car feature-icon"></i>
                                <p class="small">Controle de Veículos</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-calendar-alt feature-icon"></i>
                                <p class="small">Agendamentos</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-chart-line feature-icon"></i>
                                <p class="small">Relatórios</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/**
 * Verifica requisitos do sistema
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
function processInstallation(): array
{
    $errors = [];
    
    try {
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
                        $stmt = $connection->query("SELECT id_usuario FROM usuarios WHERE email = 'admin@lj-os.com'");
        if (!$stmt->fetch()) {
                    $senha = password_hash('admin123', PASSWORD_DEFAULT);
                    $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso, status, data_cadastro) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    
                                         $stmt = $connection->prepare($sql);
                     $stmt->execute([
                         'Administrador',
                         'admin@lj-os.com',
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
        
    } catch (Exception $e) {
        $errors[] = 'Erro geral: ' . $e->getMessage();
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors
    ];
}
?>
