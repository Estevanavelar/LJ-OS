
<?php
/**
 * Script completo de configuração do banco de dados SQLite
 * LJ-OS Sistema para Lava Jato - Replit
 */

require_once 'config/database.php';

echo "🚀 Configurando banco de dados SQLite completo...\n\n";

try {
    $pdo = getDB();

    echo "✅ Conexão com SQLite estabelecida\n";
    echo "📁 Banco: " . DB_PATH . "\n\n";

    // Ativar foreign keys no SQLite
    $pdo->exec("PRAGMA foreign_keys = ON");

    // Remover tabelas existentes se houver conflitos
    $pdo->exec("DROP TABLE IF EXISTS ordens_servico");
    $pdo->exec("DROP TABLE IF EXISTS agendamentos");
    $pdo->exec("DROP TABLE IF EXISTS servicos");
    $pdo->exec("DROP TABLE IF EXISTS categorias_servicos");
    $pdo->exec("DROP TABLE IF EXISTS veiculos");
    $pdo->exec("DROP TABLE IF EXISTS clientes");
    echo "🗑️ Tabelas antigas removidas\n";

    // Criar tabelas principais com estrutura correta
    $sql = "
    -- Tabela de usuários
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

    -- Tabela de configurações (estrutura simples para SQLite)
    CREATE TABLE IF NOT EXISTS configuracoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        chave VARCHAR(100) UNIQUE NOT NULL,
        valor TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Tabela de clientes
    CREATE TABLE IF NOT EXISTS clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome VARCHAR(100) NOT NULL,
        tipo_pessoa VARCHAR(2) DEFAULT 'PF',
        cpf_cnpj VARCHAR(20),
        email VARCHAR(100),
        telefone VARCHAR(20),
        endereco TEXT,
        cidade VARCHAR(50),
        estado VARCHAR(2),
        cep VARCHAR(10),
        ativo BOOLEAN DEFAULT 1,
        pontos_fidelidade INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Tabela de veículos
    CREATE TABLE IF NOT EXISTS veiculos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_cliente INTEGER NOT NULL,
        placa VARCHAR(10) UNIQUE NOT NULL,
        marca VARCHAR(50) NOT NULL,
        modelo VARCHAR(100) NOT NULL,
        ano INTEGER NOT NULL,
        cor VARCHAR(30) NOT NULL,
        combustivel VARCHAR(20),
        km_atual INTEGER,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id)
    );

    -- Tabela de categorias de serviços (usando nomes corretos das colunas)
    CREATE TABLE IF NOT EXISTS categorias_servicos (
        id_categoria INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_categoria VARCHAR(100) NOT NULL,
        descricao TEXT,
        status VARCHAR(20) DEFAULT 'ativo'
    );

    -- Tabela de serviços (usando nomes corretos das colunas)
    CREATE TABLE IF NOT EXISTS servicos (
        id_servico INTEGER PRIMARY KEY AUTOINCREMENT,
        id_categoria INTEGER,
        nome_servico VARCHAR(150) NOT NULL,
        descricao TEXT,
        preco DECIMAL(10,2) NOT NULL,
        duracao_estimada INTEGER NOT NULL,
        tipo_veiculo VARCHAR(20) DEFAULT 'todos',
        status VARCHAR(20) DEFAULT 'ativo',
        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_categoria) REFERENCES categorias_servicos(id_categoria)
    );

    -- Tabela de agendamentos
    CREATE TABLE IF NOT EXISTS agendamentos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_cliente INTEGER NOT NULL,
        id_veiculo INTEGER NOT NULL,
        id_servico INTEGER NOT NULL,
        data_agendamento DATETIME NOT NULL,
        vaga INTEGER,
        status VARCHAR(20) DEFAULT 'pendente',
        valor_estimado DECIMAL(10,2),
        observacoes TEXT,
        usuario_cadastro INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id),
        FOREIGN KEY (id_veiculo) REFERENCES veiculos(id),
        FOREIGN KEY (id_servico) REFERENCES servicos(id_servico),
        FOREIGN KEY (usuario_cadastro) REFERENCES usuarios(id)
    );

    -- Tabela de ordens de serviço
    CREATE TABLE IF NOT EXISTS ordens_servico (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(20) UNIQUE NOT NULL,
        id_cliente INTEGER NOT NULL,
        id_veiculo INTEGER NOT NULL,
        id_agendamento INTEGER,
        data_abertura DATETIME DEFAULT CURRENT_TIMESTAMP,
        data_inicio DATETIME,
        data_conclusao DATETIME,
        status VARCHAR(20) DEFAULT 'aberta',
        valor_servicos DECIMAL(10,2) DEFAULT 0.00,
        valor_produtos DECIMAL(10,2) DEFAULT 0.00,
        desconto DECIMAL(10,2) DEFAULT 0.00,
        valor_total DECIMAL(10,2) DEFAULT 0.00,
        forma_pagamento VARCHAR(20),
        vaga INTEGER,
        observacoes TEXT,
        usuario_abertura INTEGER,
        usuario_conclusao INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_cliente) REFERENCES clientes(id),
        FOREIGN KEY (id_veiculo) REFERENCES veiculos(id),
        FOREIGN KEY (id_agendamento) REFERENCES agendamentos(id),
        FOREIGN KEY (usuario_abertura) REFERENCES usuarios(id),
        FOREIGN KEY (usuario_conclusao) REFERENCES usuarios(id)
    );

    -- Tabela de produtos
    CREATE TABLE IF NOT EXISTS produtos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(50),
        nome VARCHAR(150) NOT NULL,
        descricao TEXT,
        categoria VARCHAR(100),
        unidade VARCHAR(20) DEFAULT 'unidade',
        preco_custo DECIMAL(10,2) DEFAULT 0.00,
        preco_venda DECIMAL(10,2) NOT NULL,
        estoque_atual INTEGER DEFAULT 0,
        estoque_minimo INTEGER DEFAULT 0,
        ativo BOOLEAN DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Tabela de movimentações de estoque
    CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_produto INTEGER NOT NULL,
        tipo VARCHAR(20) NOT NULL,
        quantidade INTEGER NOT NULL,
        valor_unitario DECIMAL(10,2),
        data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP,
        motivo VARCHAR(255),
        documento VARCHAR(100),
        usuario_responsavel INTEGER,
        FOREIGN KEY (id_produto) REFERENCES produtos(id),
        FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id)
    );

    -- Tabela de financeiro
    CREATE TABLE IF NOT EXISTS financeiro (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo VARCHAR(20) NOT NULL,
        categoria VARCHAR(100) NOT NULL,
        descricao TEXT NOT NULL,
        valor DECIMAL(10,2) NOT NULL,
        data_transacao DATE NOT NULL,
        data_vencimento DATE,
        forma_pagamento VARCHAR(50),
        status VARCHAR(20) DEFAULT 'pendente',
        id_os INTEGER,
        documento VARCHAR(100),
        observacoes TEXT,
        usuario_responsavel INTEGER,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_os) REFERENCES ordens_servico(id),
        FOREIGN KEY (usuario_responsavel) REFERENCES usuarios(id)
    );
    ";

    // Executar criação das tabelas
    $pdo->exec($sql);
    echo "✅ Tabelas criadas com sucesso\n\n";

    // Inserir usuário administrador
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nivel_acesso = 'admin'");
    $stmt->execute();
    $admin_exists = $stmt->fetchColumn() > 0;

    if (!$admin_exists) {
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrador', 'admin@lavajato.com', $senha_hash, 'admin']);
        echo "✅ Usuário administrador criado\n";
    } else {
        echo "ℹ️ Usuário administrador já existe\n";
    }

    // Inserir configurações básicas
    $configuracoes = [
        'nome_empresa' => 'LJ-OS Sistema para Lava Jato',
        'cnpj_empresa' => '00.000.000/0001-00',
        'endereco_empresa' => 'Rua Exemplo, 123 - Centro',
        'telefone_empresa' => '(11) 99999-9999',
        'email_empresa' => 'contato@lavajato.com',
        'horario_funcionamento' => '08:00 às 18:00',
        'dias_funcionamento' => 'Segunda a Sábado',
        'taxa_entrega' => '0.00',
        'desconto_fidelidade' => '5.00',
        'estoque_minimo_padrao' => '5',
        'cor_primaria' => '#007bff',
        'cor_secundaria' => '#6c757d'
    ];

    foreach ($configuracoes as $chave => $valor) {
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO configuracoes (chave, valor, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$chave, $valor]);
    }
    echo "✅ Configurações básicas inseridas\n\n";

    // Inserir categorias de serviços
    $categorias = [
        ['Lavagem Simples', 'Serviços básicos de lavagem externa'],
        ['Lavagem Completa', 'Serviços completos incluindo enceramento'],
        ['Higienização', 'Serviços de limpeza interna e higienização'],
        ['Estética Automotiva', 'Serviços especializados de estética'],
        ['Serviços Especiais', 'Serviços diferenciados e personalizados']
    ];

    foreach ($categorias as $categoria) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO categorias_servicos (nome_categoria, descricao) VALUES (?, ?)");
        $stmt->execute($categoria);
    }
    echo "✅ Categorias de serviços inseridas\n";

    // Inserir serviços básicos
    $servicos = [
        [1, 'Lavagem Simples Carro', 'Lavagem externa básica para carros', 15.00, 30, 'carro'],
        [1, 'Lavagem Simples Moto', 'Lavagem externa básica para motos', 10.00, 20, 'moto'],
        [2, 'Lavagem Completa Carro', 'Lavagem completa com enceramento para carros', 25.00, 45, 'carro'],
        [2, 'Lavagem Completa Moto', 'Lavagem completa com enceramento para motos', 18.00, 35, 'moto'],
        [3, 'Higienização Interna', 'Limpeza e higienização do interior do veículo', 30.00, 60, 'todos'],
        [4, 'Enceramento', 'Aplicação de cera protetora', 20.00, 30, 'todos'],
        [4, 'Polimento', 'Polimento da pintura', 80.00, 120, 'todos']
    ];

    foreach ($servicos as $servico) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO servicos (id_categoria, nome_servico, descricao, preco, duracao_estimada, tipo_veiculo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($servico);
    }
    echo "✅ Serviços básicos inseridos\n\n";

    echo "🎉 Banco de dados configurado com sucesso!\n\n";
    echo "🔐 Dados de acesso padrão:\n";
    echo "   Email: admin@lavajato.com\n";
    echo "   Senha: admin123\n\n";
    echo "🌐 Acesse o sistema em: http://localhost:5000\n";

} catch (Exception $e) {
    echo "❌ Erro na configuração: " . $e->getMessage() . "\n";
    exit(1);
}
?>
