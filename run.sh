#!/bin/bash

set -e

COLOR_GREEN=$(tput setaf 2)
COLOR_YELLOW=$(tput setaf 3)
COLOR_BLUE=$(tput setaf 4)
COLOR_RESET=$(tput sgr0)

echo "${COLOR_BLUE}--- INICIANDO SCRIPT DE DEPLOY LOCAL (ZERO-CONFIG) ---${COLOR_RESET}"

echo "\n${COLOR_YELLOW}PASSO 1: Construindo e subindo os contêineres...${COLOR_RESET}"
docker compose -f docker-compose.local.yml down
docker compose -f docker-compose.local.yml build --no-cache
docker compose -f docker-compose.local.yml up -d
echo "${COLOR_GREEN}Contêineres construídos e iniciados com sucesso!${COLOR_RESET}"


echo "\n${COLOR_YELLOW}PASSO 2: Configurando a aplicação DENTRO do contêiner...${COLOR_RESET}"

echo "Aguardando a aplicação ficar saudável (até 60s)..."
timeout 60s bash -c 'until docker compose -f docker-compose.local.yml ps | grep alpesone-app | grep -q healthy; do sleep 2; done' || true

if ! docker compose -f docker-compose.local.yml ps | grep alpesone-app | grep -q healthy; then
    echo "Erro: A aplicação não ficou saudável a tempo. Verifique os logs com 'docker compose logs app'."
    exit 1
fi

echo "Aplicação está saudável! Rodando comandos de setup..."

echo "Criando arquivo .env no contêiner..."
cat <<EOF | docker compose -f docker-compose.local.yml exec -T app sh -c 'cat > .env'
#======================================================================
# APPLICATION CONFIGURATION
#======================================================================
APP_NAME="Alpes One Test"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
#======================================================================
# LOG CONFIGURATION
#======================================================================
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
#======================================================================
# DATABASE CONFIGURATION
#======================================================================
DB_CONNECTION=mysql
DB_HOST=db-mysql
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=user
DB_PASSWORD=123
DB_ROOT_PASSWORD=123
#======================================================================
# SWAGGER CONFIGURATION
#======================================================================
L5_SWAGGER_CONST_HOST=http://localhost
EOF

SETUP_COMMANDS=(
  "php artisan key:generate"
  "php artisan config:clear"
  "php artisan config:cache"
  "php artisan route:cache"
  "php artisan migrate --force"
  "php artisan db:seed --force"
  "php artisan l5-swagger:generate"
  "php artisan app:import-vehicles"
)

for cmd in "${SETUP_COMMANDS[@]}"; do
    echo "Executando no contêiner: ${cmd}"
    docker compose -f docker-compose.local.yml exec -T app ${cmd}
done

echo "${COLOR_GREEN}Configuração da aplicação concluída com sucesso!${COLOR_RESET}"

echo "\n${COLOR_BLUE}--- SCRIPT DE DEPLOY LOCAL CONCLUÍDO ---${COLOR_RESET}"
echo "Sua aplicação está no ar e pronta para uso em http://localhost"
