#!/bin/bash

# Git Tools Portátil - Funciona em qualquer pasta
# Uso: ./git-tools-portable.sh [comando] [opções]

# Cores para melhor visualização
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Diretório onde está este script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Função para exibir banner
show_banner() {
    echo -e "${CYAN}"
    echo "╔════════════════════════════════════════╗"
    echo "║        GIT TOOLS PORTÁTIL             ║"
    echo "║         Funciona em qualquer pasta     ║"
    echo "╚════════════════════════════════════════╝"
    echo -e "${NC}"
}

# Função para mostrar ajuda
show_help() {
    show_banner
    echo -e "${BLUE}📋 COMANDOS DISPONÍVEIS:${NC}"
    echo ""
    echo -e "${CYAN}🎯 OPERAÇÕES BÁSICAS:${NC}"
    echo "  menu     - Menu interativo principal"
    echo "  commit   - Commit rápido"
    echo "  comitar  - Commit alternativo"
    echo "  pull     - Atualizar do remoto"
    echo "  push     - Enviar para remoto"
    echo "  sync     - Sincronização completa"
    echo ""
    echo -e "${CYAN}🌿 GERENCIAMENTO:${NC}"
    echo "  branch   - Gerenciar branches"
    echo "  stash    - Gerenciar stashes"
    echo "  merge    - Gerenciar merges"
    echo "  tag      - Gerenciar tags"
    echo "  log      - Ver histórico/logs"
    echo ""
    echo -e "${CYAN}💡 EXEMPLOS DE USO:${NC}"
    echo "  ./git-tools-portable.sh menu"
    echo "  ./git-tools-portable.sh commit 'feat: nova funcionalidade'"
    echo "  ./git-tools-portable.sh sync 'atualizações do dia'"
    echo "  ./git-tools-portable.sh branch"
    echo ""
    echo -e "${YELLOW}💡 DICA: Execute sem parâmetros para ver este menu!${NC}"
}

# Função para executar script
run_script() {
    local script_name="$1"
    local script_path="$SCRIPT_DIR/$script_name.sh"
    
    if [ -f "$script_path" ]; then
        if [ ! -x "$script_path" ]; then
            chmod +x "$script_path"
        fi
        
        # Passa todos os argumentos para o script
        shift
        "$script_path" "$@"
    else
        echo -e "${RED}❌ Script $script_name.sh não encontrado!${NC}"
        echo "Certifique-se de que todos os scripts estão no diretório: $SCRIPT_DIR"
        exit 1
    fi
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

# Função para menu interativo
show_menu() {
    while true; do
        clear
        show_banner
        check_git_repo
        
        echo ""
        echo -e "${BLUE}📊 Status do Repositório:${NC}"
        echo -e "📁 Diretório: ${YELLOW}$(basename `git rev-parse --show-toplevel`)${NC}"
        echo -e "🌿 Branch atual: ${GREEN}$(git branch --show-current)${NC}"
        
        # Verifica se há mudanças
        if [ -n "$(git status --porcelain)" ]; then
            echo -e "📝 Status: ${YELLOW}Mudanças pendentes${NC}"
        else
            echo -e "📝 Status: ${GREEN}Limpo${NC}"
        fi
        
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
        echo "  8) 📦 Gerenciar stashes"
        echo "  9) 🔀 Gerenciar merges"
        echo " 10) 🏷️  Gerenciar tags"
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
                run_script "git-commit" "$commit_msg"
                ;;
            2)
                run_script "git-pull"
                ;;
            3)
                run_script "git-push"
                ;;
            4)
                echo ""
                read -p "Mensagem do commit (Enter para padrão): " commit_msg
                run_script "git-sync" "$commit_msg"
                ;;
            5)
                run_script "git-branch"
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
                run_script "git-log"
                ;;
            8)
                run_script "git-stash"
                ;;
            9)
                run_script "git-merge"
                ;;
            10)
                run_script "git-tag"
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

# Função principal
main() {
    case "$1" in
        "menu"|"")
            show_menu
            ;;
        "commit")
            run_script "git-commit" "${@:2}"
            ;;
        "comitar")
            run_script "comitar" "${@:2}"
            ;;
        "pull")
            run_script "git-pull" "${@:2}"
            ;;
        "push")
            run_script "git-push" "${@:2}"
            ;;
        "sync")
            run_script "git-sync" "${@:2}"
            ;;
        "branch")
            run_script "git-branch" "${@:2}"
            ;;
        "stash")
            run_script "git-stash" "${@:2}"
            ;;
        "merge")
            run_script "git-merge" "${@:2}"
            ;;
        "tag")
            run_script "git-tag" "${@:2}"
            ;;
        "log")
            run_script "git-log" "${@:2}"
            ;;
        "help"|"-h"|"--help")
            show_help
            ;;
        *)
            echo -e "${RED}❌ Comando '$1' não reconhecido!${NC}"
            echo ""
            show_help
            exit 1
            ;;
    esac
}

# Executa função principal com todos os argumentos
main "$@"
