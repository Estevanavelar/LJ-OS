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
2. Configure a **URL raiz** do sistema (ex: `http://localhost/LJ-OS`)
3. Siga os passos na tela
4. O sistema será configurado automaticamente com as URLs corretas

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

### Limpeza e Reinstalação

Se você precisar reinstalar o sistema do zero:

1. **Acesse o script de limpeza:**
   ```
   http://localhost/LJ-OS/clean_and_reinstall.php
   ```

2. **⚠️ ATENÇÃO - Esta operação é IRREVERSÍVEL:**
   - Remove TODOS os dados do sistema
   - Deleta o banco de dados
   - Limpa todas as configurações
   - Remove todos os usuários
   - Apaga arquivos de cache e logs

3. **Processo de limpeza:**
   - Confirme que deseja continuar
   - O sistema será limpo automaticamente
   - Você será redirecionado para o instalador

4. **Após a limpeza:**
   - Acesse `install_web.php` para reinstalar
   - Configure todos os parâmetros novamente
   - O sistema estará limpo e pronto para uso

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
- Acesse: `http://localhost/LJ-OS/` (página principal)
- Acesse: `http://localhost/LJ-OS/app/` (aplicação)
- Acesse: `http://localhost/LJ-OS/install_web.php` (instalador)

## 📁 Estrutura do projeto

```
LJ-OS/
├── app/             # Arquivos da aplicação (web root)
│   ├── assets/      # CSS, JS e imagens
│   ├── components/  # Componentes reutilizáveis
│   ├── languages/   # Arquivos de idioma
│   ├── api/         # APIs do sistema
│   └── index.php    # Ponto de entrada da aplicação
├── config/          # Configurações da aplicação
├── database/        # Banco de dados SQLite
├── logs/            # Arquivos de log
├── cache/           # Cache do sistema
├── tmp/             # Arquivos temporários
├── uploads/         # Arquivos enviados pelos usuários
├── src/             # Código fonte da aplicação
│   ├── Database/    # Classes de banco de dados
│   ├── Models/      # Modelos de dados
│   ├── Utils/       # Utilitários (idiomas, temas)
│   └── Auth/        # Autenticação e autorização
├── sql/             # Scripts SQL do banco
├── script/          # Documentação e especificações
├── .vscode/         # Configurações do VS Code
├── composer.json    # Configurações do Composer
├── .gitignore       # Arquivos ignorados pelo Git
├── autoload.php     # Autoloader personalizado
├── index.php        # Página principal
├── install.php      # Instalador CLI
├── install_web.php  # Instalador web
├── clean_and_reinstall.php # Script de limpeza
├── CLEAN_INSTALL.md # Documentação de limpeza
├── INSTALACAO.md    # Guia de instalação
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

### URLs do Sistema
O sistema LJ-OS gera automaticamente um arquivo `config/urls.php` durante a instalação com todas as URLs necessárias:

```php
// Exemplo de configuração gerada
define('BASE_URL', 'http://localhost/LJ-OS');
define('APP_URL', BASE_URL . '/app');
define('API_URL', BASE_URL . '/app/api');
define('ASSETS_URL', BASE_URL . '/app/assets');
```

**Para usar em suas páginas:**
```php
require_once __DIR__ . '/../config/urls.php';
echo '<link href="' . ASSETS_URL . '/css/themes.css" rel="stylesheet">';
```

### VS Code
O projeto já está configurado para usar o PHP do XAMPP. As configurações estão em `.vscode/settings.json`.

### PHP
- **Versão mínima**: 8.0.0
- **Timezone**: America/Sao_Paulo
- **Locale**: pt_BR
- **Debug**: Habilitado em desenvolvimento

## 🌐 Endpoints disponíveis

### URLs Base (configuráveis durante instalação)
- **`BASE_URL`** - URL raiz do sistema (ex: `http://localhost/LJ-OS`)
- **`APP_URL`** - URL da aplicação principal (`BASE_URL/app`)
- **`API_URL`** - URL da API (`BASE_URL/app/api`)
- **`ASSETS_URL`** - URL dos assets (`BASE_URL/app/assets`)

### Endpoints principais
- **`/`** - Página principal (redireciona para login ou dashboard)
- **`/install_web.php`** - Instalador web do sistema
- **`/clean_and_reinstall.php`** - Script de limpeza e reinstalação
- **`/app/`** - Página principal da aplicação
- **`/app/login.php`** - Página de login do sistema
- **`/app/dashboard.php`** - Dashboard principal
- **`/app/api/auth.php`** - API de autenticação (login, logout, refresh)
- **`/app/api/clientes.php`** - API de gestão de clientes
- **`/app/api/status`** - Status da API em JSON
- **`/app/components/theme-settings.php`** - Configurações de tema e idioma

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

### Teste das URLs
Para verificar se as URLs estão configuradas corretamente:

```bash
# Via navegador
http://localhost/LJ-OS/test_urls.php

# Via linha de comando
php test_urls.php
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
