# Teste Técnico - Alpes One

API de importação e gerenciamento de veículos com infraestrutura AWS e deploy automatizado.

## 📋 Índice

- [Visão Geral](#-visão-geral)
- [Etapa 1: Aplicação Laravel](#-etapa-1-aplicação-laravel)
- [Etapa 2: Infraestrutura AWS](#-etapa-2-infraestrutura-aws)
- [Etapa 3: Deploy Automatizado](#-etapa-3-deploy-automatizado)
- [Configuração Local](#-configuração-local)
- [Testes](#-testes)
- [API Endpoints](#-api-endpoints)

## 🎯 Visão Geral

Esta aplicação Laravel implementa uma API REST para gerenciamento de veículos, com importação automática de dados
externos, deploy automatizado via GitHub Actions e infraestrutura hospedada na AWS EC2.

### Recursos Implementados

- ✅ API REST completa com CRUD de veículos
- ✅ Comando Artisan para importação de dados da API externa
- ✅ Autenticação via tokens (Laravel Sanctum)
- ✅ Testes automatizados (unitários e integração)
- ✅ Infraestrutura AWS EC2 com Docker
- ✅ Deploy automatizado com GitHub Actions
- ✅ HTTPS com Let's Encrypt
- ✅ Domínio personalizado
- ✅ Documentação Swagger

### URLs de Acesso

- **API Production**: https://pvss-dev.ddns.net
- **Documentação Swagger**: https://pvss-dev.ddns.net/api/documentation
- **Repositório**: https://github.com/pvss-dev/alpesone-test

## 🚀 Etapa 1: Aplicação Laravel

### Arquitetura da Aplicação

A aplicação segue a arquitetura MVC do Laravel com as seguintes camadas:

```
├── app/
│   ├── Console/Commands/
│   │   └── ImportVehicles.php           # Comando de importação
│   ├── Http/Controllers/Api
│   │   ├── AuthController.php           # Autenticação
│   │   └── VehicleController.php        # CRUD de veículos
│   ├── Models/
│   │   ├── User.php                     # Model de usuário
│   │   └── Vehicle.php                  # Model de veículo
├── database/
│   ├── migrations/                      # Migrações do banco
│   └── seeders/                         # Seeds iniciais
└── tests/
    ├── Feature/                         # Testes de integração
    └── Unit/                            # Testes unitários
```

### Comando de Importação

O comando `app:import-vehicles` foi implementado para:

1. **Baixar dados** da URL `https://hub.alpes.one/api/v1/integrator/export/1902`
2. **Validar** os dados recebidos
3. **Inserir/Atualizar** veículos usando a placa como chave única
4. **Executar automaticamente** a cada hora via cron

```bash
# Execução manual
php artisan app:import-vehicles

# Execução agendada (configurada no Scheduler)
php artisan schedule:run
```

### API REST

A API fornece endpoints completos para gerenciamento de veículos:

| Método | Endpoint             | Descrição                  | Auth |
|--------|----------------------|----------------------------|------|
| POST   | `/api/login`         | Autenticação               | ❌    |
| POST   | `/api/logout`        | Logout                     | ✅    |
| GET    | `/api/vehicles`      | Listar veículos (paginado) | ✅    |
| GET    | `/api/vehicles/{id}` | Visualizar veículo         | ✅    |
| POST   | `/api/vehicles`      | Criar veículo              | ✅    |
| PUT    | `/api/vehicles/{id}` | Atualizar veículo          | ✅    |
| DELETE | `/api/vehicles/{id}` | Deletar veículo            | ✅    |

### Autenticação

A aplicação utiliza **Laravel Sanctum** para autenticação via tokens:

```bash
// Login
POST /api/login
{
    "email": "test@example.com",
    "password": "123"
}

// Resposta
{
    "access_token": "1|token...",
    "token_type": "Bearer"
}
```

Todos os endpoints protegidos requerem o header:

```
Authorization: Bearer {token}
```

## 🏗️ Etapa 2: Infraestrutura AWS

### Configuração EC2

A aplicação está hospedada em uma instância EC2 com as seguintes configurações:

- **Tipo**: m7i-flex.large
- **Sistema**: Ubuntu 22.04 LTS
- **Região**: sa-east-1 (São Paulo)
- **Security Group**: Portas 22 (SSH), 80 (HTTP), 443 (HTTPS)

### Domínio e HTTPS

- **Domínio**: pvss-dev.ddns.net (NoIP Dynamic DNS)
- **SSL/TLS**: Let's Encrypt (renovação automática)
- **Redirecionamento**: HTTP → HTTPS automático

### Containerização

A aplicação utiliza Docker Compose com os seguintes serviços:

```yaml
services:
    app:           # Laravel + PHP-FPM
    nginx:         # Servidor web
    db-mysql:      # Banco de dados MySQL 8.0
```

### Fluxo de Requisições

A arquitetura segue um fluxo otimizado para alta performance:

```
Cliente/Browser
      ↓
[Internet] → Cloudflare (DNS)
      ↓
[AWS EC2] → Nginx (Porta 443)
      ↓
├─ Requisições API → PHP-FPM (app:9000)
      ↓
   Laravel Framework
      ↓
   Controllers/Models
      ↓
   MySQL (db-mysql:3306)
```

### Instruções de Configuração EC2

#### 1. Criar Instância EC2

1. Acesse AWS Console → EC2 → Launch Instance
2. **Nome**: `alpesone-api`
3. **AMI**: Ubuntu 24.04 LTS
4. **Instance Type**: m7i-flex.large
5. **Key Pair**: Criar nova chave `.pem`
6. **Security Group**:
    - SSH (22): My IP
    - HTTP (80): Anywhere
    - HTTPS (443): Anywhere

#### 2. Conectar via SSH

```bash
chmod 400 sua-chave.pem
ssh -i "sua-chave.pem" ubuntu@ec2-xx-xxx-xx-xxx.sa-east-1.compute.amazonaws.com
```

#### 3. Instalar Docker

```bash
# Atualizar sistema
sudo apt-get update && sudo apt-get upgrade -y

# Instalar dependências
sudo apt-get install ca-certificates curl

# Adicionar repositório Docker
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Instalar Docker
sudo apt-get update
sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Configurar usuário
sudo usermod -aG docker $USER
```

**Importante**: Reconecte via SSH após executar o `usermod`.

## 🔄 Etapa 3: Deploy Automatizado

### Pipeline CI/CD (GitHub Actions)

O deploy é totalmente automatizado através de um pipeline com 3 estágios:

#### 1. **Test** - Testes Automatizados

- Executa em `ubuntu-latest` com MySQL de teste
- Instala PHP 8.4 e dependências
- Executa todos os testes unitários e de integração

#### 2. **Build and Push** - Construção da Imagem

- Constrói imagem Docker otimizada (multi-stage)
- Publica no Docker Hub com tags `latest` e SHA do commit
- Utiliza cache para otimizar builds

#### 3. **Deploy** - Implantação

- Executa em **self-hosted runner** (na própria EC2)
- Atualiza containers com nova imagem
- Executa comandos pós-deploy (migrations, cache, etc.)

### Configuração do Runner Self-hosted

O runner executa diretamente na EC2, eliminando necessidade de SSH:

1. **Instalar GitHub Runner**:

```bash
# Na EC2

# Create a folder
$ mkdir actions-runner && cd actions-runner

# Download the latest runner package
$ curl -o actions-runner-linux-x64-2.328.0.tar.gz -L https://github.com/actions/runner/releases/download/v2.328.0/actions-runner-linux-x64-2.328.0.tar.gz

# Optional: Validate the hash
$ echo "01066fad3a2893e63e6ca880ae3a1fad5bf9329d60e77ee15f2b97c148c3cd4e  actions-runner-linux-x64-2.328.0.tar.gz" | shasum -a 256 -c

# Extract the installer
$ tar xzf ./actions-runner-linux-x64-2.328.0.tar.gz

# Create the runner and start the configuration experience
$ ./config.sh --url https://github.com/usuer/repo --token AGVCBVTVTN44TCFRG7XRNPDIV6FY45

# Last step, run it!
$ ./run.sh
```

### Secrets Necessários

Configure no GitHub (Settings → Secrets):

```
# Docker Hub
DOCKERHUB_USERNAME
DOCKERHUB_TOKEN

# Aplicação
APP_ENV=production
APP_KEY=base64:...
APP_URL=https://pvss-dev.ddns.net

# Banco de Dados
DB_HOST=db-mysql
DB_DATABASE=my_database
DB_USERNAME=user
DB_PASSWORD=password
DB_ROOT_PASSWORD=rootpassword
```

## 💻 Configuração Local

### Pré-requisitos

- Docker & Docker Compose
- Git

### Instalação

```bash
# 1. Clonar repositório
git clone https://github.com/pvss-dev/alpesone-test.git
cd alpesone-test

# 2. Configurar ambiente
cp .env.example .env
# Editar .env com configurações locais

# 3. Construir e iniciar containers
docker compose up -d --build

# 4. Configurar aplicação
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan l5-swagger:generate

# 5. Importar dados iniciais
docker compose exec app php artisan app:import-vehicles
```

### Configurar Cron (Produção)

```bash
# Editar crontab
crontab -e

# Adicionar linha (substituir caminho):
* * * * * cd /caminho/para/projeto && docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

## 🧪 Testes

### Execução Local

```bash
# Todos os testes
docker compose exec app php artisan test

# Apenas testes unitários
docker compose exec app php artisan test --testsuite=Unit

# Apenas testes de integração
docker compose exec app php artisan test --testsuite=Feature

# Com coverage
docker compose exec app php artisan test --coverage
```

### Estrutura de Testes

#### Testes Unitários

- `VehicleImportTest`: Validação dos dados e lógica de importação

#### Testes de Integração

- `AuthTest`: Endpoints de login/logout
- `VehicleApiTest`: CRUD completo da API

Os testes cobrem:

- ✅ Autenticação e autorização
- ✅ CRUD completo de veículos
- ✅ Validação de dados

## 📡 API Endpoints

### Autenticação

```http
POST /api/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "123"
}
```

### Veículos

```http
# Listar (com paginação)
GET /api/vehicles?page=1&per_page=15
Authorization: Bearer {token}

# Visualizar
GET /api/vehicles/1
Authorization: Bearer {token}

# Criar
POST /api/vehicles
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "carro",
  "brand": "Hyundai",
  "model": "CRETA",
  "version": "CRETA 16A ACTION",
  "year_model": "2025",
  "year_build": "2025",
  "optionals": "Informações extras",
  "doors": 5,
  "board": "BRA2E19",
  "chassi": "4TUKBM8WFSJTH1635",
  "transmission": "Automática",
  "km": 24208,
  "description": "Carro em ótimo estado de conservação, único dono, revisões em dia.",
  "sold": false,
  "category": "Carros",
  "url_car": "https://example.com/creta-16a-action.jpg",
  "price": 115900,
  "color": "Branco",
  "fuel": "Flex",
  "photos": [
    "https://example.s3.amazonaws.com/creta1.jpeg",
    "https://example.s3.amazonaws.com/creta2.jpeg"
  ]
}

# Atualizar
PUT /api/vehicles/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "model": "Novo Modelo"
}

# Deletar
DELETE /api/vehicles/1
Authorization: Bearer {token}
```

### Collection do Postman

Uma collection completa está disponível
em: [test_collection.postman_collection.json](test_collection.postman_collection.json)

## 📚 Documentação Adicional

- **Swagger UI**: https://pvss-dev.ddns.net/api/documentation
- **Collection Postman**: [test_collection.postman_collection.json](test_collection.postman_collection.json)
- **Pipeline CI/CD**: [.github/workflows/deploy.yml](.github/workflows/deploy.yml)
- **Docker Configuration**: [docker-compose.yml](docker-compose.yml) e [Dockerfile](Dockerfile)
