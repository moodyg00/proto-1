# APP-LAB

APP-LAB is a Laravel 12 application that combines multiple business domains in a single workspace. The current structure includes operational workflows, CRM, accounting, banking, content management, and administration surfaces, with both Filament admin resources and Inertia/Vue pages.

## Stack

- PHP 8.2+
- Laravel 12
- Filament 3
- Inertia.js with Vue 3
- Vite
- Laravel Horizon
- Sanctum
- Spatie Media Library
- Spatie Permission

## App Areas

- Operations: work orders, materials, contractor assignment, bookings, status changes, photo uploads
- CRM: leads and CRM dashboard
- Accounting: invoices, bills, payments, journal entries, reporting
- Banking: accounts, transactions, reconciliations, transfers
- Content: blog posts, pages, physical designs, assets, image files
- Administration: users, settings, products, services, inventory, change log

## Routing Overview

The default web entry redirects from `/` to `/operations/dashboard`.

Primary route groups:

- `/operations/*`
- `/crm/*`
- `/accounting/*`
- `/banking/*`
- `/content/*`
- `/administration/*`

## Local Setup

Install dependencies and build the frontend:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

If you want the built-in local development workflow:

```bash
composer run dev
```

That starts:

- the Laravel app server
- the queue listener
- the Laravel log tail
- the Vite dev server

## Useful Commands

```bash
php artisan test
php artisan migrate
php artisan queue:listen --tries=1 --timeout=0
php artisan horizon
npm run dev
npm run build
```

## Notes

- `.env` is ignored; use `.env.example` as the starting point for local configuration.
- `public/js/filament`, `public/css/filament`, and `public/vendor/livewire` are treated as generated or published assets and are ignored for this repo import.
- `bootstrap/cache/*.php` is ignored; cache placeholder `.gitignore` files remain tracked.

## License

This repository currently inherits the Laravel application skeleton license metadata in `composer.json` and should be reviewed if a project-specific license is required.
