#!/bin/bash

# Script menu principal para automação Git
# Uso: ./git-menu.sh

# Cores para melhor visualização
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Função para exibir banner
show_banner() {
    clear
    echo -e "${CYAN}"
    echo "╔════════════════════════════════════════╗"
    echo "║        GIT AUTOMATION TOOLKIT          ║"
    echo "║          Menu Principal v1.0           ║"
    echo "╚════════════════════════════════════════╝"
    echo -e "${NC}"
}

# Função para verificar se está em repositório Git
check_git_repo() {
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        echo -e "${RED}❌ Erro: Não está em um repositório Git!${NC}"
        echo ""
        echo "Opções:"
        echo "1) Inicializar novo repositório aqui"
        echo "2) Clonar repositório existente"
        echo "3) Sair"
        echo ""
        read -p "Escolha uma opção: " init_choice
        
        case $init_choice in
            1)
                git init
                echo -e "${GREEN}✅ Repositório Git inicializado!${NC}"
                echo ""
                read -p "Pressione Enter para continuar..."
                ;;
            2)
                echo ""
                read -p "URL do repositório: " repo_url
                if [ ! -z "$repo_url" ]; then
                    git clone $repo_url
                    echo -e "${GREEN}✅ Repositório clonado!${NC}"
                    echo "Entre no diretório clonado e execute o script novamente."
                else
                    echo -e "${RED}URL vazia!${NC}"
                fi
                exit 0
                ;;
            3)
                exit 0
                ;;
        esac
    fi
}

# Função para mostrar status do repositório
show_status() {
    echo -e "${BLUE}📊 Status do Repositório:${NC}"
    echo -e "📁 Diretório: ${YELLOW}$(basename `git rev-parse --show-toplevel`)${NC}"
    echo -e "🌿 Branch atual: ${GREEN}$(git branch --show-current)${NC}"
    
    # Verifica se há mudanças
    if [ -n "$(git status --porcelain)" ]; then
        echo -e "📝 Status: ${YELLOW}Mudanças pendentes${NC}"
    else
        echo -e "📝 Status: ${GREEN}Limpo${NC}"
    fi
    
    # Verifica sincronização com remoto
    if git remote -v | grep -q origin; then
        git fetch origin &>/dev/null
        LOCAL=$(git rev-parse @)
        REMOTE=$(git rev-parse @{u} 2>/dev/null)
        
        if [ ! -z "$REMOTE" ]; then
            if [ "$LOCAL" = "$REMOTE" ]; then
                echo -e "🔄 Sincronização: ${GREEN}Atualizado${NC}"
            else
                BASE=$(git merge-base @ @{u})
                if [ "$LOCAL" = "$BASE" ]; then
                    echo -e "🔄 Sincronização: ${YELLOW}Atualizações disponíveis${NC}"
                elif [ "$REMOTE" = "$BASE" ]; then
                    echo -e "🔄 Sincronização: ${YELLOW}Commits locais não enviados${NC}"
                else
                    echo -e "🔄 Sincronização: ${RED}Divergente${NC}"
                fi
            fi
        else
            echo -e "🔄 Sincronização: ${YELLOW}Sem upstream${NC}"
        fi
    else
        echo -e "🔄 Remoto: ${YELLOW}Não configurado${NC}"
    fi
}

# Função para executar scripts
run_script() {
    script_name=$1
    shift
    
    if [ -f "./$script_name" ]; then
        if [ ! -x "./$script_name" ]; then
            chmod +x "./$script_name"
        fi
        ./$script_name "$@"
    else
        echo -e "${RED}❌ Script $script_name não encontrado!${NC}"
        echo "Certifique-se de que todos os scripts estão no mesmo diretório."
    fi
}

# Função para configuração inicial
initial_setup() {
    echo -e "${CYAN}=== CONFIGURAÇÃO INICIAL ===${NC}"
    echo ""
    
    # Torna todos os scripts executáveis
    echo "🔧 Tornando scripts executáveis..."
    chmod +x *.sh 2>/dev/null
    
    # Configuração do Git
    echo ""
    echo "📝 Configuração do Git:"
    
    current_name=$(git config user.name)
    current_email=$(git config user.email)
    
    if [ -z "$current_name" ]; then
        read -p "Seu nome: " user_name
        git config --global user.name "$user_name"
    else
        echo -e "Nome: ${GREEN}$current_name${NC}"
    fi
    
    if [ -z "$current_email" ]; then
        read -p "Seu email: " user_email
        git config --global user.email "$user_email"
    else
        echo -e "Email: ${GREEN}$current_email${NC}"
    fi
    
    echo ""
    echo -e "${GREEN}✅ Configuração concluída!${NC}"
}

# Função para mostrar logs
show_logs() {
    echo -e "${CYAN}=== HISTÓRICO DE COMMITS ===${NC}"
    echo ""
    echo "1) Últimos 10 commits"
    echo "2) Commits de hoje"
    echo "3) Commits da última semana"
    echo "4) Buscar por autor"
    echo "5) Buscar por mensagem"
    echo "6) Ver gráfico de branches"
    echo ""
    read -p "Escolha uma opção: " log_choice
    
    case $log_choice in
        1)
            git log --oneline -10
            ;;
        2)
            git log --oneline --since="midnight"
            ;;
        3)
            git log --oneline --since="1 week ago"
            ;;
        4)
            read -p "Nome do autor: " author_name
            git log --oneline --author="$author_name"
            ;;
        5)
            read -p "Texto a buscar: " search_text
            git log --oneline --grep="$search_text"
            ;;
        6)
            git log --graph --oneline --all --decorate
            ;;
        *)
            echo -e "${RED}Opção inválida!${NC}"
            ;;
    esac
}

# Função principal do menu
main_menu() {
    while true; do
        show_banner
        check_git_repo
        echo ""
        show_status
        echo ""
        echo -e "${PURPLE}═══════════════════════════════════════${NC}"
        echo ""
        echo "📋 OPERAÇÕES BÁSICAS:"
        echo "  1) 💾 Commit rápido"
        echo "  2) 📥 Pull (atualizar do remoto)"
        echo "  3) 📤 Push (enviar para remoto)"
        echo "  4) 🔄 Sync (sincronização completa)"
        echo ""
        echo "🌿 GERENCIAMENTO:"
        echo "  5) 📑 Gerenciar branches"
        echo "  6) 📊 Ver status detalhado"
        echo "  7) 📜 Ver histórico/logs"
        echo ""
        echo "⚙️  CONFIGURAÇÕES:"
        echo "  8) 🔧 Configuração inicial"
        echo "  9) 🔍 Executar comando Git personalizado"
        echo ""
        echo "  0) 🚪 Sair"
        echo ""
        echo -e "${PURPLE}═══════════════════════════════════════${NC}"
        echo ""
        read -p "Escolha uma opção: " choice
        
        case $choice in
            1)
                echo ""
                read -p "Mensagem do commit (Enter para padrão): " commit_msg
                run_script "git-commit.sh" "$commit_msg"
                ;;
            2)
                run_script "git-pull.sh"
                ;;
            3)
                run_script "git-push.sh"
                ;;
            4)
                echo ""
                read -p "Mensagem do commit (Enter para padrão): " commit_msg
                run_script "git-sync.sh" "$commit_msg"
                ;;
            5)
                run_script "git-branch.sh"
                ;;
            6)
                echo ""
                echo -e "${CYAN}=== STATUS DETALHADO ===${NC}"
                echo ""
                git status
                echo ""
                echo -e "${CYAN}=== MUDANÇAS ===${NC}"
                git diff --stat
                ;;
            7)
                show_logs
                ;;
            8)
                initial_setup
                ;;
            9)
                echo ""
                echo "Digite o comando Git (sem 'git' no início):"
                read -p "git " git_command
                if [ ! -z "$git_command" ]; then
                    echo ""
                    git $git_command
                fi
                ;;
            0)
                echo ""
                echo -e "${GREEN}👋 Até logo!${NC}"
                exit 0
                ;;
            *)
                echo -e "${RED}❌ Opção inválida!${NC}"
                ;;
        esac
        
        echo ""
        read -p "Pressione Enter para continuar..."
    done
}

# Executa o menu principal
main_menu
