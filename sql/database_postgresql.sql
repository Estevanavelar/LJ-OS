
-- LJ-OS Sistema para Lava Jato - PostgreSQL
-- Script de criação das tabelas principais

-- Tabela de configurações
CREATE TABLE IF NOT EXISTS configuracoes (
    id SERIAL PRIMARY KEY,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descricao TEXT,
    tipo VARCHAR(20) DEFAULT 'texto',
    categoria VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    nivel_acesso VARCHAR(20) DEFAULT 'funcionario',
    ativo BOOLEAN DEFAULT true,
    ultimo_acesso TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    telefone VARCHAR(20),
    whatsapp VARCHAR(20),
    cpf_cnpj VARCHAR(18),
    endereco TEXT,
    cidade VARCHAR(50),
    estado VARCHAR(2),
    cep VARCHAR(10),
    observacoes TEXT,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de veículos
CREATE TABLE IF NOT EXISTS veiculos (
    id SERIAL PRIMARY KEY,
    cliente_id INTEGER REFERENCES clientes(id),
    marca VARCHAR(50),
    modelo VARCHAR(50),
    ano INTEGER,
    cor VARCHAR(30),
    placa VARCHAR(10),
    tipo_veiculo VARCHAR(20) DEFAULT 'carro',
    observacoes TEXT,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de serviços
CREATE TABLE IF NOT EXISTS servicos (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    duracao_estimada INTEGER DEFAULT 60,
    categoria VARCHAR(50),
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir dados padrão
INSERT INTO configuracoes (chave, valor, descricao, categoria) VALUES 
('nome_empresa', 'LJ-OS Sistema para Lava Jato', 'Nome da empresa', 'empresa'),
('email_empresa', 'admin@lavajato.com', 'Email da empresa', 'empresa'),
('telefone_empresa', '(11) 9999-9999', 'Telefone da empresa', 'empresa'),
('endereco_empresa', 'Rua Exemplo, 123', 'Endereço da empresa', 'empresa')
ON CONFLICT (chave) DO NOTHING;

INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES 
('Administrador', 'admin@lavajato.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON CONFLICT (email) DO NOTHING;

INSERT INTO servicos (nome, descricao, preco, categoria) VALUES 
('Lavagem Simples', 'Lavagem externa do veículo', 15.00, 'lavagem'),
('Lavagem Completa', 'Lavagem externa e interna', 25.00, 'lavagem'),
('Enceramento', 'Aplicação de cera protetora', 30.00, 'acabamento'),
('Aspiração', 'Limpeza interna com aspirador', 10.00, 'limpeza')
ON CONFLICT DO NOTHING;
