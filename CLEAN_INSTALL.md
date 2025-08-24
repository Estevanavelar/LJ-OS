# 🧹 LJ-OS - Limpeza e Reinstalação

Este documento explica como limpar completamente o sistema LJ-OS e reinstalá-lo do zero.

## ⚠️ **ATENÇÃO - OPERAÇÃO IRREVERSÍVEL**

**Esta operação remove TODOS os dados do sistema permanentemente!**

## 🎯 **Quando usar?**

- ✅ Sistema corrompido ou com problemas
- ✅ Mudança de configurações que não podem ser revertidas
- ✅ Testes de instalação
- ✅ Limpeza para desenvolvimento
- ✅ Reset completo do sistema

## 🚫 **O que será REMOVIDO?**

### **Arquivos do Sistema:**
- `.installed` - Marcador de instalação
- `database/lj_os.db` - Banco de dados completo
- `config/urls.php` - Configurações de URLs
- `config/database.php` - Configurações do banco

### **Diretórios Limpos:**
- `logs/*` - Todos os arquivos de log
- `cache/*` - Todos os arquivos de cache
- `tmp/*` - Todos os arquivos temporários
- `uploads/*` - Todos os arquivos enviados

### **Dados Perdidos:**
- ❌ Todos os usuários
- ❌ Todos os clientes
- ❌ Todos os veículos
- ❌ Todos os serviços
- ❌ Todos os agendamentos
- ❌ Todas as ordens de serviço
- ❌ Todos os produtos
- ❌ Todo o histórico
- ❌ Todas as configurações

## 🔄 **Processo de Limpeza**

### **Passo 1: Acessar o Script**
```
http://localhost/LJ-OS/clean_and_reinstall.php
```

### **Passo 2: Confirmação**
- Leia atentamente todos os avisos
- Confirme que entendeu as consequências
- Clique em "SIM, LIMPAR TUDO E REINSTALAR"

### **Passo 3: Processamento**
- O sistema será limpo automaticamente
- Você verá uma lista de arquivos removidos
- Aguarde a conclusão da operação

### **Passo 4: Reinstalação**
- Clique em "Ir para Instalação"
- Configure todos os parâmetros novamente
- Complete a instalação

## 📋 **Checklist Pré-Limpeza**

Antes de executar a limpeza, certifique-se de:

- [ ] **Fazer backup** de dados importantes
- [ ] **Anotar configurações** que precisará reconfigurar
- [ ] **Verificar permissões** de escrita no diretório
- [ ] **Fechar todas as sessões** ativas do sistema
- [ ] **Parar serviços** que possam estar usando o banco

## 🔧 **Configurações a Reconfigurar**

Após a limpeza, você precisará configurar novamente:

### **Banco de Dados:**
- Host do servidor
- Nome do banco
- Usuário de conexão
- Senha de acesso

### **Usuário Administrador:**
- E-mail de acesso
- Senha de acesso

### **Sistema:**
- URL raiz do projeto
- Configurações de idioma
- Configurações de tema

## 🚨 **Problemas Comuns**

### **Erro de Permissão**
```
Erro: Não foi possível criar o diretório
```
**Solução:** Verificar permissões de escrita no diretório do projeto

### **Arquivo não encontrado**
```
Erro: Arquivo .installed não encontrado
```
**Solução:** O sistema já está limpo, pode prosseguir para instalação

### **Banco em uso**
```
Erro: database is locked
```
**Solução:** Fechar todas as conexões com o banco antes da limpeza

## 📞 **Suporte**

Se encontrar problemas durante a limpeza:

1. **Verifique os logs** do servidor web
2. **Confirme permissões** de escrita
3. **Reinicie o servidor** web se necessário
4. **Consulte a documentação** de instalação

## 🔄 **Alternativas à Limpeza Completa**

### **Reset Parcial (Recomendado)**
- Manter estrutura do banco
- Limpar apenas dados específicos
- Usar scripts de manutenção

### **Backup e Restore**
- Fazer backup antes de mudanças
- Restaurar versão anterior se necessário
- Manter histórico de configurações

### **Instalação em Novo Diretório**
- Criar nova instância do sistema
- Testar mudanças antes de aplicar
- Migrar dados gradualmente

## 📚 **Documentação Relacionada**

- [Instalação do Sistema](README.md#instalação)
- [Configuração do Banco](README.md#configurações)
- [Estrutura do Projeto](README.md#estrutura-do-projeto)
- [Troubleshooting](README.md#problemas-comuns)

---

**⚠️ LEMBRE-SE: Esta operação é IRREVERSÍVEL! Use com responsabilidade.**
