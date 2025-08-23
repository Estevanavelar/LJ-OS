# 🚀 LJ-OS Sistema

Sistema PHP desenvolvido com XAMPP e configurado para desenvolvimento web moderno.

## 📋 Pré-requisitos

- **XAMPP** instalado e configurado
- **PHP 8.0+** (incluído no XAMPP)
- **Composer** (gerenciador de dependências PHP)
- **Git** para controle de versão

## 🛠️ Instalação

### Instalação Automática (Recomendada)

O sistema LJ-OS possui um instalador automático que configura tudo para você:

#### Opção 1: Instalador Web (Interface Gráfica)
1. Acesse: `http://localhost/LJ-OS/install_web.php`
2. Siga os passos na tela
3. O sistema será configurado automaticamente

#### Opção 2: Instalador via Linha de Comando
```bash
# No diretório do projeto
php install.php
```

### Instalação Manual

1. **Clone o repositório:**
   ```bash
   git clone [URL_DO_REPOSITORIO]
   cd LJ-OS
   ```

2. **Instale as dependências:**
   ```bash
   composer install
   ```

3. **Configure o ambiente:**
   - Copie o arquivo `.env.example` para `.env` (quando disponível)
   - Ajuste as configurações de banco de dados em `config/config.php`

## 🚀 Como executar

### Opção 1: Servidor PHP embutido (desenvolvimento)
```bash
# Iniciar servidor de desenvolvimento
composer dev

# Ou iniciar servidor padrão
composer start
```

### Opção 2: XAMPP (produção)
- Coloque o projeto em `C:\xampp\htdocs\LJ-OS`
- Acesse: `http://localhost/LJ-OS/app/`

## 📁 Estrutura do projeto

```
LJ-OS/
├── app/             # Arquivos da aplicação (web root)
│   ├── assets/      # CSS, JS e imagens
│   ├── components/  # Componentes reutilizáveis
│   ├── languages/   # Arquivos de idioma
│   └── index.php    # Ponto de entrada da aplicação
├── config/          # Configurações da aplicação
├── logs/            # Arquivos de log
├── src/             # Código fonte da aplicação
│   ├── Database/    # Classes de banco de dados
│   ├── Models/      # Modelos de dados
│   ├── Utils/       # Utilitários (idiomas, temas)
│   └── Auth/        # Autenticação e autorização
├── tests/           # Testes unitários
├── vendor/          # Dependências do Composer
├── .vscode/         # Configurações do VS Code
├── composer.json    # Configurações do Composer
├── .gitignore       # Arquivos ignorados pelo Git
└── README.md        # Este arquivo
```

## 🔧 Comandos úteis

```bash
# Executar testes
composer test

# Verificar cobertura de testes
composer test-coverage

# Análise estática de código
composer analyze

# Verificar padrões de código
composer cs-check

# Corrigir padrões de código
composer cs-fix
```

## ⚙️ Configurações

### VS Code
O projeto já está configurado para usar o PHP do XAMPP. As configurações estão em `.vscode/settings.json`.

### PHP
- **Versão mínima**: 8.0.0
- **Timezone**: America/Sao_Paulo
- **Locale**: pt_BR
- **Debug**: Habilitado em desenvolvimento

## 🌐 Endpoints disponíveis

- **`/`** - Redireciona para página de login
- **`/app/login.php`** - Página de login do sistema
- **`/app/api/auth.php`** - API de autenticação (login, logout, refresh)
- **`/app/api/clientes.php`** - API de gestão de clientes
- **`/app/api/status`** - Status da API em JSON

## 🌍 Funcionalidades de Internacionalização

### Idiomas Suportados
- **Português (Brasil)** - Idioma padrão
- **English (US)** - Inglês americano

### Sistema de Temas
- **Modo Claro** - Tema padrão com cores suaves
- **Modo Escuro** - Tema escuro para ambientes com pouca luz

### Opções de Acessibilidade
- **Contraste Baixo** - Para usuários com sensibilidade visual
- **Contraste Normal** - Configuração padrão
- **Contraste Alto** - Para melhor legibilidade

### Tamanhos de Fonte
- **Pequeno** - Para telas grandes
- **Médio** - Tamanho padrão
- **Grande** - Para melhor legibilidade

## 🎨 Personalização da Interface

O sistema permite personalização completa através de:
- Seleção de idioma
- Alternância entre temas claro/escuro
- Ajuste de contraste
- Controle de tamanho da fonte
- Configurações salvas automaticamente
- Transições suaves entre temas

## 🧪 Testes

### Teste do Sistema
Execute o arquivo de teste para verificar se tudo está funcionando:

```bash
# Via navegador
http://localhost/LJ-OS/test_system.php

# Via linha de comando
php test_system.php
```

### Teste das Novas Funcionalidades
Para testar as funcionalidades de tema e idioma:

1. **Acesse a página de configurações**:
   ```
   http://localhost/LJ-OS/app/components/theme-settings.php
   ```

2. **Teste os controles de tema**:
   - Clique no botão de tema para alternar entre claro/escuro
   - Use os seletores para ajustar contraste e tamanho da fonte
   - Altere o idioma e veja as traduções

3. **Verifique a persistência**:
   - As configurações são salvas automaticamente
   - Recarregue a página para confirmar que foram mantidas

### Testes Unitários
```bash
# Executar todos os testes
composer test

# Executar testes com cobertura
composer test-coverage
```

## 📝 Logs

Os logs são armazenados na pasta `logs/` e são configurados para:
- Rotação automática (máximo 30 arquivos)
- Nível configurável via variável de ambiente
- Formato estruturado

## 🔒 Segurança

- **Autenticação JWT** com tokens seguros
- **Controle de permissões** granular por usuário
- **Validação de entrada** de dados
- **Sanitização de saída** para prevenir XSS
- **Prepared statements** para prevenir SQL injection
- **Sessões configuradas** com `httponly`

### Credenciais Padrão
- **Email**: admin@lj-os.com
- **Senha**: admin123
- **Nível**: ADMIN (acesso total ao sistema)

⚠️ **IMPORTANTE**: Altere a senha do administrador após o primeiro login!

## 📞 Suporte

Para dúvidas ou problemas:
- Abra uma issue no repositório
- Entre em contato com a equipe de desenvolvimento

## 📄 Licença

Este projeto está licenciado sob a licença MIT.

---

**Desenvolvido com ❤️ pela equipe LJ-OS**
