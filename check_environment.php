
<?php
/**
 * Script para verificar se o ambiente Replit está configurado corretamente
 */

echo "🔍 Verificando ambiente Replit...\n\n";

// Verificar se está no Replit
if (getenv('REPL_ID')) {
    echo "✅ Executando no Replit (ID: " . getenv('REPL_ID') . ")\n";
} else {
    echo "⚠️  Não está no ambiente Replit\n";
}

// Verificar PHP
echo "🐘 PHP Version: " . PHP_VERSION . "\n";

// Verificar extensões necessárias
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext\n";
    } else {
        echo "❌ $ext (FALTANDO)\n";
    }
}

// Verificar banco de dados
try {
    require_once 'config/database.php';
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    echo "✅ Conexão MySQL funcionando\n";
} catch (Exception $e) {
    echo "❌ Erro MySQL: " . $e->getMessage() . "\n";
}

// Verificar diretórios
$dirs = ['uploads', 'logs', 'config'];
foreach ($dirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "✅ Diretório $dir (OK)\n";
    } else {
        echo "⚠️  Diretório $dir (verificar permissões)\n";
    }
}

echo "\n🚀 Para configurar o sistema:\n";
echo "1. Execute: php setup_database.php\n";
echo "2. Acesse install.php no navegador\n";
echo "3. Configure seus dados e comece a usar!\n";
?>
