#!/bin/bash

# Script para gerenciar branches no Git
# Uso: ./git-branch.sh

echo "=== GIT BRANCH MANAGER ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Função para listar branches
list_branches() {
    echo ""
    echo "📋 Branches locais:"
    git branch -v
    echo ""
    echo "📋 Branches remotos:"
    git branch -r
}

# Função para criar novo branch
create_branch() {
    echo ""
    read -p "Nome do novo branch: " branch_name
    
    if [ -z "$branch_name" ]; then
        echo "❌ Nome do branch não pode ser vazio!"
        return
    fi
    
    # Verifica se o branch já existe
    if git show-ref --verify --quiet refs/heads/$branch_name; then
        echo "❌ Branch '$branch_name' já existe!"
        return
    fi
    
    echo ""
    echo "Criar branch a partir de:"
    echo "1) Branch atual ($(git branch --show-current))"
    echo "2) Branch main/master"
    echo "3) Outro branch"
    echo "4) Commit específico"
    echo ""
    read -p "Escolha (1-4): " from_choice
    
    case $from_choice in
        1)
            git checkout -b $branch_name
            ;;
        2)
            # Detecta se é main ou master
            if git show-ref --verify --quiet refs/heads/main; then
                git checkout -b $branch_name main
            elif git show-ref --verify --quiet refs/heads/master; then
                git checkout -b $branch_name master
            else
                echo "❌ Branch main/master não encontrado!"
                return
            fi
            ;;
        3)
            echo ""
            echo "Branches disponíveis:"
            git branch -a
            echo ""
            read -p "Nome do branch de origem: " source_branch
            git checkout -b $branch_name $source_branch
            ;;
        4)
            echo ""
            echo "Commits recentes:"
            git log --oneline -10
            echo ""
            read -p "Hash do commit: " commit_hash
            git checkout -b $branch_name $commit_hash
            ;;
        *)
            echo "❌ Opção inválida!"
            return
            ;;
    esac
    
    if [ $? -eq 0 ]; then
        echo "✅ Branch '$branch_name' criado e ativado!"
        echo ""
        read -p "Deseja fazer push do novo branch? (s/n): " push_branch
        if [ "$push_branch" = "s" ] || [ "$push_branch" = "S" ]; then
            git push -u origin $branch_name
            if [ $? -eq 0 ]; then
                echo "✅ Branch enviado para o remoto!"
            fi
        fi
    else
        echo "❌ Erro ao criar branch!"
    fi
}

# Função para trocar de branch
switch_branch() {
    echo ""
    echo "📋 Branches disponíveis:"
    git branch -a
    echo ""
    
    # Verifica mudanças não commitadas
    if ! git diff-index --quiet HEAD -- 2>/dev/null; then
        echo "⚠️  Existem mudanças não commitadas!"
        echo ""
        echo "1) Fazer commit antes de trocar"
        echo "2) Fazer stash das mudanças"
        echo "3) Descartar mudanças"
        echo "4) Cancelar"
        echo ""
        read -p "Escolha (1-4): " change_choice
        
        case $change_choice in
            1)
                read -p "Mensagem do commit: " commit_msg
                git add -A
                git commit -m "$commit_msg"
                ;;
            2)
                git stash save "Stash antes de trocar de branch - $(date '+%d/%m/%Y %H:%M:%S')"
                echo "✅ Stash criado!"
                ;;
            3)
                read -p "⚠️  Tem certeza que deseja descartar as mudanças? (s/n): " confirm_discard
                if [ "$confirm_discard" = "s" ] || [ "$confirm_discard" = "S" ]; then
                    git reset --hard HEAD
                    echo "✅ Mudanças descartadas!"
                else
                    return
                fi
                ;;
            4)
                return
                ;;
        esac
    fi
    
    echo ""
    read -p "Nome do branch: " target_branch
    
    if [ -z "$target_branch" ]; then
        echo "❌ Nome do branch não pode ser vazio!"
        return
    fi
    
    # Remove prefixo origin/ se fornecido
    target_branch=${target_branch#origin/}
    
    # Verifica se é um branch remoto
    if git show-ref --verify --quiet refs/remotes/origin/$target_branch; then
        if ! git show-ref --verify --quiet refs/heads/$target_branch; then
            echo "🔄 Criando branch local a partir do remoto..."
            git checkout -b $target_branch origin/$target_branch
        else
            git checkout $target_branch
        fi
    else
        git checkout $target_branch
    fi
    
    if [ $? -eq 0 ]; then
        echo "✅ Mudou para o branch '$target_branch'!"
    else
        echo "❌ Erro ao trocar de branch!"
    fi
}

# Função para deletar branch
delete_branch() {
    echo ""
    echo "⚠️  DELETAR BRANCH"
    echo ""
    echo "📋 Branches locais (exceto o atual):"
    git branch | grep -v "^\*"
    echo ""
    
    read -p "Nome do branch a deletar: " branch_to_delete
    
    if [ -z "$branch_to_delete" ]; then
        echo "❌ Nome do branch não pode ser vazio!"
        return
    fi
    
    # Verifica se não é o branch atual
    current_branch=$(git branch --show-current)
    if [ "$branch_to_delete" = "$current_branch" ]; then
        echo "❌ Não é possível deletar o branch atual!"
        return
    fi
    
    # Verifica se o branch existe
    if ! git show-ref --verify --quiet refs/heads/$branch_to_delete; then
        echo "❌ Branch '$branch_to_delete' não existe!"
        return
    fi
    
    # Verifica se foi feito merge
    if git branch --merged | grep -q "^\s*$branch_to_delete$"; then
        echo "✅ Branch já foi mergeado."
    else
        echo "⚠️  Branch NÃO foi mergeado!"
    fi
    
    echo ""
    read -p "Confirma a exclusão do branch '$branch_to_delete'? (s/n): " confirm
    
    if [ "$confirm" = "s" ] || [ "$confirm" = "S" ]; then
        # Deleta localmente
        git branch -D $branch_to_delete
        
        if [ $? -eq 0 ]; then
            echo "✅ Branch local deletado!"
            
            # Verifica se existe no remoto
            if git show-ref --verify --quiet refs/remotes/origin/$branch_to_delete; then
                echo ""
                read -p "Deletar também do remoto? (s/n): " delete_remote
                
                if [ "$delete_remote" = "s" ] || [ "$delete_remote" = "S" ]; then
                    git push origin --delete $branch_to_delete
                    
                    if [ $? -eq 0 ]; then
                        echo "✅ Branch remoto também deletado!"
                    else
                        echo "❌ Erro ao deletar branch remoto!"
                    fi
                fi
            fi
        else
            echo "❌ Erro ao deletar branch!"
        fi
    else
        echo "❌ Exclusão cancelada!"
    fi
}

# Função para fazer merge
merge_branch() {
    echo ""
    echo "🔀 MERGE DE BRANCH"
    echo ""
    echo "Branch atual: $(git branch --show-current)"
    echo ""
    echo "📋 Branches disponíveis para merge:"
    git branch -a | grep -v "^\*"
    echo ""
    
    read -p "Branch a ser mergeado: " source_branch
    
    if [ -z "$source_branch" ]; then
        echo "❌ Nome do branch não pode ser vazio!"
        return
    fi
    
    # Remove prefixo origin/ se fornecido
    source_branch=${source_branch#origin/}
    
    # Se for um branch remoto, faz fetch primeiro
    if git show-ref --verify --quiet refs/remotes/origin/$source_branch; then
        echo "🔄 Atualizando informações do branch remoto..."
        git fetch origin $source_branch
    fi
    
    echo ""
    echo "Tipo de merge:"
    echo "1) Merge normal (cria commit de merge)"
    echo "2) Merge fast-forward (linear)"
    echo "3) Squash merge (compacta commits)"
    echo ""
    read -p "Escolha (1-3): " merge_type
    
    case $merge_type in
        1)
            git merge $source_branch
            ;;
        2)
            git merge --ff-only $source_branch
            ;;
        3)
            git merge --squash $source_branch
            if [ $? -eq 0 ]; then
                echo ""
                read -p "Mensagem do commit de squash: " squash_msg
                git commit -m "$squash_msg"
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            return
            ;;
    esac
    
    if [ $? -eq 0 ]; then
        echo "✅ Merge realizado com sucesso!"
    else
        echo "❌ Erro no merge! Pode haver conflitos para resolver."
        echo ""
        echo "📋 Arquivos com conflito:"
        git diff --name-only --diff-filter=U
        echo ""
        echo "Resolva os conflitos, faça commit e finalize o merge."
    fi
}

# Menu principal
while true; do
    echo ""
    echo "=== MENU PRINCIPAL ==="
    echo "🌿 Branch atual: $(git branch --show-current)"
    echo ""
    echo "1) Listar branches"
    echo "2) Criar novo branch"
    echo "3) Trocar de branch"
    echo "4) Deletar branch"
    echo "5) Fazer merge"
    echo "6) Atualizar lista de branches remotos"
    echo "0) Sair"
    echo ""
    read -p "Escolha uma opção: " choice
    
    case $choice in
        1)
            list_branches
            ;;
        2)
            create_branch
            ;;
        3)
            switch_branch
            ;;
        4)
            delete_branch
            ;;
        5)
            merge_branch
            ;;
        6)
            echo ""
            echo "🔄 Atualizando informações dos branches remotos..."
            git fetch --all --prune
            if [ $? -eq 0 ]; then
                echo "✅ Atualização concluída!"
                list_branches
            else
                echo "❌ Erro ao atualizar!"
            fi
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
