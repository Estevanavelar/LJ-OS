<?php
/**
 * LJ-OS - Limpeza e Reinstalação
 * 
 * Script para limpar completamente o sistema e permitir reinstalação
 */

// Verificar se é uma requisição POST (confirmação)
$confirmed = $_POST['confirmed'] ?? false;
$step = $_POST['step'] ?? 1;
$errors = [];
$success = false;
$filesRemoved = [];
$directoriesRemoved = [];

if ($_POST && $step == 2 && $confirmed) {
    try {
        // Passo 1: Remover arquivo de instalação
        if (file_exists(__DIR__ . '/.installed')) {
            unlink(__DIR__ . '/.installed');
            $filesRemoved[] = '.installed';
        }
        
        // Passo 2: Remover banco de dados
        if (file_exists(__DIR__ . '/database/lj_os.db')) {
            unlink(__DIR__ . '/database/lj_os.db');
            $filesRemoved[] = 'database/lj_os.db';
        }
        
        // Passo 3: Remover arquivos de configuração
        $configFiles = [
            'config/urls.php',
            'config/database.php'
        ];
        
        foreach ($configFiles as $file) {
            if (file_exists(__DIR__ . '/' . $file)) {
                unlink(__DIR__ . '/' . $file);
                $filesRemoved[] = $file;
            }
        }
        
        // Passo 4: Limpar diretórios de cache e logs
        $directories = ['logs', 'cache', 'tmp', 'uploads'];
        foreach ($directories as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (is_dir($path)) {
                // Remover todos os arquivos dentro do diretório
                $files = glob($path . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                // Remover diretório se estiver vazio
                if (count(scandir($path)) <= 2) { // . e ..
                    rmdir($path);
                    $directoriesRemoved[] = $dir;
                }
            }
        }
        
        // Passo 5: Limpar diretório database (manter estrutura)
        $dbPath = __DIR__ . '/database';
        if (is_dir($dbPath)) {
            $files = glob($dbPath . '/*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitkeep') {
                    unlink($file);
                }
            }
        }
        
        $success = true;
        
    } catch (Exception $e) {
        $errors[] = 'Erro durante a limpeza: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LJ-OS - Limpeza e Reinstalação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); min-height: 100vh; }
        .clean-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; }
        .warning-icon { font-size: 4rem; color: #dc3545; }
        .danger-zone { border: 2px solid #dc3545; border-radius: 15px; background: #fff5f5; }
        .file-list { max-height: 300px; overflow-y: auto; }
        .step-indicator { width: 100%; height: 4px; background: #e9ecef; border-radius: 2px; }
        .step-progress { height: 100%; background: linear-gradient(90deg, #dc3545, #c82333); border-radius: 2px; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="clean-card shadow-lg p-5">
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <div class="warning-icon mb-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h1 class="display-4 text-danger mb-3">⚠️ ATENÇÃO!</h1>
                        <p class="lead text-muted">Limpeza e Reinstalação do Sistema</p>
                        <div class="step-indicator">
                            <div class="step-progress" style="width: <?= $step == 1 ? '50%' : '100%' ?>"></div>
                        </div>
                    </div>

                    <?php if ($step == 1): ?>
                        <!-- Passo 1: Aviso e Confirmação -->
                        <div class="danger-zone p-4 mb-4">
                            <h3 class="text-danger mb-3">
                                <i class="fas fa-radiation"></i> ZONA DE PERIGO
                            </h3>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-triangle"></i> ATENÇÃO!</h5>
                                <p class="mb-0">
                                    <strong>Esta operação irá:</strong>
                                </p>
                                <ul class="mb-0 mt-2">
                                    <li>❌ <strong>REMOVER</strong> todos os dados do sistema</li>
                                    <li>❌ <strong>DELETAR</strong> o banco de dados</li>
                                    <li>❌ <strong>LIMPAR</strong> todas as configurações</li>
                                    <li>❌ <strong>REMOVER</strong> todos os usuários</li>
                                    <li>❌ <strong>APAGAR</strong> todos os arquivos de cache e logs</li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-info-circle"></i> IMPORTANTE:</h6>
                                <p class="mb-0">
                                    <strong>Esta ação é IRREVERSÍVEL!</strong> Todos os dados serão perdidos permanentemente.
                                    Certifique-se de fazer backup antes de continuar.
                                </p>
                            </div>
                        </div>

                        <div class="text-center">
                            <h4 class="mb-4">Você tem certeza que deseja continuar?</h4>
                            
                            <form method="post" id="confirmForm">
                                <input type="hidden" name="step" value="2">
                                <input type="hidden" name="confirmed" value="true">
                                
                                <div class="d-grid gap-3">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        <i class="fas fa-trash-alt"></i> SIM, LIMPAR TUDO E REINSTALAR
                                    </button>
                                    
                                    <a href="index.php" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times"></i> CANCELAR - Voltar ao Sistema
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Informações Adicionais -->
                        <div class="mt-5">
                            <h5><i class="fas fa-info-circle"></i> O que será removido:</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-danger">Arquivos do Sistema:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-file"></i> .installed</li>
                                        <li><i class="fas fa-database"></i> database/lj_os.db</li>
                                        <li><i class="fas fa-cog"></i> config/urls.php</li>
                                        <li><i class="fas fa-cog"></i> config/database.php</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-danger">Diretórios:</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-folder"></i> logs/*</li>
                                        <li><i class="fas fa-folder"></i> cache/*</li>
                                        <li><i class="fas fa-folder"></i> tmp/*</li>
                                        <li><i class="fas fa-folder"></i> uploads/*</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($step == 2): ?>
                        <!-- Passo 2: Resultado da Limpeza -->
                        <?php if ($success): ?>
                            <div class="text-center">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h3>Sistema Limpo com Sucesso!</h3>
                                    <p class="lead">Todos os dados foram removidos e o sistema está pronto para reinstalação.</p>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-trash-alt fa-2x text-danger mb-3"></i>
                                                <h5>Arquivos Removidos</h5>
                                                <p class="text-muted"><?= count($filesRemoved) ?> arquivos deletados</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-folder-open fa-2x text-warning mb-3"></i>
                                                <h5>Diretórios Limpos</h5>
                                                <p class="text-muted"><?= count($directoriesRemoved) ?> diretórios limpos</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lista de arquivos removidos -->
                                <?php if (!empty($filesRemoved)): ?>
                                    <div class="mt-4">
                                        <h6><i class="fas fa-list"></i> Arquivos Removidos:</h6>
                                        <div class="file-list">
                                            <ul class="list-group">
                                                <?php foreach ($filesRemoved as $file): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <i class="fas fa-file text-danger"></i>
                                                        <?= htmlspecialchars($file) ?>
                                                        <span class="badge bg-danger rounded-pill">
                                                            <i class="fas fa-trash"></i> Removido
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Botões de ação -->
                                <div class="mt-4">
                                    <div class="d-grid gap-3">
                                        <a href="install_web.php" class="btn btn-success btn-lg">
                                            <i class="fas fa-download"></i> Ir para Instalação
                                        </a>
                                        
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="fas fa-home"></i> Voltar ao Início
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Próximo passo:</strong> Acesse o instalador para configurar o sistema novamente.
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Erro durante a limpeza:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="text-center">
                                <a href="?step=1" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Tentar Novamente
                                </a>
                                
                                <a href="index.php" class="btn btn-primary">
                                    <i class="fas fa-home"></i> Voltar ao Sistema
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
    
    <!-- Confirmação adicional -->
    <script>
        document.getElementById('confirmForm').addEventListener('submit', function(e) {
            const confirmed = confirm('⚠️ ATENÇÃO!\n\nVocê está prestes a REMOVER TODOS os dados do sistema!\n\nEsta ação é IRREVERSÍVEL!\n\nTem certeza que deseja continuar?');
            
            if (!confirmed) {
                e.preventDefault();
                alert('Operação cancelada pelo usuário.');
            }
        });
    </script>
</body>
</html>
