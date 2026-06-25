# RentFlow - Multi-Tenant Property Management System

A complete property management platform with strict data isolation per owner.

## Tech Stack

| Layer    | Technology                             |
| -------- | -------------------------------------- |
| Frontend | HTML + Tailwind CSS + Vanilla JS (SPA) |
| Backend  | PHP 8.4 (No framework)                 |
| Database | MySQL/MariaDB via MySQLi               |
| Auth     | JWT (HS256)                            |

## Project Structure

```
RentFlow/
├── backend/
│   ├── app/
│   │   ├── Controllers/     # 11 REST controllers
│   │   ├── Core/            # Database, JWT, Router, Env
│   │   └── Middleware/       # Auth middleware
│   ├── config/               # Database & JWT config
│   ├── database/
│   │   ├── migrations/       # SQL schema (10 tables)
│   │   └── seeders/          # Demo data seeder
│   └── public/               # API entry point
├── frontend/
│   ├── public/               # SPA entry (index.php)
│   ├── src/
│   │   ├── css/input.css     # Tailwind source
│   │   └── js/api.js         # API client
├── .env                      # Environment variables
├── .gitignore
├── .env.example
├── package.json              # Tailwind build
├── tailwind.config.js
└── postcss.config.js
```

## Quick Start

### 1. Database Setup

```bash
# Create database
mysql -u root -h 127.0.0.1 -e "CREATE DATABASE rentflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# Run migrations
php backend/database/migrate.php

# Seed demo data
php backend/database/seeders/seed.php
```

### 2. Start Development Server

```bash
# Option A: PHP Built-in Server
php -S localhost:8080 -t backend/public

# Option B: Apache (XAMPP)
# Configure vhost to point to backend/public/
```

### 3. Test Login

```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"owner@rentflow.co","password":"admin123"}'
```

### 4. Frontend

```bash
# Build Tailwind CSS
npm run build

# Or watch during development
npm run watch
```

## API Endpoints

### Public

- `POST /api/auth/register` - Create account
- `POST /api/auth/login` - Login → JWT token

### Protected (Require Bearer Token)

- `GET /api/auth/me` - Profile
- `GET /api/dashboard` - Stats
- CRUD: `/api/properties`, `/api/houses`, `/api/tenants`
- `/api/payments`, `/api/bills`, `/api/complaints`
- `/api/communications`, `/api/caretakers`
- `/api/bills/generate`, `/api/reports`

## Multi-Tenant Isolation

Every table has `owner_id`. All queries include `WHERE owner_id = ?` via the authenticated JWT token. Registering a new owner creates an isolated workspace with default communication templates.

## Demo Credentials

- **Owner:** owner@rentflow.co / admin123
