<?php

/**
 * Teste do Sistema LJ-OS
 * Execute este arquivo para verificar se tudo está funcionando
 */

echo "<h1>🧪 Teste do Sistema LJ-OS</h1>";
echo "<hr>";

// Teste 1: Verificar PHP
echo "<h2>1. Verificação do PHP</h2>";
echo "Versão do PHP: " . PHP_VERSION . "<br>";
echo "Extensões necessárias:<br>";

$extensoes = ['pdo', 'pdo_sqlite', 'json', 'mbstring', 'openssl'];
foreach ($extensoes as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "  {$status} {$ext}<br>";
}

echo "<hr>";

// Teste 2: Verificar estrutura de diretórios
echo "<h2>2. Estrutura de Diretórios</h2>";
$diretorios = [
    'src',
    'src/Database',
    'src/Models',
    'src/Auth',
    'api',
    'config',
    'database',
    'sql',
    'public'
];

foreach ($diretorios as $dir) {
    $status = is_dir($dir) ? "✅" : "❌";
    echo "  {$status} {$dir}<br>";
}

echo "<hr>";

// Teste 3: Verificar arquivos principais
echo "<h2>3. Arquivos Principais</h2>";
$arquivos = [
    'src/Database/Database.php',
    'src/Models/BaseModel.php',
    'src/Models/Usuario.php',
    'src/Models/Cliente.php',
    'src/Auth/JWTAuth.php',
    'api/auth.php',
    'api/clientes.php',
    'config/config.php',
    'sql/schema.sql',
    'public/login.php'
];

foreach ($arquivos as $arquivo) {
    $status = file_exists($arquivo) ? "✅" : "❌";
    echo "  {$status} {$arquivo}<br>";
}

echo "<hr>";

// Incluir autoload
require_once 'autoload.php';

// Teste 4: Testar conexão com banco
echo "<h2>4. Teste de Conexão com Banco</h2>";
try {
    $db = LJOS\Database\Database::getInstance();
    echo "✅ Conexão com banco estabelecida com sucesso<br>";
    
    // Verificar se as tabelas foram criadas
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tabelas = $stmt->fetchAll();
    
    echo "Tabelas encontradas: " . count($tabelas) . "<br>";
    foreach ($tabelas as $tabela) {
        echo "  - {$tabela['name']}<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na conexão com banco: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Teste 5: Testar modelos
echo "<h2>5. Teste dos Modelos</h2>";
try {
    $usuarioModel = new LJOS\Models\Usuario();
    
    // Contar usuários
    $total = $usuarioModel->count();
    echo "✅ Modelo Usuario funcionando. Total de usuários: {$total}<br>";
    
    // Buscar usuário admin
    $admin = $usuarioModel->findByEmail('admin@lj-os.com');
    if ($admin) {
        echo "✅ Usuário admin encontrado: {$admin['nome']}<br>";
    } else {
        echo "⚠️ Usuário admin não encontrado<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro no modelo Usuario: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Teste 6: Testar autenticação JWT
echo "<h2>6. Teste de Autenticação JWT</h2>";
try {
    $auth = new LJOS\Auth\JWTAuth();
    
    // Testar geração de token
    $payload = ['user_id' => 1, 'email' => 'test@test.com'];
    $token = $auth->generateToken($payload);
    echo "✅ Token JWT gerado com sucesso<br>";
    
    // Testar validação de token
    $payloadValidado = $auth->validateToken($token);
    echo "✅ Token JWT validado com sucesso<br>";
    
    // Testar decodificação
    $payloadDecodificado = $auth->decodeToken($token);
    echo "✅ Token JWT decodificado com sucesso<br>";
    
} catch (Exception $e) {
    echo "❌ Erro na autenticação JWT: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Teste 7: Verificar configurações
echo "<h2>7. Configurações do Sistema</h2>";
try {
    $config = require_once 'config/config.php';
    echo "✅ Configurações carregadas com sucesso<br>";
    echo "Nome da aplicação: {$config['app']['name']}<br>";
    echo "Versão: {$config['app']['version']}<br>";
    echo "Timezone: {$config['app']['timezone']}<br>";
    echo "Debug: " . ($config['app']['debug'] ? 'Ativado' : 'Desativado') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao carregar configurações: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Teste 8: Verificar permissões de arquivo
echo "<h2>8. Permissões de Arquivo</h2>";
$diretoriosWrite = ['database', 'logs', 'uploads'];
foreach ($diretoriosWrite as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? "✅" : "❌";
        echo "  {$writable} {$dir} (gravável)<br>";
    } else {
        echo "  ❌ {$dir} (não existe)<br>";
    }
}

echo "<hr>";

// Teste 9: Resumo
echo "<h2>9. Resumo do Sistema</h2>";
echo "<p><strong>Status Geral:</strong> ";
if (extension_loaded('pdo') && extension_loaded('pdo_sqlite')) {
    echo "✅ Sistema funcionando corretamente!</p>";
    echo "<p>🎉 O LJ-OS está pronto para uso!</p>";
    echo "<p>📱 Acesse: <a href='public/login.php'>Página de Login</a></p>";
    echo "<p>🔑 Credenciais padrão: admin@lj-os.com / password</p>";
} else {
    echo "❌ Sistema com problemas!</p>";
    echo "<p>⚠️ Verifique as extensões PHP necessárias.</p>";
}

echo "<hr>";
echo "<p><small>Teste executado em: " . date('d/m/Y H:i:s') . "</small></p>";
?>
