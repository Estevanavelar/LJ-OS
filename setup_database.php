
<?php
/**
 * Script para configurar o banco PostgreSQL no Replit
 */

require_once 'config/database.php';

try {
    echo "🔍 Verificando conexão PostgreSQL...\n";
    
    // Testar conexão
    $pdo = getDB();
    echo "✅ Conexão PostgreSQL estabelecida!\n";
    
    // Verificar se as tabelas existem
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "⚠️  Banco vazio. Execute install.php para criar as tabelas.\n";
        echo "🔗 Acesse: https://" . $_SERVER['HTTP_HOST'] . "/install.php\n";
    } else {
        echo "✅ Banco configurado com " . count($tables) . " tabelas!\n";
        echo "🔗 Acesse: https://" . $_SERVER['HTTP_HOST'] . "/\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro na configuração do banco: " . $e->getMessage() . "\n";
    echo "💡 Certifique-se de criar o banco PostgreSQL no Replit:\n";
    echo "   1. Abra uma nova aba e digite 'Database'\n";
    echo "   2. Clique em 'create a database'\n";
    echo "   3. Execute este script novamente\n";
}
?>
