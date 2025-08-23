# 🚀 LJ-OS Sistema

Sistema PHP desenvolvido com XAMPP e configurado para desenvolvimento web moderno.

## 📋 Pré-requisitos

- **XAMPP** instalado e configurado
- **PHP 8.0+** (incluído no XAMPP)
- **Composer** (gerenciador de dependências PHP)
- **Git** para controle de versão

## 🛠️ Instalação

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
- Acesse: `http://localhost/LJ-OS/public/`

## 📁 Estrutura do projeto

```
LJ-OS/
├── config/          # Configurações da aplicação
├── logs/            # Arquivos de log
├── public/          # Arquivos públicos (web root)
├── src/             # Código fonte da aplicação
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

- **`/`** - Página inicial do sistema
- **`/api/status`** - Status da API em JSON

## 🧪 Testes

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

- Sessões configuradas com `httponly`
- Validação de entrada de dados
- Sanitização de saída
- Configurações de segurança do PHP

## 📞 Suporte

Para dúvidas ou problemas:
- Abra uma issue no repositório
- Entre em contato com a equipe de desenvolvimento

## 📄 Licença

Este projeto está licenciado sob a licença MIT.

---

**Desenvolvido com ❤️ pela equipe LJ-OS**
