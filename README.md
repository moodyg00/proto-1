# Proto-1 — Business Operations Suite

A unified, modern Laravel application that merges custom **APP-LAB** architecture with proven UI patterns from erpsaas (Accounting) and Krayin Laravel CRM.

## Project Goal

Create a single, cohesive business platform with:
- APP-LAB’s clean header, sidebar, and color scheme across the entire app
- Full custom database schema (Operations, CRM, Accounting, Banking, Marketing, Content, etc.)
- High-performance Filament + Inertia/Vue interface
- Column selector on all tables
- AI-agent ready archetechure 

## Tech Stack

- **PHP 8.2+** & **Laravel 12**
- **Filament 3** (Admin Panel)
- **Inertia.js + Vue 3**
- **Vite**
- MySQL / PostgreSQL
- Spatie Permission, Media Library, Horizon, Sanctum

## Main Modules

| Module                | Status     | UI Style                  |
|-----------------------|------------|---------------------------|
| Operations            | In Progress| custom |
| Accounting            | In Progress| erpsaas|
| Customer Relations    | In Progress| Krayin |
| Banking               | In Progress| erpsaas|
| Marketing & Ads       | In Progress| Custom |
| Content & Blog        | In Progress| Custom |
| AI Tools              | In Progress| Custom |
| Integrations          | In Progress| Custom |
| Administration        | In Progress| Nested menu |

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build