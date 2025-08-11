#!/bin/bash

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
WHITE='\033[1;37m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "========================================"
echo "    GIT TOOLS - INSTALADOR PORTATIL"
echo "========================================"
echo -e "${NC}"

# Verificar se o Git está instalado
if ! command -v git &> /dev/null; then
    echo -e "${RED}❌ ERRO: Git não está instalado!${NC}"
    echo "Por favor, instale o Git primeiro: https://git-scm.com/"
    exit 1
fi

# Verificar se o arquivo ZIP existe
if [ ! -f "git-tools-portable.zip" ]; then
    echo -e "${RED}❌ ERRO: Arquivo 'git-tools-portable.zip' não encontrado!${NC}"
    echo "Certifique-se de que está na mesma pasta do instalador."
    exit 1
fi

# Verificar se unzip está disponível
if ! command -v unzip &> /dev/null; then
    echo -e "${YELLOW}⚠️  AVISO: 'unzip' não encontrado. Tentando instalar...${NC}"
    if command -v apt-get &> /dev/null; then
        sudo apt-get update && sudo apt-get install -y unzip
    elif command -v yum &> /dev/null; then
        sudo yum install -y unzip
    elif command -v brew &> /dev/null; then
        brew install unzip
    else
        echo -e "${RED}❌ ERRO: Não foi possível instalar 'unzip'. Instale manualmente.${NC}"
        exit 1
    fi
fi

# Criar pasta de instalação
INSTALL_DIR="$HOME/.git-tools"
mkdir -p "$INSTALL_DIR"

echo -e "${BLUE}📁 Instalando em: $INSTALL_DIR${NC}"
echo

# Extrair arquivos
echo -e "${YELLOW}🔄 Extraindo arquivos...${NC}"
unzip -o "git-tools-portable.zip" -d "$INSTALL_DIR"

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ ERRO: Falha ao extrair arquivos!${NC}"
    exit 1
fi

# Tornar scripts executáveis
echo -e "${YELLOW}🔧 Configurando permissões...${NC}"
chmod +x "$INSTALL_DIR"/*.sh

# Criar arquivo de aliases
ALIASES_FILE="$HOME/.git-tools-aliases"
cat > "$ALIASES_FILE" << 'EOF'
# Aliases para Git Tools
export GIT_TOOLS_DIR="$HOME/.git-tools"

# Aliases principais
alias githelp="$GIT_TOOLS_DIR/git-help.sh"
alias gitmenu="$GIT_TOOLS_DIR/git-menu.sh"
alias gitinit="$GIT_TOOLS_DIR/git-init.sh"
alias gitcommit="$GIT_TOOLS_DIR/git-commit.sh"
alias gitpush="$GIT_TOOLS_DIR/git-push.sh"
alias gitpull="$GIT_TOOLS_DIR/git-pull.sh"
alias gitsync="$GIT_TOOLS_DIR/git-sync.sh"
alias gitbranch="$GIT_TOOLS_DIR/git-branch.sh"
alias gitstash="$GIT_TOOLS_DIR/git-stash.sh"
alias gitmerge="$GIT_TOOLS_DIR/git-merge.sh"
alias gittag="$GIT_TOOLS_DIR/git-tag.sh"
alias gitlog="$GIT_TOOLS_DIR/git-log.sh"

# Aliases curtos
alias help="githelp"
alias ghelp="githelp"
alias menu="gitmenu"
alias init="gitinit"
alias commit="gitcommit"
alias push="gitpush"
alias pull="gitpull"
alias sync="gitsync"
alias branch="gitbranch"
alias stash="gitstash"
alias merge="gitmerge"
alias tag="gittag"
alias log="gitlog"

# Função para compatibilidade
githelp() { "$GIT_TOOLS_DIR/git-help.sh" "$@"; }
EOF

# Detectar shell e configurar
SHELL_CONFIG=""
if [ -n "$ZSH_VERSION" ]; then
    SHELL_CONFIG="$HOME/.zshrc"
elif [ -n "$BASH_VERSION" ]; then
    if [ -f "$HOME/.bashrc" ]; then
        SHELL_CONFIG="$HOME/.bashrc"
    elif [ -f "$HOME/.bash_profile" ]; then
        SHELL_CONFIG="$HOME/.bash_profile"
    fi
else
    SHELL_CONFIG="$HOME/.profile"
fi

# Adicionar source ao arquivo de configuração do shell
if [ -n "$SHELL_CONFIG" ]; then
    echo -e "${YELLOW}🔧 Configurando $SHELL_CONFIG...${NC}"
    
    # Verificar se já está configurado
    if ! grep -q "source \"$ALIASES_FILE\"" "$SHELL_CONFIG" 2>/dev/null; then
        echo "" >> "$SHELL_CONFIG"
        echo "# Git Tools Configuration" >> "$SHELL_CONFIG"
        echo "source \"$ALIASES_FILE\"" >> "$SHELL_CONFIG"
    fi
fi

# Ativar aliases imediatamente
echo -e "${YELLOW}🚀 Ativando aliases...${NC}"
source "$ALIASES_FILE"

# Criar script de desinstalação
UNINSTALL_SCRIPT="$INSTALL_DIR/uninstall.sh"
cat > "$UNINSTALL_SCRIPT" << EOF
#!/bin/bash
echo "Desinstalando Git Tools..."

# Remover pasta de instalação
rm -rf "$INSTALL_DIR"

# Remover arquivo de aliases
rm -f "$ALIASES_FILE"

# Remover configuração do shell
if [ -f "$SHELL_CONFIG" ]; then
    sed -i '/source ".*\.git-tools-aliases"/d' "$SHELL_CONFIG"
    sed -i '/# Git Tools Configuration/d' "$SHELL_CONFIG"
fi

echo "Git Tools desinstalado com sucesso!"
EOF

chmod +x "$UNINSTALL_SCRIPT"

echo -e "${GREEN}✅ Instalação concluída com sucesso!${NC}"
echo
echo -e "${BLUE}🎯 Para usar os comandos:${NC}"
echo
echo -e "${WHITE}💡 Comandos disponíveis:${NC}"
echo -e "${WHITE}   githelp${NC} - Ver todos os comandos"
echo -e "${WHITE}   gitmenu${NC} - Menu principal"
echo -e "${WHITE}   gitinit${NC} - Inicializar repositório"
echo -e "${WHITE}   gitcommit${NC} - Fazer commit"
echo -e "${WHITE}   gitpush${NC} - Enviar alterações"
echo -e "${WHITE}   gitpull${NC} - Baixar alterações"
echo -e "${WHITE}   gitsync${NC} - Sincronizar"
echo
echo -e "${BLUE}🗂️  Arquivos instalados em: $INSTALL_DIR${NC}"
echo -e "${BLUE}📝 Comando para ativar: source \"$ALIASES_FILE\"${NC}"
echo
echo -e "${YELLOW}💡 DICA: Os aliases já estão ativos neste terminal!${NC}"
echo -e "${YELLOW}💡 DICA: Para ativar manualmente: source \"$ALIASES_FILE\"${NC}"
echo
echo -e "${GREEN}🎉 Teste agora: githelp${NC}"
