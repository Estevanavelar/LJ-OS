#!/bin/bash

# Script para automatizar operações de stash no Git
# Uso: ./git-stash.sh

echo "=== GIT STASH MANAGER ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Função para listar stashes
list_stashes() {
    echo ""
    echo "📦 Stashes disponíveis:"
    if git stash list | grep -q .; then
        git stash list
    else
        echo "Nenhum stash encontrado."
    fi
}

# Função para criar stash
create_stash() {
    echo ""
    echo "📝 Criando novo stash..."
    
    # Verifica se há mudanças para stash
    if [ -z "$(git status --porcelain)" ]; then
        echo "✅ Nenhuma mudança para fazer stash!"
        return
    fi
    
    echo ""
    echo "Opções de stash:"
    echo "1) Stash normal (inclui arquivos não rastreados)"
    echo "2) Stash apenas arquivos rastreados"
    echo "3) Stash com mensagem personalizada"
    echo "4) Stash com patch (interativo)"
    echo ""
    read -p "Escolha uma opção (1-4): " stash_choice
    
    case $stash_choice in
        1)
            git stash push -u
            ;;
        2)
            git stash push
            ;;
        3)
            read -p "Mensagem do stash: " stash_msg
            git stash push -u -m "$stash_msg"
            ;;
        4)
            git stash push -p
            ;;
        *)
            echo "❌ Opção inválida!"
            return
            ;;
    esac
    
    if [ $? -eq 0 ]; then
        echo "✅ Stash criado com sucesso!"
        list_stashes
    else
        echo "❌ Erro ao criar stash!"
    fi
}

# Função para aplicar stash
apply_stash() {
    list_stashes
    
    if ! git stash list | grep -q .; then
        echo "Nenhum stash para aplicar."
        return
    fi
    
    echo ""
    read -p "Índice do stash (0, 1, 2...): " stash_index
    
    if [ -z "$stash_index" ]; then
        stash_index=0
    fi
    
    echo ""
    echo "Opções de aplicação:"
    echo "1) Aplicar e manter stash"
    echo "2) Aplicar e remover stash"
    echo "3) Aplicar e remover todos os stashes"
    echo ""
    read -p "Escolha uma opção (1-3): " apply_choice
    
    case $apply_choice in
        1)
            git stash apply stash@{$stash_index}
            ;;
        2)
            git stash pop stash@{$stash_index}
            ;;
        3)
            git stash clear
            echo "✅ Todos os stashes foram removidos!"
            return
            ;;
        *)
            echo "❌ Opção inválida!"
            return
            ;;
    esac
    
    if [ $? -eq 0 ]; then
        echo "✅ Stash aplicado com sucesso!"
    else
        echo "❌ Erro ao aplicar stash!"
    fi
}

# Função para visualizar stash
view_stash() {
    list_stashes
    
    if ! git stash list | grep -q .; then
        return
    fi
    
    echo ""
    read -p "Índice do stash para visualizar: " stash_index
    
    if [ -z "$stash_index" ]; then
        stash_index=0
    fi
    
    echo ""
    echo "📋 Conteúdo do stash $stash_index:"
    git stash show -p stash@{$stash_index}
}

# Função para deletar stash
delete_stash() {
    list_stashes
    
    if ! git stash list | grep -q .; then
        return
    fi
    
    echo ""
    echo "Opções de exclusão:"
    echo "1) Deletar stash específico"
    echo "2) Deletar todos os stashes"
    echo ""
    read -p "Escolha uma opção (1-2): " delete_choice
    
    case $delete_choice in
        1)
            read -p "Índice do stash para deletar: " stash_index
            if [ ! -z "$stash_index" ]; then
                git stash drop stash@{$stash_index}
                if [ $? -eq 0 ]; then
                    echo "✅ Stash $stash_index deletado!"
                fi
            fi
            ;;
        2)
            read -p "⚠️  Confirma deletar TODOS os stashes? (s/n): " confirm
            if [ "$confirm" = "s" ] || [ "$confirm" = "S" ]; then
                git stash clear
                echo "✅ Todos os stashes foram deletados!"
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para criar branch a partir de stash
stash_branch() {
    list_stashes
    
    if ! git stash list | grep -q .; then
        echo "Nenhum stash para criar branch."
        return
    fi
    
    echo ""
    read -p "Índice do stash: " stash_index
    read -p "Nome do novo branch: " branch_name
    
    if [ -z "$stash_index" ] || [ -z "$branch_name" ]; then
        echo "❌ Índice e nome do branch são obrigatórios!"
        return
    fi
    
    # Verifica se o branch já existe
    if git show-ref --verify --quiet refs/heads/$branch_name; then
        echo "❌ Branch '$branch_name' já existe!"
        return
    fi
    
    echo ""
    echo "🌿 Criando branch '$branch_name' a partir do stash $stash_index..."
    git stash branch $branch_name stash@{$stash_index}
    
    if [ $? -eq 0 ]; then
        echo "✅ Branch criado e stash aplicado!"
    else
        echo "❌ Erro ao criar branch do stash!"
    fi
}

# Menu principal
while true; do
    echo ""
    echo "=== MENU STASH ==="
    echo "🌿 Branch atual: $(git branch --show-current)"
    echo ""
    echo "1) 📋 Listar stashes"
    echo "2) 📦 Criar novo stash"
    echo "3) 🔄 Aplicar stash"
    echo "4) 👁️  Visualizar stash"
    echo "5) 🗑️  Deletar stash"
    echo "6) 🌿 Criar branch de stash"
    echo "0) 🚪 Sair"
    echo ""
    read -p "Escolha uma opção: " choice
    
    case $choice in
        1)
            list_stashes
            ;;
        2)
            create_stash
            ;;
        3)
            apply_stash
            ;;
        4)
            view_stash
            ;;
        5)
            delete_stash
            ;;
        6)
            stash_branch
            ;;
        0)
            echo ""
            echo "👋 Até logo!"
            exit 0
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
    
    echo ""
    read -p "Pressione Enter para continuar..."
done
