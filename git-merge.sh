#!/bin/bash

# Script para automatizar operações de merge com resolução inteligente de conflitos
# Uso: ./git-merge.sh

echo "=== GIT MERGE MANAGER ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Função para mostrar status atual
show_status() {
    echo ""
    echo "📊 Status atual:"
    echo "🌿 Branch atual: $(git branch --show-current)"
    echo "📡 Branch remoto: $(git branch -r | grep 'origin/HEAD' | sed 's/origin\/HEAD -> origin\///')"
    echo ""
    git status -s
}

# Função para listar branches disponíveis
list_branches() {
    echo ""
    echo "🌿 Branches disponíveis:"
    echo ""
    echo "📋 Branches locais:"
    git branch -v | sed 's/^/  /'
    echo ""
    echo "📡 Branches remotos:"
    git branch -r | sed 's/^/  /'
    echo ""
    echo "🔄 Branches com tracking:"
    git branch -vv | sed 's/^/  /'
}

# Função para verificar se há mudanças não commitadas
check_uncommitted_changes() {
    if [ ! -z "$(git status --porcelain)" ]; then
        echo ""
        echo "⚠️  ATENÇÃO: Há mudanças não commitadas!"
        echo ""
        echo "Opções:"
        echo "1) Fazer commit das mudanças"
        echo "2) Fazer stash das mudanças"
        echo "3) Descartar mudanças (CUIDADO!)"
        echo "4) Cancelar operação"
        echo ""
        read -p "Escolha uma opção (1-4): " change_choice
        
        case $change_choice in
            1)
                echo ""
                read -p "Mensagem do commit: " commit_msg
                if [ -z "$commit_msg" ]; then
                    commit_msg="Commit antes do merge - $(date '+%d/%m/%Y %H:%M:%S')"
                fi
                git add -A
                git commit -m "$commit_msg"
                if [ $? -eq 0 ]; then
                    echo "✅ Commit realizado com sucesso!"
                else
                    echo "❌ Erro ao fazer commit!"
                    return 1
                fi
                ;;
            2)
                echo ""
                read -p "Mensagem do stash: " stash_msg
                if [ -z "$stash_msg" ]; then
                    stash_msg="Stash antes do merge - $(date '+%d/%m/%Y %H:%M:%S')"
                fi
                git stash push -u -m "$stash_msg"
                if [ $? -eq 0 ]; then
                    echo "✅ Stash criado com sucesso!"
                else
                    echo "❌ Erro ao criar stash!"
                    return 1
                fi
                ;;
            3)
                echo ""
                read -p "⚠️  Confirma descartar TODAS as mudanças? (s/n): " confirm_discard
                if [ "$confirm_discard" = "s" ] || [ "$confirm_discard" = "S" ]; then
                    git reset --hard HEAD
                    git clean -fd
                    echo "✅ Mudanças descartadas!"
                else
                    echo "Operação cancelada."
                    return 1
                fi
                ;;
            4)
                echo "Operação cancelada."
                return 1
                ;;
            *)
                echo "❌ Opção inválida!"
                return 1
                ;;
        esac
    fi
    return 0
}

# Função para merge simples
simple_merge() {
    echo ""
    echo "🔄 Merge simples..."
    
    list_branches
    
    read -p "Nome do branch para merge: " merge_branch
    
    if [ -z "$merge_branch" ]; then
        echo "❌ Nome do branch não pode ser vazio!"
        return
    fi
    
    # Remove 'origin/' se presente
    merge_branch=$(echo $merge_branch | sed 's/^origin\///')
    
    # Verifica se o branch existe
    if ! git show-ref --verify --quiet refs/heads/$merge_branch && ! git show-ref --verify --quiet refs/remotes/origin/$merge_branch; then
        echo "❌ Branch '$merge_branch' não existe!"
        return
    fi
    
    echo ""
    echo "🔄 Fazendo merge de '$merge_branch' para '$(git branch --show-current)'..."
    
    # Se for branch remoto, faz fetch primeiro
    if git show-ref --verify --quiet refs/remotes/origin/$merge_branch; then
        echo "📡 Fazendo fetch do branch remoto..."
        git fetch origin $merge_branch:$merge_branch
    fi
    
    # Tenta o merge
    git merge $merge_branch
    
    if [ $? -eq 0 ]; then
        echo "✅ Merge realizado com sucesso!"
        echo ""
        echo "📊 Status após merge:"
        git status -s
    else
        echo "⚠️  Conflitos detectados! Iniciando resolução automática..."
        resolve_conflicts_auto
    fi
}

# Função para merge com rebase
merge_with_rebase() {
    echo ""
    echo "🔄 Merge com rebase..."
    
    list_branches
    
    read -p "Nome do branch para rebase: " rebase_branch
    
    if [ -z "$rebase_branch" ]; then
        echo "❌ Nome do branch não pode ser vazio!"
        return
    fi
    
    # Remove 'origin/' se presente
    rebase_branch=$(echo $rebase_branch | sed 's/^origin\///')
    
    # Verifica se o branch existe
    if ! git show-ref --verify --quiet refs/heads/$rebase_branch && ! git show-ref --verify --quiet refs/remotes/origin/$rebase_branch; then
        echo "❌ Branch '$rebase_branch' não existe!"
        return
    fi
    
    echo ""
    echo "🔄 Fazendo rebase de '$(git branch --show-current)' sobre '$rebase_branch'..."
    
    # Se for branch remoto, faz fetch primeiro
    if git show-ref --verify --quiet refs/remotes/origin/$rebase_branch; then
        echo "📡 Fazendo fetch do branch remoto..."
        git fetch origin $rebase_branch:$rebase_branch
    fi
    
    # Tenta o rebase
    git rebase $rebase_branch
    
    if [ $? -eq 0 ]; then
        echo "✅ Rebase realizado com sucesso!"
        echo ""
        echo "📊 Status após rebase:"
        git status -s
    else
        echo "⚠️  Conflitos detectados! Iniciando resolução automática..."
        resolve_conflicts_auto
    fi
}

# Função para merge de pull request
merge_pull_request() {
    echo ""
    echo "🔄 Merge de Pull Request..."
    
    echo "📡 Buscando informações do remoto..."
    git fetch --all
    
    list_branches
    
    read -p "Nome do branch da PR: " pr_branch
    
    if [ -z "$pr_branch" ]; then
        echo "❌ Nome do branch não pode ser vazio!"
        return
    fi
    
    # Remove 'origin/' se presente
    pr_branch=$(echo $pr_branch | sed 's/^origin\///')
    
    # Verifica se o branch existe
    if ! git show-ref --verify --quiet refs/remotes/origin/$pr_branch; then
        echo "❌ Branch remoto '$pr_branch' não existe!"
        return
    fi
    
    echo ""
    echo "🔄 Fazendo merge da PR '$pr_branch'..."
    
    # Cria branch local da PR
    local_pr_branch="pr-$pr_branch"
    git checkout -b $local_pr_branch origin/$pr_branch
    
    if [ $? -eq 0 ]; then
        echo "✅ Branch local criado: $local_pr_branch"
        
        # Volta para o branch principal
        git checkout $(git branch --show-current)
        
        # Faz o merge
        git merge $local_pr_branch
        
        if [ $? -eq 0 ]; then
            echo "✅ PR mergeada com sucesso!"
            
            # Remove o branch local da PR
            git branch -d $local_pr_branch
            echo "🗑️  Branch local da PR removido."
        else
            echo "⚠️  Conflitos detectados! Iniciando resolução automática..."
            resolve_conflicts_auto
        fi
    else
        echo "❌ Erro ao criar branch local da PR!"
    fi
}

# Função para resolução automática de conflitos
resolve_conflicts_auto() {
    echo ""
    echo "🔧 Resolução automática de conflitos..."
    
    # Lista arquivos com conflitos
    conflicted_files=$(git diff --name-only --diff-filter=U)
    
    if [ -z "$conflicted_files" ]; then
        echo "✅ Nenhum conflito detectado!"
        return
    fi
    
    echo ""
    echo "⚠️  Arquivos com conflitos:"
    echo "$conflicted_files"
    echo ""
    
    echo "🔧 Estratégias de resolução:"
    echo "1) Resolver manualmente (recomendado para conflitos complexos)"
    echo "2) Aceitar nossa versão (HEAD)"
    echo "3) Aceitar versão do branch mergeado"
    echo "4) Resolução inteligente por tipo de arquivo"
    echo "5) Cancelar merge/rebase"
    echo ""
    read -p "Escolha uma estratégia (1-5): " resolve_strategy
    
    case $resolve_strategy in
        1)
            echo ""
            echo "🔧 Abrindo editor para resolução manual..."
            echo "💡 Dicas para resolver conflitos:"
            echo "   - Procure por marcadores <<<<<<<, =======, >>>>>>>"
            echo "   - Escolha qual versão manter ou combine ambas"
            echo "   - Remova os marcadores de conflito"
            echo "   - Salve o arquivo"
            echo ""
            read -p "Pressione Enter quando terminar de resolver os conflitos..."
            
            # Verifica se ainda há conflitos
            if [ ! -z "$(git diff --name-only --diff-filter=U)" ]; then
                echo "⚠️  Ainda há conflitos não resolvidos!"
                echo "Use 'git status' para ver arquivos com conflitos."
                return
            fi
            
            complete_merge
            ;;
        2)
            echo ""
            echo "✅ Aceitando nossa versão (HEAD) para todos os conflitos..."
            git checkout --ours -- .
            git add -A
            complete_merge
            ;;
        3)
            echo ""
            echo "✅ Aceitando versão do branch mergeado para todos os conflitos..."
            git checkout --theirs -- .
            git add -A
            complete_merge
            ;;
        4)
            echo ""
            echo "🧠 Resolução inteligente por tipo de arquivo..."
            resolve_conflicts_smart
            ;;
        5)
            echo ""
            echo "❌ Cancelando merge/rebase..."
            if git status | grep -q "rebasing"; then
                git rebase --abort
                echo "✅ Rebase cancelado!"
            else
                git merge --abort
                echo "✅ Merge cancelado!"
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para resolução inteligente por tipo de arquivo
resolve_conflicts_smart() {
    echo ""
    echo "🧠 Resolução inteligente por tipo de arquivo..."
    
    for file in $conflicted_files; do
        echo ""
        echo "📁 Processando: $file"
        
        # Determina estratégia baseada na extensão
        case "$file" in
            *.lock|*.log|*.tmp|*.temp)
                echo "   🔄 Arquivo temporário - aceitando nossa versão"
                git checkout --ours -- "$file"
                ;;
            *.json|*.xml|*.yaml|*.yml|*.config|*.conf)
                echo "   ⚙️  Arquivo de configuração - aceitando nossa versão"
                git checkout --ours -- "$file"
                ;;
            *.md|*.txt|*.rst)
                echo "   📝 Documentação - aceitando nossa versão"
                git checkout --ours -- "$file"
                ;;
            *.test.js|*.test.ts|*.spec.js|*.spec.ts|*Test.java|*Test.kt)
                echo "   🧪 Arquivo de teste - aceitando nossa versão"
                git checkout --ours -- "$file"
                ;;
            *.css|*.scss|*.less)
                echo "   🎨 Arquivo de estilo - aceitando nossa versão"
                git checkout --ours -- "$file"
                ;;
            *)
                echo "   ❓ Arquivo não reconhecido - aceitando nossa versão"
                git checkout --ours -- "$file"
                ;;
        esac
        
        git add "$file"
        echo "   ✅ Conflito resolvido para $file"
    done
    
    complete_merge
}

# Função para completar o merge
complete_merge() {
    echo ""
    echo "✅ Todos os conflitos foram resolvidos!"
    
    # Verifica se ainda há conflitos
    if [ ! -z "$(git diff --name-only --diff-filter=U)" ]; then
        echo "⚠️  Ainda há conflitos não resolvidos!"
        echo "Use 'git status' para ver arquivos com conflitos."
        return
    fi
    
    # Completa o merge/rebase
    if git status | grep -q "rebasing"; then
        echo "🔄 Completando rebase..."
        git rebase --continue
        if [ $? -eq 0 ]; then
            echo "✅ Rebase completado com sucesso!"
        else
            echo "❌ Erro ao completar rebase!"
        fi
    else
        echo "🔄 Completando merge..."
        git commit -m "Merge automático - $(date '+%d/%m/%Y %H:%M:%S')"
        if [ $? -eq 0 ]; then
            echo "✅ Merge completado com sucesso!"
        else
            echo "❌ Erro ao completar merge!"
        fi
    fi
    
    echo ""
    echo "📊 Status final:"
    git status -s
}

# Função para merge de múltiplos branches
merge_multiple_branches() {
    echo ""
    echo "🔄 Merge de múltiplos branches..."
    
    list_branches
    
    echo ""
    echo "Digite os nomes dos branches para merge (separados por espaço):"
    read -p "Branches: " branches_input
    
    if [ -z "$branches_input" ]; then
        echo "❌ Nenhum branch especificado!"
        return
    fi
    
    # Converte input em array
    IFS=' ' read -ra branches <<< "$branches_input"
    
    echo ""
    echo "🔄 Iniciando merge de ${#branches[@]} branches..."
    
    for branch in "${branches[@]}"; do
        echo ""
        echo "🔄 Fazendo merge de '$branch'..."
        
        # Remove 'origin/' se presente
        branch=$(echo $branch | sed 's/^origin\///')
        
        # Verifica se o branch existe
        if ! git show-ref --verify --quiet refs/heads/$branch && ! git show-ref --verify --quiet refs/remotes/origin/$branch; then
            echo "⚠️  Branch '$branch' não existe, pulando..."
            continue
        fi
        
        # Se for branch remoto, faz fetch primeiro
        if git show-ref --verify --quiet refs/remotes/origin/$branch; then
            git fetch origin $branch:$branch
        fi
        
        # Tenta o merge
        git merge $branch
        
        if [ $? -eq 0 ]; then
            echo "✅ Merge de '$branch' realizado com sucesso!"
        else
            echo "⚠️  Conflitos em '$branch'! Iniciando resolução..."
            resolve_conflicts_auto
            break
        fi
    done
    
    echo ""
    echo "📊 Status final:"
    git status -s
}

# Função para merge com squash
merge_with_squash() {
    echo ""
    echo "🔄 Merge com squash..."
    
    list_branches
    
    read -p "Nome do branch para squash merge: " squash_branch
    
    if [ -z "$squash_branch" ]; then
        echo "❌ Nome do branch não pode ser vazio!"
        return
    fi
    
    # Remove 'origin/' se presente
    squash_branch=$(echo $squash_branch | sed 's/^origin\///')
    
    # Verifica se o branch existe
    if ! git show-ref --verify --quiet refs/heads/$squash_branch && ! git show-ref --verify --quiet refs/remotes/origin/$squash_branch; then
        echo "❌ Branch '$squash_branch' não existe!"
        return
    fi
    
    echo ""
    echo "🔄 Fazendo squash merge de '$squash_branch'..."
    
    # Se for branch remoto, faz fetch primeiro
    if git show-ref --verify --quiet refs/remotes/origin/$squash_branch; then
        git fetch origin $squash_branch:$squash_branch
    fi
    
    # Faz o squash merge
    git merge --squash $squash_branch
    
    if [ $? -eq 0 ]; then
        echo "✅ Squash merge realizado com sucesso!"
        echo ""
        read -p "Mensagem do commit: " commit_msg
        if [ -z "$commit_msg" ]; then
            commit_msg="Squash merge de $squash_branch - $(date '+%d/%m/%Y %H:%M:%S')"
        fi
        git commit -m "$commit_msg"
        echo "✅ Commit criado!"
    else
        echo "❌ Erro no squash merge!"
    fi
}

# Menu principal
while true; do
    echo ""
    echo "=== MENU MERGE ==="
    show_status
    echo ""
    echo "1) 🔄 Merge simples"
    echo "2) 🔄 Merge com rebase"
    echo "3) 🔄 Merge de Pull Request"
    echo "4) 🔄 Merge de múltiplos branches"
    echo "5) 🔄 Merge com squash"
    echo "6) 📋 Listar branches"
    echo "7) 📊 Mostrar status"
    echo "0) 🚪 Sair"
    echo ""
    read -p "Escolha uma opção: " choice
    
    case $choice in
        1)
            if check_uncommitted_changes; then
                simple_merge
            fi
            ;;
        2)
            if check_uncommitted_changes; then
                merge_with_rebase
            fi
            ;;
        3)
            if check_uncommitted_changes; then
                merge_pull_request
            fi
            ;;
        4)
            if check_uncommitted_changes; then
                merge_multiple_branches
            fi
            ;;
        5)
            if check_uncommitted_changes; then
                merge_with_squash
            fi
            ;;
        6)
            list_branches
            ;;
        7)
            show_status
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
