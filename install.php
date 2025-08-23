<?php
/**
 * LJ-OS - Instalador Automático
 * 
 * Este script configura automaticamente o ambiente do sistema
 */

// Configurações de instalação
$config = [
    'php_version' => '8.0.0',
    'extensions' => ['pdo', 'pdo_sqlite', 'json', 'openssl', 'mbstring'],
    'directories' => [
        'database',
        'logs',
        'cache',
        'tmp',
        'uploads',
        'uploads/clientes',
        'uploads/veiculos',
        'uploads/produtos',
        'uploads/relatorios'
    ],
    'permissions' => 0755
];

// Iniciar instalação
echo "🚀 Iniciando instalação do LJ-OS...\n\n";

// Verificar requisitos
echo "📋 Verificando requisitos do sistema...\n";
$requirements = checkRequirements($config);
if (!$requirements['success']) {
    echo "❌ Requisitos não atendidos:\n";
    foreach ($requirements['errors'] as $error) {
        echo "   - $error\n";
    }
    exit(1);
}
echo "✅ Requisitos atendidos!\n\n";

// Criar diretórios
echo "📁 Criando estrutura de diretórios...\n";
$directories = createDirectories($config['directories'], $config['permissions']);
if (!$directories['success']) {
    echo "❌ Erro ao criar diretórios:\n";
    foreach ($directories['errors'] as $error) {
        echo "   - $error\n";
    }
    exit(1);
}
echo "✅ Diretórios criados com sucesso!\n\n";

// Configurar banco de dados
echo "🗄️ Configurando banco de dados...\n";
$database = setupDatabase();
if (!$database['success']) {
    echo "❌ Erro ao configurar banco:\n";
    echo "   - " . $database['error'] . "\n";
    exit(1);
}
echo "✅ Banco de dados configurado!\n\n";

// Criar arquivo de configuração
echo "⚙️ Criando arquivo de configuração...\n";
$configFile = createConfigFile();
if (!$configFile['success']) {
    echo "❌ Erro ao criar arquivo de configuração:\n";
    echo "   - " . $configFile['error'] . "\n";
    exit(1);
}
echo "✅ Arquivo de configuração criado!\n\n";

// Criar usuário administrador
echo "👤 Criando usuário administrador...\n";
$admin = createAdminUser();
if (!$admin['success']) {
    echo "❌ Erro ao criar usuário admin:\n";
    echo "   - " . $admin['error'] . "\n";
    exit(1);
}
echo "✅ Usuário administrador criado!\n\n";

// Verificar instalação
echo "🔍 Verificando instalação...\n";
$verification = verifyInstallation();
if (!$verification['success']) {
    echo "❌ Erro na verificação:\n";
    echo "   - " . $verification['error'] . "\n";
    exit(1);
}
echo "✅ Instalação verificada com sucesso!\n\n";

// Finalizar instalação
echo "🎉 Instalação concluída com sucesso!\n\n";
echo "📝 Informações de acesso:\n";
echo "   - URL: http://localhost/LJ-OS/\n";
echo "   - Email: admin@lj-os.com\n";
echo "   - Senha: admin123\n\n";
echo "⚠️ IMPORTANTE: Altere a senha do administrador após o primeiro login!\n\n";

/**
 * Verifica requisitos do sistema
 */
function checkRequirements(array $config): array
{
    $errors = [];
    
    // Verificar versão do PHP
    if (version_compare(PHP_VERSION, $config['php_version'], '<')) {
        $errors[] = "PHP {$config['php_version']} ou superior é necessário. Versão atual: " . PHP_VERSION;
    }
    
    // Verificar extensões
    foreach ($config['extensions'] as $extension) {
        if (!extension_loaded($extension)) {
            $errors[] = "Extensão PHP '$extension' não está carregada";
        }
    }
    
    // Verificar permissões de escrita
    if (!is_writable(__DIR__)) {
        $errors[] = "Diretório atual não tem permissão de escrita";
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Cria diretórios necessários
 */
function createDirectories(array $directories, int $permissions): array
{
    $errors = [];
    
    foreach ($directories as $dir) {
        $path = __DIR__ . '/' . $dir;
        
        if (!is_dir($path)) {
            if (!mkdir($path, $permissions, true)) {
                $errors[] = "Não foi possível criar o diretório: $dir";
            }
        }
        
        if (!is_writable($path)) {
            if (!chmod($path, $permissions)) {
                $errors[] = "Não foi possível definir permissões para: $dir";
            }
        }
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Configura o banco de dados
 */
function setupDatabase(): array
{
    try {
        // Carregar autoloader
        if (file_exists(__DIR__ . '/autoload.php')) {
            require_once __DIR__ . '/autoload.php';
        }
        
        // Tentar conectar ao banco
        $db = LJOS\Database\Database::getInstance();
        $connection = $db->getConnection();
        
        // Verificar se as tabelas foram criadas
        $stmt = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='usuarios'");
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'error' => 'Tabelas do banco não foram criadas automaticamente'
            ];
        }
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Cria arquivo de configuração
 */
function createConfigFile(): array
{
    try {
        $configPath = __DIR__ . '/config/config.php';
        
        if (file_exists($configPath)) {
            return ['success' => true]; // Já existe
        }
        
        // Verificar se o diretório config existe
        $configDir = dirname($configPath);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        // Criar arquivo .env
        $envPath = __DIR__ . '/.env';
        if (!file_exists($envPath)) {
            $envContent = "# Configurações do ambiente LJ-OS\n";
            $envContent .= "APP_ENV=production\n";
            $envContent .= "APP_DEBUG=false\n";
            $envContent .= "JWT_SECRET=" . generateRandomString(32) . "\n";
            $envContent .= "JWT_EXPIRATION=3600\n";
            $envContent .= "JWT_REFRESH_EXPIRATION=604800\n";
            $envContent .= "DB_DRIVER=sqlite\n";
            $envContent .= "DB_DATABASE=lj_os\n";
            $envContent .= "LOG_LEVEL=info\n";
            $envContent .= "CACHE_DRIVER=file\n";
            
            if (file_put_contents($envPath, $envContent) === false) {
                return [
                    'success' => false,
                    'error' => 'Não foi possível criar o arquivo .env'
                ];
            }
        }
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Cria usuário administrador
 */
function createAdminUser(): array
{
    try {
        // Verificar se já existe usuário admin
        $db = LJOS\Database\Database::getInstance();
        $connection = $db->getConnection();
        
        $stmt = $connection->query("SELECT id_usuario FROM usuarios WHERE email = 'admin@lj-os.com'");
        if ($stmt->fetch()) {
            return ['success' => true]; // Já existe
        }
        
        // Criar usuário admin
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
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Verifica se a instalação foi bem-sucedida
 */
function verifyInstallation(): array
{
    try {
        // Verificar se o banco está funcionando
        $db = LJOS\Database\Database::getInstance();
        $connection = $db->getConnection();
        
        // Verificar se as principais tabelas existem
        $tables = ['usuarios', 'clientes', 'veiculos', 'servicos', 'agendamentos'];
        foreach ($tables as $table) {
            $stmt = $connection->query("SELECT COUNT(*) FROM $table");
            if (!$stmt) {
                return [
                    'success' => false,
                    'error' => "Tabela '$table' não está funcionando corretamente"
                ];
            }
        }
        
        // Verificar se o usuário admin foi criado
        $stmt = $connection->query("SELECT id_usuario FROM usuarios WHERE email = 'admin@lj-os.com'");
        if (!$stmt->fetch()) {
            return [
                'success' => false,
                'error' => 'Usuário administrador não foi criado'
            ];
        }
        
        return ['success' => true];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Gera string aleatória
 */
function generateRandomString(int $length): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $string;
}
?>
