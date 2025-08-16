
<?php
/**
 * Script para configurar o banco de dados no Replit
 */

require_once 'config/database.php';

try {
    // Conectar sem especificar banco para criar se necessário
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    
    // Criar banco se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Banco de dados criado/verificado com sucesso!\n";
    
    // Conectar ao banco específico
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    // Verificar se as tabelas existem
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️  Banco vazio. Execute install.php para criar as tabelas.\n";
        echo "🔗 Acesse: " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/install.php\n";
    } else {
        echo "✅ Banco configurado com " . count($tables) . " tabelas!\n";
        echo "🔗 Acesse: " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro na configuração do banco: " . $e->getMessage() . "\n";
    echo "💡 Certifique-se de que o MySQL está ativo no Replit.\n";
}
?>
