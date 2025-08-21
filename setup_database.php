
<?php
/**
 * Script de configuração do banco de dados SQLite
 * LJ-OS Sistema para Lava Jato - Replit
 */

require_once 'config/database.php';

echo "🚀 Configurando banco de dados SQLite...\n\n";

try {
    $pdo = getDB();
    
    echo "✅ Conexão com SQLite estabelecida\n";
    echo "📁 Banco: " . DB_PATH . "\n\n";
    
    // Criar tabelas básicas
    $sql = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        nivel_acesso VARCHAR(20) DEFAULT 'funcionario',
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        telefone VARCHAR(20),
        endereco TEXT,
        cpf_cnpj VARCHAR(20),
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS servicos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10,2) NOT NULL,
        tempo_estimado INTEGER DEFAULT 30,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS configuracoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        descricao TEXT,
        tipo VARCHAR(20) DEFAULT 'texto',
        categoria VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS veiculos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_cliente INTEGER NOT NULL,
        modelo VARCHAR(100),
        marca VARCHAR(50),
        ano INTEGER,
        placa VARCHAR(10),
        cor VARCHAR(30),
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id)
    );
    
    CREATE TABLE IF NOT EXISTS ordens_servico (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        numero VARCHAR(20) UNIQUE NOT NULL,
        id_cliente INTEGER NOT NULL,
        id_veiculo INTEGER,
        status VARCHAR(20) DEFAULT 'pendente',
        total DECIMAL(10,2) DEFAULT 0,
        observacoes TEXT,
        data_entrada DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_conclusao DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id),
        FOREIGN KEY (id_veiculo) REFERENCES veiculos(id)
    );EFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS veiculos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_cliente INTEGER NOT NULL,
        placa VARCHAR(10) UNIQUE NOT NULL,
        marca VARCHAR(50) NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        ano INTEGER NOT NULL,
        cor VARCHAR(30) NOT NULL,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id)
    );
    
    CREATE TABLE IF NOT EXISTS servicos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10,2) NOT NULL,
        tempo_estimado INTEGER DEFAULT 30,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS ordens_servico (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(20) UNIQUE NOT NULL,
        id_cliente INTEGER NOT NULL,
        id_veiculo INTEGER NOT NULL,
        data_abertura DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_conclusao DATETIME NULL,
        status VARCHAR(20) DEFAULT 'aberta',
        valor_total DECIMAL(10,2) DEFAULT 0.00,
        observacoes TEXT,
        usuario_abertura INTEGER NULL,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id),
        FOREIGN KEY (id_veiculo) REFERENCES veiculos(id),
        FOREIGN KEY (usuario_abertura) REFERENCES usuarios(id)
    );
    
    CREATE TABLE IF NOT EXISTS configuracoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $pdo->exec($sql);
    echo "✅ Tabelas criadas com sucesso\n";
    
    // Inserir usuário administrador padrão
    $admin_exists = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE email = 'admin@lavajato.com'")->fetchColumn();
    if ($admin_exists == 0) {
        $admin_senha = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES ('Administrador', 'admin@lavajato.com', '$admin_senha', 'admin')");
        echo "✅ Usuário administrador criado\n";
    }
    
    // Inserir configurações básicas
    $config_exists = $pdo->query("SELECT COUNT(*) FROM configuracoes WHERE chave = 'nome_empresa'")->fetchColumn();
    if ($config_exists == 0) {
        $configs = [
            ['nome_empresa', 'LJ-OS Sistema', 'Nome da empresa'],
            ['email_empresa', 'contato@lavajato.com', 'Email da empresa'],
            ['telefone_empresa', '(11) 99999-9999', 'Telefone da empresa'],
            ['endereco_empresa', 'Rua Example, 123', 'Endereço da empresa']
        ];
        
        foreach ($configs as $config) {
            $pdo->exec("INSERT INTO configuracoes (chave, valor, descricao) VALUES ('{$config[0]}', '{$config[1]}', '{$config[2]}')");
        }
        echo "✅ Configurações básicas inseridas\n";
    }
    
    // Inserir serviços básicos
    $servicos_exists = $pdo->query("SELECT COUNT(*) FROM servicos")->fetchColumn();
    if ($servicos_exists == 0) {
        $servicos = [
            ['Lavagem Simples', 'Lavagem externa básica', 25.00, 30],
            ['Lavagem Completa', 'Lavagem externa e interna', 45.00, 60],
            ['Enceramento', 'Aplicação de cera protetora', 35.00, 45],
            ['Aspiração', 'Limpeza interna com aspirador', 20.00, 20]
        ];
        
        foreach ($servicos as $servico) {
            $pdo->exec("INSERT INTO servicos (nome, descricao, preco, tempo_estimado) VALUES ('{$servico[0]}', '{$servico[1]}', {$servico[2]}, {$servico[3]})");
        }
        echo "✅ Serviços básicos inseridos\n";
    }
    
    echo "✅ Setup concluído com sucesso!\n";
    
    // Inserir usuário admin padrão
    $admin_exists = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE nivel_acesso = 'admin'")->fetchColumn();
    
    if ($admin_exists == 0) {
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrador', 'admin@lavajato.com', $senha_hash, 'admin']);
        echo "✅ Usuário administrador criado\n";
        echo "   Email: admin@lavajato.com\n";
        echo "   Senha: admin123\n";
    }
    
    // Inserir serviços básicos
    $servicos_exists = $pdo->query("SELECT COUNT(*) FROM servicos")->fetchColumn();
    
    if ($servicos_exists == 0) {
        $servicos = [
            ['Lavagem Simples', 'Lavagem externa básica', 15.00, 30],
            ['Lavagem Completa', 'Lavagem completa com enceramento', 25.00, 45],
            ['Higienização Interna', 'Limpeza e higienização do interior', 30.00, 60],
            ['Enceramento', 'Aplicação de cera protetora', 20.00, 30]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO servicos (nome, descricao, preco, tempo_estimado) VALUES (?, ?, ?, ?)");
        foreach ($servicos as $servico) {
            $stmt->execute($servico);
        }
        echo "✅ Serviços básicos inseridos\n";
    }
    
    // Inserir configurações básicas
    $configs = [
        'nome_empresa' => 'LJ-OS Sistema',
        'telefone_empresa' => '(11) 99999-9999',
        'endereco_empresa' => 'Rua Exemplo, 123',
        'horario_funcionamento' => '08:00 às 18:00',
        'dias_funcionamento' => 'Segunda a Sábado'
    ];
    
    foreach ($configs as $chave => $valor) {
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO configuracoes (chave, valor) VALUES (?, ?)");
        $stmt->execute([$chave, $valor]);
    }
    
    echo "✅ Configurações básicas inseridas\n";
    echo "\n🎉 Sistema configurado com sucesso!\n";
    echo "💡 Acesse o sistema através do navegador\n";
    echo "🌐 URL: https://" . $_SERVER['HTTP_HOST'] . "\n";
    echo "👤 Login: admin@lavajato.com\n";
    echo "🔑 Senha: admin123\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>
