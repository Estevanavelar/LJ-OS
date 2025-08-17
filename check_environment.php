
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
$required_extensions = ['pdo', 'pdo_sqlite', 'mbstring', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext\n";
    } else {
        echo "❌ $ext (FALTANDO)\n";
    }
}

// Verificar banco de dados SQLite
echo "\n🗄️  Verificando banco de dados...\n";
try {
    require_once 'config/database.php';
    $pdo = getDB();
    echo "✅ Conexão SQLite funcionando\n";
    echo "📁 Banco: " . DB_PATH . "\n";
    
    // Verificar se as tabelas existem
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "✅ Tabelas encontradas: " . implode(', ', $tables) . "\n";
    } else {
        echo "⚠️  Nenhuma tabela encontrada - execute setup_database.php\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro SQLite: " . $e->getMessage() . "\n";
    echo "💡 Execute: php setup_database.php\n";
}

// Verificar diretórios
echo "\n📁 Verificando diretórios...\n";
$dirs = ['uploads', 'logs', 'config', 'database'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? "✅" : "⚠️";
        echo "$writable Diretório $dir\n";
    } else {
        echo "❌ Diretório $dir (não existe)\n";
    }
}

echo "\n🚀 Próximos passos:\n";
echo "1. Execute: php setup_database.php\n";
echo "2. Acesse o sistema pelo navegador\n";
echo "3. Faça login com admin@lavajato.com / admin123\n";
