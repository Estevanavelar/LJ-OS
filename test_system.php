
<?php
/**
 * Script de Teste do Sistema LJ-OS
 */

echo "🧪 Testando Sistema LJ-OS...\n\n";

// Teste 1: Verificar arquivos essenciais
echo "📁 Verificando arquivos essenciais:\n";
$arquivos_essenciais = [
    'config/config.php',
    'config/database.php',
    'includes/functions.php',
    'index.php',
    'login.php',
    'dashboard.php'
];

foreach ($arquivos_essenciais as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo\n";
    } else {
        echo "❌ $arquivo (FALTANDO)\n";
    }
}

// Teste 2: Verificar conexão com banco
echo "\n🗄️ Testando conexão com banco:\n";
try {
    require_once 'config/database.php';
    $pdo = getDB();
    echo "✅ Conexão com banco funcionando\n";
    
    // Verificar tabelas principais
    $tabelas = ['usuarios', 'clientes', 'servicos', 'configuracoes'];
    foreach ($tabelas as $tabela) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $tabela");
            $count = $stmt->fetchColumn();
            echo "✅ Tabela $tabela: $count registros\n";
        } catch (Exception $e) {
            echo "❌ Tabela $tabela: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
}

// Teste 3: Verificar usuário admin
echo "\n👤 Verificando usuário administrador:\n";
try {
    $stmt = $pdo->query("SELECT nome, email FROM usuarios WHERE id_perfil = 1 OR nivel_acesso = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    if ($admin) {
        echo "✅ Admin encontrado: {$admin['nome']} ({$admin['email']})\n";
        echo "🔑 Senha padrão: admin123\n";
    } else {
        echo "❌ Usuário administrador não encontrado\n";
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar admin: " . $e->getMessage() . "\n";
}

// Teste 4: Verificar permissões de diretório
echo "\n📂 Verificando permissões:\n";
$diretorios = ['uploads', 'logs', 'database', 'temp'];
foreach ($diretorios as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? "✅" : "⚠️";
        echo "$writable $dir " . (is_writable($dir) ? "(escrita OK)" : "(sem permissão de escrita)") . "\n";
    } else {
        echo "❌ $dir (não existe)\n";
    }
}

// Teste 5: Verificar URL de acesso
echo "\n🌐 URLs de acesso:\n";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
echo "🔗 Sistema: {$protocol}{$host}/\n";
echo "🔗 Login: {$protocol}{$host}/login.php\n";
echo "🔗 Dashboard: {$protocol}{$host}/dashboard.php\n";

echo "\n✅ Teste concluído!\n";
echo "💡 Se tudo estiver OK, acesse o sistema pelo navegador.\n";
