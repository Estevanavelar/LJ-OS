
<?php
/**
 * Script de Reset Completo do Sistema
 * Remove todos os dados e prepara para nova instalação
 */

echo "🧹 Resetando Sistema LJ-OS...\n\n";

// Verificar se realmente quer fazer o reset
echo "⚠️  ATENÇÃO: Esta operação irá:\n";
echo "   - Remover banco de dados\n";
echo "   - Limpar todos os uploads\n";
echo "   - Apagar logs\n";
echo "   - Resetar configurações\n\n";

// Remover banco de dados
$db_path = __DIR__ . '/database/lj_os.db';
if (file_exists($db_path)) {
    unlink($db_path);
    echo "✅ Banco de dados removido\n";
} else {
    echo "ℹ️  Banco de dados já estava limpo\n";
}

// Remover lock de instalação
$lock_path = __DIR__ . '/config/installed.lock';
if (file_exists($lock_path)) {
    unlink($lock_path);
    echo "✅ Lock de instalação removido\n";
}

// Limpar diretórios
$directories = ['logs', 'uploads', 'temp', 'backup'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✅ Diretório $dir limpo\n";
    }
}

// Criar arquivo .gitkeep para manter diretórios
$keep_dirs = ['logs', 'uploads', 'temp', 'backup', 'database'];
foreach ($keep_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/.gitkeep', '');
}

echo "\n🎉 Reset concluído!\n";
echo "🚀 Sistema pronto para nova instalação\n";
echo "💡 Execute: php setup_database.php quando quiser configurar novamente\n";
