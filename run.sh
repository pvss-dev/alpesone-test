#!/bin/bash

set -e

COLOR_GREEN=$(tput setaf 2)
COLOR_YELLOW=$(tput setaf 3)
COLOR_BLUE=$(tput setaf 4)
COLOR_RESET=$(tput sgr0)

echo "${COLOR_BLUE}--- INICIANDO SCRIPT DE DEPLOY LOCAL ---${COLOR_RESET}"

echo "\n${COLOR_YELLOW}PASSO 1: Preparando o ambiente...${COLOR_RESET}"

if [ ! -f ".env" ]; then
    echo "Arquivo .env não encontrado. Copiando de .env.example..."
    cp .env.example .env
    echo "${COLOR_GREEN}Arquivo .env criado.${COLOR_RESET} Por favor, preencha-o com suas configurações e rode o script novamente."
    exit 0
fi

echo "Verificando dependências do Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "Ajustando permissões das pastas storage e bootstrap/cache..."
sudo chmod -R 777 storage bootstrap/cache

echo "${COLOR_GREEN}Ambiente preparado com sucesso!${COLOR_RESET}"

echo "\n${COLOR_YELLOW}PASSO 2: Construindo as imagens Docker (Build)...${COLOR_RESET}"
docker compose build --no-cache

echo "${COLOR_GREEN}Imagens construídas com sucesso!${COLOR_RESET}"

echo "\n${COLOR_YELLOW}PASSO 3: Subindo os contêineres (Deploy)...${COLOR_RESET}"
docker compose down
docker compose up -d

echo "${COLOR_GREEN}Contêineres iniciados com sucesso!${COLOR_RESET}"

echo "\n${COLOR_YELLOW}PASSO 4: Executando comandos pós-deploy...${COLOR_RESET}"

echo "Aguardando a aplicação ficar saudável (até 60s)..."

timeout 60s bash -c 'until docker compose ps | grep alpesone-app | grep -q healthy; do sleep 2; done' || true

if ! docker compose ps | grep alpesone-app | grep -q healthy; then
    echo "Erro: A aplicação não ficou saudável a tempo. Verifique os logs com 'docker compose logs app'."
    exit 1
fi

echo "Aplicação está saudável! Rodando comandos artisan..."

COMMANDS=(
  "php artisan config:clear"
  "php artisan config:cache"
  "php artisan route:cache"
  "php artisan migrate --force"
  "php artisan db:seed --force"
  "php artisan l5-swagger:generate"
)

for cmd in "${COMMANDS[@]}"; do
    echo "Executando: docker compose exec -T app ${cmd}"
    docker compose exec -T app ${cmd}
done

echo "${COLOR_GREEN}Comandos pós-deploy executados com sucesso!${COLOR_RESET}"

echo "\n${COLOR_BLUE}--- SCRIPT DE DEPLOY LOCAL CONCLUÍDO ---${COLOR_RESET}"
