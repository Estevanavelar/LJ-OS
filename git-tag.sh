#!/bin/bash

# Script para automatizar operações de tags no Git
# Uso: ./git-tag.sh

echo "=== GIT TAG MANAGER ==="

# Verifica se está em um repositório Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "❌ Erro: Não está em um repositório Git!"
    exit 1
fi

# Função para listar tags
list_tags() {
    echo ""
    echo "🏷️  Tags disponíveis:"
    if git tag | grep -q .; then
        git tag -l --sort=-version:refname
        echo ""
        echo "📊 Tags com detalhes:"
        git tag -l -n --sort=-version:refname | head -10
    else
        echo "Nenhuma tag encontrada."
    fi
}

# Função para criar tag
create_tag() {
    echo ""
    echo "🏷️  Criando nova tag..."
    
    read -p "Nome da tag: " tag_name
    
    if [ -z "$tag_name" ]; then
        echo "❌ Nome da tag não pode ser vazio!"
        return
    fi
    
    # Verifica se a tag já existe
    if git tag -l | grep -q "^$tag_name$"; then
        echo "❌ Tag '$tag_name' já existe!"
        return
    fi
    
    echo ""
    echo "Tipo de tag:"
    echo "1) Tag leve (apenas nome)"
    echo "2) Tag anotada (com mensagem)"
    echo "3) Tag em commit específico"
    echo ""
    read -p "Escolha uma opção (1-3): " tag_type
    
    case $tag_type in
        1)
            git tag $tag_name
            ;;
        2)
            read -p "Mensagem da tag: " tag_message
            if [ -z "$tag_message" ]; then
                tag_message="Tag $tag_name - $(date '+%d/%m/%Y %H:%M:%S')"
            fi
            git tag -a $tag_name -m "$tag_message"
            ;;
        3)
            echo ""
            echo "Commits recentes:"
            git log --oneline -10
            echo ""
            read -p "Hash do commit: " commit_hash
            if [ ! -z "$commit_hash" ]; then
                read -p "Mensagem da tag: " tag_message
                if [ -z "$tag_message" ]; then
                    tag_message="Tag $tag_name em $commit_hash"
                fi
                git tag -a $tag_name $commit_hash -m "$tag_message"
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            return
            ;;
    esac
    
    if [ $? -eq 0 ]; then
        echo "✅ Tag '$tag_name' criada com sucesso!"
        
        echo ""
        read -p "Deseja fazer push da tag para o remoto? (s/n): " push_tag
        if [ "$push_tag" = "s" ] || [ "$push_tag" = "S" ]; then
            git push origin $tag_name
            if [ $? -eq 0 ]; then
                echo "✅ Tag enviada para o remoto!"
            fi
        fi
    else
        echo "❌ Erro ao criar tag!"
    fi
}

# Função para deletar tag
delete_tag() {
    list_tags
    
    if ! git tag | grep -q .; then
        return
    fi
    
    echo ""
    read -p "Nome da tag para deletar: " tag_name
    
    if [ -z "$tag_name" ]; then
        echo "❌ Nome da tag não pode ser vazio!"
        return
    fi
    
    # Verifica se a tag existe
    if ! git tag -l | grep -q "^$tag_name$"; then
        echo "❌ Tag '$tag_name' não existe!"
        return
    fi
    
    echo ""
    echo "Opções de exclusão:"
    echo "1) Deletar apenas localmente"
    echo "2) Deletar local e remoto"
    echo ""
    read -p "Escolha uma opção (1-2): " delete_choice
    
    case $delete_choice in
        1)
            git tag -d $tag_name
            if [ $? -eq 0 ]; then
                echo "✅ Tag '$tag_name' deletada localmente!"
            fi
            ;;
        2)
            git tag -d $tag_name
            git push origin --delete $tag_name
            if [ $? -eq 0 ]; then
                echo "✅ Tag '$tag_name' deletada local e remotamente!"
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para fazer checkout de tag
checkout_tag() {
    list_tags
    
    if ! git tag | grep -q .; then
        return
    fi
    
    echo ""
    read -p "Nome da tag para checkout: " tag_name
    
    if [ -z "$tag_name" ]; then
        echo "❌ Nome da tag não pode ser vazio!"
        return
    fi
    
    # Verifica se a tag existe
    if ! git tag -l | grep -q "^$tag_name$"; then
        echo "❌ Tag '$tag_name' não existe!"
        return
    fi
    
    echo ""
    echo "⚠️  ATENÇÃO: Checkout de tag cria um estado 'detached HEAD'"
    echo "Opções:"
    echo "1) Fazer checkout da tag (detached HEAD)"
    echo "2) Criar branch a partir da tag"
    echo ""
    read -p "Escolha uma opção (1-2): " checkout_choice
    
    case $checkout_choice in
        1)
            git checkout $tag_name
            if [ $? -eq 0 ]; then
                echo "✅ Checkout da tag '$tag_name' realizado!"
                echo "💡 Use 'git checkout -' para voltar ao branch anterior"
            fi
            ;;
        2)
            read -p "Nome do novo branch: " branch_name
            if [ ! -z "$branch_name" ]; then
                git checkout -b $branch_name $tag_name
                if [ $? -eq 0 ]; then
                    echo "✅ Branch '$branch_name' criado a partir da tag '$tag_name'!"
                fi
            fi
            ;;
        *)
            echo "❌ Opção inválida!"
            ;;
    esac
}

# Função para fazer push de todas as tags
push_all_tags() {
    echo ""
    echo "📤 Enviando todas as tags para o remoto..."
    git push origin --tags
    
    if [ $? -eq 0 ]; then
        echo "✅ Todas as tags foram enviadas!"
    else
        echo "❌ Erro ao enviar tags!"
    fi
}

# Função para buscar tags remotas
fetch_tags() {
    echo ""
    echo "🔄 Buscando tags do repositório remoto..."
    git fetch --tags
    
    if [ $? -eq 0 ]; then
        echo "✅ Tags atualizadas!"
        list_tags
    else
        echo "❌ Erro ao buscar tags!"
    fi
}

# Função para mostrar informações da tag
show_tag_info() {
    list_tags
    
    if ! git tag | grep -q .; then
        return
    fi
    
    echo ""
    read -p "Nome da tag para detalhes: " tag_name
    
    if [ -z "$tag_name" ]; then
        echo "❌ Nome da tag não pode ser vazio!"
        return
    fi
    
    # Verifica se a tag existe
    if ! git tag -l | grep -q "^$tag_name$"; then
        echo "❌ Tag '$tag_name' não existe!"
        return
    fi
    
    echo ""
    echo "🏷️  Informações da tag '$tag_name':"
    echo ""
    git show $tag_name --stat
}

# Menu principal
while true; do
    echo ""
    echo "=== MENU TAG ==="
    echo "🌿 Branch atual: $(git branch --show-current)"
    echo ""
    echo "1) 📋 Listar tags"
    echo "2) 🏷️  Criar nova tag"
    echo "3) 🗑️  Deletar tag"
    echo "4) 🔄 Fazer checkout de tag"
    echo "5) 📤 Enviar todas as tags"
    echo "6) 🔍 Buscar tags remotas"
    echo "7) 👁️  Ver detalhes da tag"
    echo "0) 🚪 Sair"
    echo ""
    read -p "Escolha uma opção: " choice
    
    case $choice in
        1)
            list_tags
            ;;
        2)
            create_tag
            ;;
        3)
            delete_tag
            ;;
        4)
            checkout_tag
            ;;
        5)
            push_all_tags
            ;;
        6)
            fetch_tags
            ;;
        7)
            show_tag_info
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
