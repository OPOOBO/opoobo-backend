# OPOOBO Backend (opoobo-backend) - Project Documentation

## Overview

The OPOOBO Backend is the **central authentication, user management, module linking, and API proxy** layer for the OPOOBO super-app platform. It serves as the hub connecting multiple services (Bus, Market, Go, Mall) under one unified account system.

**Framework:** Laravel 12 (PHP 8.2+) | **Version:** Latest | **URL:** `https://one.opoobo.com`

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Language | PHP 8.2+ (platform: 8.3) |
| Auth (API) | Keycloak SSO (JWT) + Laravel Sanctum |
| Auth (Web) | Session-based (Developer/Admin portals) |
| Database | MySQL (prod) / SQLite (dev) |
| Frontend | Vite 8 + Tailwind CSS 4 (minimal, server-rendered Blade) |
| JWT | firebase/php-jwt |
| Deployment | cPanel (with deploy.php helper) |

---

## Project Structure

```
opoobo-backend/
  app/
    Guards/KeycloakGuard.php        # Custom Keycloak JWT auth guard
    Http/
      Controllers/
        AdminController.php          # Admin web dashboard
        DeveloperController.php      # Developer portal
        MiniAppController.php        # Mini-app management
        Api/V1/                      # REST API controllers (14 files)
      Middleware/                     # AdminAuth, DeveloperAuth
      Requests/                      # Form request validation
      Resources/                     # API resource transformers
    Models/                          # 10 Eloquent models
    Services/KeycloakService.php     # OIDC discovery, JWKS, JWT verification
  config/
    keycloak.php                     # Keycloak SSO config
    miniapp.php                      # Admin email whitelist
    services.php                     # Flutterwave keys
  database/
    schema.sql                       # Full MySQL DDL (15 tables)
    migrations/                      # 24 migration files
    seeders/                         # Seeds 4 core modules + test user
  resources/views/                   # Blade views (admin + developer portals)
  routes/
    api.php                          # REST API (prefix /api/v1)
    web.php                          # Web routes (developer + admin portals)
  public/deploy.php                  # cPanel deployment helper
```

---

## What Has Been Done

### Authentication System (Dual)
1. **Keycloak SSO (Mobile API)** - Custom JWT auth guard
   - OIDC discovery with file-based caching
   - JWKS keys cached with configurable TTL
   - Bearer token verification with issuer + audience validation
   - Auto-provisioning on first SSO login
   - Auto-linking existing email accounts
2. **Traditional Auth (Web portals)** - Sanctum tokens + session-based login

### User Management
- UUID-based `opoobo_id` for users
- Membership tiers: basic, silver, gold, platinum
- User status: active, suspended, deactivated
- Profile management, avatar, initials

### Module/Service System (Super App Core)
- Module model represents services (bus, market, go, mall) or third-party mini-apps
- Module linking with email check, credential verification, account creation
- SSO auto-link via Keycloak id_token
- Password propagation to linked modules
- 4 seeded modules: Bus, Market, Go, Mall

### Market Proxy
- Backend acts as reverse proxy to `https://app.opoobo.market/api`
- Token minting via SSO for market authentication
- 20+ proxied endpoints (home, items, chat, payments, etc.)

### Mini-App Store
- Public store browse with filtering/search
- Developer submission workflow
- Version checking, install counting
- Compliance badges, preflight checks

### Developer Portal (Web)
- Registration, login, dashboard
- Submit/edit/withdraw mini-apps
- Documentation and demo pages

### Admin Dashboard (Web)
- Review workflow: approve, reject, feature, delete
- Preflight checks (SSL, URL loading, load time)
- Module reordering

### Payment & Financial
- Flutterwave integration (save cards, tokenized payments, verify)
- Payment methods CRUD with default selection

### Security
- Login history tracking (device, IP, location)
- Session revocation
- Address and saved location management

---

## What Can Be Added

### High Priority
1. **API rate limiting** - Add throttling middleware for all API endpoints
2. **Audit logging** - Track all write operations for compliance
3. **Webhook system** - Notify external services on module events
4. **Two-factor authentication** - Add 2FA for admin/developer portals
5. **API versioning strategy** - Plan for v2 endpoints

### Medium Priority
6. **Notification service** - Unified push notification dispatch across modules
7. **Analytics API** - Aggregate usage data across all modules
8. **Module health monitoring** - Uptime/latency checks for linked modules
9. **Bulk operations** - Batch module linking/unlinking for users
10. **GraphQL API** - Alternative API layer for complex queries

### Low Priority
11. **Role-based access control** - Granular permissions for admin dashboard
12. **API documentation** - OpenAPI/Swagger spec generation
13. **Caching layer** - Redis for hot data (settings, modules, user profiles)
14. **Event sourcing** - Track state changes for critical operations
15. **Internationalization** - Multi-language support for portals

---

## How to Add New Features

### Adding a New API Endpoint
1. Create controller in `app/Http/Controllers/Api/V1/`
2. Add form request validation in `app/Http/Requests/`
3. Register route in `routes/api.php`
4. Add API resource transformer if needed in `app/Http/Resources/`
5. Test with: `php artisan test`

### Adding a New Module
1. Create migration for module record: `php artisan make:migration add_xxx_module`
2. Seed the module in `database/seeders/DatabaseSeeder.php`
3. Add module-specific controllers if needed
4. Register module linking logic in `ModuleController.php`
5. Run: `php artisan migrate --force && php artisan db:seed`

### Adding a New Model
1. Create model: `php artisan make:model ModelName -mrc`
2. Define relationships, casts, fillable attributes
3. Create migration, run it
4. Add controller and routes

### Adding a New Web Page
1. Create Blade view in `resources/views/your_section/`
2. Add route in `routes/web.php`
3. Add controller method
4. Use existing layout from `resources/views/layouts/app.blade.php`

### Modifying Authentication
1. Edit `app/Guards/KeycloakGuard.php` for JWT changes
2. Edit `app/Services/KeycloakService.php` for OIDC changes
3. Edit `config/keycloak.php` for configuration
4. Test with both mobile (JWT) and web (session) auth flows

---

## Build & Run

```bash
# Setup (installs all deps, generates key, migrates, seeds)
composer setup

# Development (starts server, queue, logs, Vite concurrently)
composer dev

# Individual commands
php artisan serve                    # Start dev server
npm run dev                          # Start Vite dev server
npm run build                        # Build production assets
php artisan migrate --force          # Run migrations
php artisan db:seed                  # Seed database
php artisan storage:link             # Create storage symlink

# Testing
composer test
```

### cPanel Deployment
1. Upload zip to cPanel, extract
2. Edit `.env` via File Manager
3. Visit `https://one.opoobo.com/deploy.php?token=TOKEN&action=run&migrate=1`
4. Delete `deploy.php` immediately

---

## Environment Variables

| Variable | Purpose |
|----------|---------|
| `APP_URL` | Base URL (`https://one.opoobo.com`) |
| `KEYCLOAK_SERVER_URL` | Keycloak server (`https://account.opoobo.com`) |
| `KEYCLOAK_REALM` | Keycloak realm (`opoobo`) |
| `KEYCLOAK_CLIENT_ID` | Keycloak client (`opoobo-mobile`) |
| `FLW_SECRET_KEY` | Flutterwave secret key |
| `DB_HOST/DB_DATABASE/DB_USERNAME/DB_PASSWORD` | MySQL credentials |
| `MINIAPP_ADMIN_EMAILS` | Admin email whitelist |

---

## Database Schema (15 tables)

`users`, `modules`, `module_users`, `developers`, `addresses`, `saved_locations`, `payment_methods`, `flutterwave_authorizations`, `login_history`, `mini_app_reviews`, `cache`, `jobs`, `failed_jobs`, `personal_access_tokens`, `password_reset_tokens`
