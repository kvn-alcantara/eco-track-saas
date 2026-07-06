# EcoTrack SaaS

MVP de uma plataforma B2B para gestão ambiental e cálculo de pegada de carbono. O projeto cobre multi-tenancy por empresa, autenticação via Sanctum, filas Redis, trilha de auditoria e controle de acesso por perfil (Laravel Policies).

## Stack

- PHP 8.5 + Laravel 13
- MySQL 8.4, Redis 7.4
- Nginx, PHP-FPM (Docker)
- Laravel Sanctum (API tokens)
- Mailhog (e-mail em desenvolvimento)

## Pré-requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (recomendado)
- Git

Para rodar fora do Docker, você também precisa de PHP 8.5, Composer e extensões `pdo_mysql`, `redis`, `mbstring`, `zip`, `intl`.

## Setup com Docker

### 1. Clonar e configurar ambiente

```bash
git clone https://github.com/kvn-alcantara/eco-track-saas.git
cd eco-track-saas
cp .env.example .env
```

O `.env.example` já aponta para os serviços do Docker (`mysql`, `redis`, `mailhog`). Ajuste apenas se necessário.

### 2. Subir os containers

```bash
docker compose up -d --build
```

Serviços expostos:

| Serviço  | URL / Porta                    |
|----------|--------------------------------|
| API      | http://localhost:8080/api/v1   |
| MySQL    | localhost:3306                 |
| Redis    | localhost:6379                 |
| Mailhog  | http://localhost:8025          |

### 3. Instalar dependências e preparar a aplicação

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 4. Processar filas (relatórios assíncronos)

A geração de relatórios de carbono usa fila Redis. Em outro terminal:

```bash
docker compose exec app php artisan queue:work
```

### 5. Verificar saúde da API

```bash
curl http://localhost:8080/
```

Resposta esperada:

```json
{"app":"EcoTrack SaaS","status":"ok"}
```

## Autenticação

A API usa **Bearer Token** (Laravel Sanctum).

1. Registre uma empresa e o primeiro usuário (recebe perfil `admin`):

```bash
curl -X POST http://localhost:8080/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin",
    "email": "admin@empresa.com",
    "password": "password",
    "password_confirmation": "password",
    "company_name": "Minha Empresa"
  }'
```

2. Use o `token` retornado nas requisições protegidas:

```bash
curl http://localhost:8080/api/v1/me \
  -H "Authorization: Bearer SEU_TOKEN"
```

## Perfis de acesso

| Perfil        | Descrição |
|---------------|-----------|
| `admin`       | Criado no registro da empresa. Acesso completo. |
| `manager`     | Acesso completo (padrão da factory). |
| `colaborador` | Pode **lançar resíduos** (criar). Não edita, exclui, gera relatórios nem vê auditoria. |
| `auditor`     | Pode **aprovar relatórios** e **consultar a trilha de auditoria**. Não lança resíduos. |

As permissões são aplicadas via **Laravel Policies** nos controllers e Form Requests.

## Rotas da API

Base URL: `http://localhost:8080/api/v1`

### Públicas (sem token)

| Método | Rota        | Descrição |
|--------|-------------|-----------|
| POST   | `/register` | Cria empresa + usuário admin. Retorna token. |
| POST   | `/login`    | Autentica por e-mail/senha. Retorna token. |

### Autenticadas (`Authorization: Bearer {token}`)

| Método | Rota | Descrição | Perfis |
|--------|------|-----------|--------|
| POST | `/logout` | Revoga o token atual | Todos |
| GET | `/me` | Dados do usuário logado e empresa | Todos |
| GET | `/waste-records` | Lista resíduos da empresa | Todos |
| POST | `/waste-records` | Lança um resíduo | `admin`, `manager`, `colaborador` |
| GET | `/waste-records/{id}` | Detalhe de um resíduo | Todos |
| PATCH | `/waste-records/{id}` | Atualiza um resíduo | `admin`, `manager` |
| DELETE | `/waste-records/{id}` | Remove um resíduo | `admin`, `manager` |
| GET | `/carbon-reports` | Lista relatórios de carbono | Todos |
| POST | `/carbon-reports` | Cria relatório manualmente | `admin`, `manager` |
| GET | `/carbon-reports/{id}` | Detalhe de um relatório | Todos |
| POST | `/reports/generate` | Gera relatório de forma assíncrona | `admin`, `manager` |
| POST | `/carbon-reports/{id}/approve` | Aprova relatório (`completed` ou `generated`) | `admin`, `manager`, `auditor` |
| GET | `/audit-logs` | Lista trilha de auditoria | `admin`, `manager`, `auditor` |
| GET | `/audit-logs/{id}` | Detalhe de um registro de auditoria | `admin`, `manager`, `auditor` |

### Web

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/` | Health check da aplicação |

## Exemplos de payload

### Lançar resíduo

```bash
curl -X POST http://localhost:8080/api/v1/waste-records \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "waste_type": "recyclable",
    "quantity_kg": 150.5,
    "co2e_kg": 75.25,
    "occurred_at": "2026-07-05",
    "notes": "Coleta semanal"
  }'
```

Tipos de resíduo aceitos: `general`, `recyclable`, `organic`, `hazardous`, `electronic`.

### Gerar relatório de carbono

```bash
curl -X POST http://localhost:8080/api/v1/reports/generate \
  -H "Authorization: Bearer SEU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Relatório Q2 2026",
    "period_start": "2026-04-01",
    "period_end": "2026-06-30"
  }'
```

### Aprovar relatório (auditor)

```bash
curl -X POST http://localhost:8080/api/v1/carbon-reports/1/approve \
  -H "Authorization: Bearer SEU_TOKEN"
```

## Testes

```bash
docker compose exec app php artisan test
```

Ou, localmente:

```bash
php artisan test
```

Os testes usam SQLite em memória (`phpunit.xml`) e não exigem MySQL rodando.

## Estrutura principal

- `app/Models` — `Company`, `User`, `WasteRecord`, `CarbonReport`, `AuditLog`
- `app/Enums/UserRole.php` — perfis e regras de permissão
- `app/Policies` — autorização por recurso (`WasteRecord`, `CarbonReport`, `AuditLog`)
- `app/Scopes/CompanyScope.php` — isolamento multi-tenant por `company_id`
- `app/Http/Middleware/EnsureCompanyContext.php` — contexto de tenant nas rotas autenticadas
- `app/Services` — regras de negócio (resíduos, relatórios, auditoria)
- `database/migrations` — schema do banco
