# EcoTrack SaaS

MVP de uma plataforma B2B para gestão ambiental e cálculo de pegada de carbono, pensada para treinar Laravel, PHP, Docker, filas e multi-tenancy simplificado.

## O que este scaffold cobre

- Ambiente Docker com PHP-FPM, Nginx, MySQL e Redis.
- Modelo multi-tenant baseado em `company_id`.
- `CompanyScope` para filtrar automaticamente as consultas do tenant autenticado.
- Fluxo inicial de resíduos e relatórios de carbono.

## Estrutura principal

- `app/Models` contém `Company`, `User`, `WasteRecord` e `CarbonReport`.
- `app/Scopes/CompanyScope.php` aplica o filtro do tenant.
- `app/Http/Middleware/EnsureCompanyContext.php` trava as rotas autenticadas sem empresa.
- `app/Http/Controllers/Api` expõe endpoints mínimos para registrar resíduos e gerar relatórios.
- `database/migrations` modela o isolamento por empresa.

## Como subir localmente

1. Instale PHP 8.5 e Composer na sua máquina.
2. Rode `composer install`.
3. Copie `.env.example` para `.env` e ajuste a `APP_KEY`.
4. Suba os containers com `docker compose up -d --build`.
5. Execute as migrations depois que o projeto Laravel estiver instalado: `php artisan migrate`.

## Observação

Este repositório foi montado como base de estudo e documentação da arquitetura. O ambiente desta sessão não possui PHP nem Composer, então o scaffold foi criado sem executar a instalação completa das dependências.
