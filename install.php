<?php
/**
 * Script de Instalação
 * LJ-OS Sistema para Lava Jato
 */

// Carregar helpers para sessão e CSRF, se existir
$functionsPath = __DIR__ . '/includes/functions.php';
if (file_exists($functionsPath)) {
    require_once $functionsPath;
}

// Verificar se já está instalado
$ja_instalado = file_exists('config/installed.lock');
$banco_existe = false;

// Verificar se o banco já existe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF, se função existir
    if (function_exists('csrf_verificar')) {
        csrf_verificar();
    }

    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    
    try {
        $dsn = "mysql:host=$db_host;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar se o banco lava_jato_db existe
        $stmt = $pdo->query("SHOW DATABASES LIKE 'lava_jato_db'");
        $banco_existe = $stmt->rowCount() > 0;
    } catch (Exception $e) {
        // Ignorar erro de conexão aqui
    }
}

$erro = '';
$sucesso = '';

// Processar instalação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF já verificado acima (se disponível)
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? 'lava_jato_db';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_email = $_POST['admin_email'] ?? 'admin@lavajato.com';
    $admin_senha = $_POST['admin_senha'] ?? 'admin123';
    $nome_empresa = $_POST['nome_empresa'] ?? 'LJ-OS Sistema para Lava Jato';
    
    try {
        // Testar conexão com PostgreSQL
        if (isset($_ENV['DATABASE_URL'])) {
            $pdo = getDB();
        } else {
            $dsn = "pgsql:host=$db_host;port=5432";
            $pdo = new PDO($dsn, $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        // Se o banco já existia, verificar se as tabelas existem
        if ($banco_existe) {
            $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
            $tabelas_existem = $stmt->rowCount() > 0;
            
            if ($tabelas_existem) {
                // Apenas atualizar configurações e admin
                $pdo->exec("UPDATE configuracoes SET valor = '$nome_empresa' WHERE chave = 'nome_empresa'");
                $pdo->exec("UPDATE configuracoes SET valor = '$admin_email' WHERE chave = 'email_empresa'");
                
                // Atualizar senha do admin
                $senha_hash = password_hash($admin_senha, PASSWORD_DEFAULT);
                $pdo->exec("UPDATE usuarios SET email = '$admin_email', senha = '$senha_hash' WHERE nivel_acesso = 'admin'");
                
                $sucesso = "Sistema atualizado com sucesso! O banco de dados já existia e foi preservado.";
            } else {
                // Banco existe mas tabelas não, executar script SQL
                $sql_file = file_get_contents('sql/database_completo.sql');
                $statements = explode(';', $sql_file);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement) && !preg_match('/^CREATE DATABASE|^USE/i', $statement)) {
                        $pdo->exec($statement);
                    }
                }
                
                // Atualizar configurações
                $pdo->exec("UPDATE configuracoes SET valor = '$nome_empresa' WHERE chave = 'nome_empresa'");
                $pdo->exec("UPDATE configuracoes SET valor = '$admin_email' WHERE chave = 'email_empresa'");
                
                // Atualizar senha do admin
                $senha_hash = password_hash($admin_senha, PASSWORD_DEFAULT);
                $pdo->exec("UPDATE usuarios SET email = '$admin_email', senha = '$senha_hash' WHERE nivel_acesso = 'admin'");
                
                $sucesso = "Sistema instalado com sucesso! O banco de dados foi criado e as tabelas foram adicionadas.";
            }
        } else {
            // Executar script SQL do PostgreSQL
            $sql_file = file_get_contents('sql/database_postgresql.sql');
            $statements = explode(';', $sql_file);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            // Atualizar configurações
            $pdo->exec("UPDATE configuracoes SET valor = '$nome_empresa' WHERE chave = 'nome_empresa'");
            $pdo->exec("UPDATE configuracoes SET valor = '$admin_email' WHERE chave = 'email_empresa'");
            
            // Atualizar senha do admin
            $senha_hash = password_hash($admin_senha, PASSWORD_DEFAULT);
            $pdo->exec("UPDATE usuarios SET email = '$admin_email', senha = '$senha_hash' WHERE nivel_acesso = 'admin'");
            
            $sucesso = "Sistema instalado com sucesso!";
        }
        
        // Criar arquivo de configuração
        $config_content = "<?php\n /**\n  * Configuração de conexão com o banco de dados\n  * LJ-OS Sistema para Lava Jato\n  */\n\n // Configurações do banco de dados\n define('DB_HOST', '$db_host');\n define('DB_NAME', '$db_name');\n define('DB_USER', '$db_user');\n define('DB_PASS', '$db_pass');\n define('DB_CHARSET', 'utf8mb4');\n\n /**\n  * Classe para gerenciar conexões com o banco de dados\n  */\n class Database {\n     private static \$instance = null;\n     private \$connection;\n     \n     private function __construct() {\n         try {\n             \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;\n             \$options = [\n                 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n                 PDO::ATTR_EMULATE_PREPARES => false,\n                 PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4\"\n             ];\n             \$this->connection = new PDO(\$dsn, DB_USER, DB_PASS, \$options);\n         } catch (PDOException \$e) {\n             error_log(\"Erro de conexão com banco de dados: \" . \$e->getMessage());\n             die(\"Erro de conexão com o banco de dados. Tente novamente mais tarde.\");\n         }\n     }\n     public static function getInstance() {\n         if (self::\$instance === null) {\n             self::\$instance = new self();\n         }\n         return self::\$instance;\n     }\n     public function getConnection() {\n         return \$this->connection;\n     }\n     private function __clone() {}\n     public function __wakeup() {}\n }\n function getDB() {\n     return Database::getInstance()->getConnection();\n }\n?>";
        
        file_put_contents('config/database.php', $config_content);
        
        // Criar diretórios necessários
        $diretorios = ['uploads', 'uploads/clientes', 'uploads/veiculos', 'uploads/os', 'logs'];
        foreach ($diretorios as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        // Criar arquivo de lock
        file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
        
        $sucesso = 'Sistema instalado com sucesso! Você pode fazer login com as credenciais configuradas.';
        
    } catch (Exception $e) {
        $erro = 'Erro na instalação: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - LJ-OS Sistema para Lava Jato</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .install-container {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 3rem;
            max-width: 600px;
            width: 100%;
        }
        
        .install-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .install-header h1 {
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }
        
        .install-header p {
            color: var(--text-muted);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: border-color var(--transition-fast);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
        }
        
        .btn-install {
            width: 100%;
            padding: 1rem;
            background: var(--accent-color);
            color: white;
            border: none;
            border-radius: var(--border-radius-sm);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background var(--transition-fast);
        }
        
        .btn-install:hover {
            background: var(--accent-light);
        }
        
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .requirements {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--border-radius-sm);
            margin-bottom: 2rem;
        }
        
        .requirements h3 {
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }
        
        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .requirement-item:last-child {
            border-bottom: none;
        }
        
        .status {
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status-ok {
            background: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🚗 LJ-OS Sistema para Lava Jato</h1>
            <p>Instalação e Configuração do Sistema</p>
        </div>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <strong>✅ Sucesso!</strong> <?php echo $sucesso; ?>
                <br><br>
                <a href="login.php" class="btn-install">Ir para o Login</a>
            </div>
        <?php else: ?>
            <!-- Verificação de Requisitos -->
            <div class="requirements">
                <h3>📋 Verificação de Requisitos</h3>
                
                <?php
                $requirements_ok = true;
                $php_version = phpversion();
                $php_ok = version_compare($php_version, '7.4.0', '>=');
                $pdo_ok = extension_loaded('pdo_mysql');
                $mbstring_ok = extension_loaded('mbstring');
                $curl_ok = extension_loaded('curl');
                $gd_ok = extension_loaded('gd');
                
                if (!$php_ok) $requirements_ok = false;
                if (!$pdo_ok) $requirements_ok = false;
                if (!$mbstring_ok) $requirements_ok = false;
                if (!$curl_ok) $requirements_ok = false;
                if (!$gd_ok) $requirements_ok = false;
                ?>
                
                <div class="requirement-item">
                    <span>PHP 7.4+ (Atual: <?php echo $php_version; ?>)</span>
                    <span class="status <?php echo $php_ok ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $php_ok ? 'OK' : 'ERRO'; ?>
                    </span>
                </div>
                
                <div class="requirement-item">
                    <span>Extensão PDO MySQL</span>
                    <span class="status <?php echo $pdo_ok ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $pdo_ok ? 'OK' : 'ERRO'; ?>
                    </span>
                </div>
                
                <div class="requirement-item">
                    <span>Extensão MBString</span>
                    <span class="status <?php echo $mbstring_ok ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $mbstring_ok ? 'OK' : 'ERRO'; ?>
                    </span>
                </div>
                
                <div class="requirement-item">
                    <span>Extensão cURL</span>
                    <span class="status <?php echo $curl_ok ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $curl_ok ? 'OK' : 'ERRO'; ?>
                    </span>
                </div>
                
                <div class="requirement-item">
                    <span>Extensão GD</span>
                    <span class="status <?php echo $gd_ok ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $gd_ok ? 'OK' : 'ERRO'; ?>
                    </span>
                </div>
            </div>
            
            <?php if (!$requirements_ok): ?>
                <div class="alert alert-danger">
                    <strong>❌ Erro!</strong> Alguns requisitos não foram atendidos. 
                    Verifique se todas as extensões PHP necessárias estão instaladas.
                </div>
            <?php else: ?>
                <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <strong>❌ Erro!</strong> <?php echo $erro; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($banco_existe): ?>
                    <div class="alert alert-info">
                        <strong>ℹ️ Informação:</strong> O banco de dados 'lava_jato_db' já existe. 
                        O sistema irá atualizar as configurações e preservar os dados existentes.
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <?php echo function_exists('csrf_field') ? csrf_field() : ''; ?>
                    <h3>🗄️ Configuração do Banco de Dados</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Host do Banco</label>
                        <input type="text" name="db_host" class="form-control" value="localhost" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nome do Banco</label>
                        <input type="text" name="db_name" class="form-control" value="lava_jato_db" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Usuário do Banco</label>
                        <input type="text" name="db_user" class="form-control" value="root" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Senha do Banco</label>
                        <input type="password" name="db_pass" class="form-control" value="">
                    </div>
                    
                    <h3>👤 Configuração do Administrador</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Email do Administrador</label>
                        <input type="email" name="admin_email" class="form-control" value="admin@lavajato.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Senha do Administrador</label>
                        <input type="password" name="admin_senha" class="form-control" value="admin123" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nome da Empresa</label>
                        <input type="text" name="nome_empresa" class="form-control" value="LJ-OS Sistema para Lava Jato" required>
                    </div>
                    
                    <button type="submit" class="btn-install">
                        🚀 Instalar Sistema
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html> 