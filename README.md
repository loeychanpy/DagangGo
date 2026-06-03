# DagangGo — Point of Sale System

A web-based Point of Sale (POS) application built with Laravel 12, designed for small retail businesses. Supports cash and credit transactions, inventory management, delivery notes, financial reporting, and a full audit trail.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 · Laravel 12 |
| Frontend | Blade · Tailwind CSS v3 · Alpine.js |
| Build tool | Vite |
| Database | MySQL |
| PDF generation | barryvdh/laravel-dompdf |
| Fonts | Hanken Grotesk · JetBrains Mono |

## Features

### POS / Transaksi
- Product search and category filter
- Add/remove items from cart with quantity control
- Discount input
- Payment methods: **Cash**, **Transfer**, **QRIS**, **Tempo (kasbon)**
- Tempo transactions track due date and remaining bill per customer
- Upload payment proof photo for transfer/QRIS
- Print invoice PDF immediately after checkout

### Inventory
- Full product CRUD (name, SKU, category, unit, purchase price, selling price, minimum stock)
- Stock badge: green (normal) · amber (low) · red (out of stock)
- Low stock count badge in the navbar, visible on every page
- Search by name and filter by category with auto-submit

### Laporan (Financial Report)
- Date range filter
- KPI cards: Total Omzet, Total Uang Masuk, Total Piutang
- Export full report as PDF or CSV
- Per-transaction invoice PDF
- Record partial or full payment for outstanding tempo bills
- Payment proof upload (transfer/QRIS)

### Surat Jalan (Delivery Note)
- Create delivery data per transaction (shipping address, driver name, license plate)
- Print delivery note in a clean printable view

### Audit Log
- Tracks key system actions: new transactions, product changes, user management, payment recording
- Visible to owner role only

### User Management
- Owner can create staff accounts and assign roles (owner / staff)
- Role-based access: staff can access POS and view laporan; owner has full access

### Dashboard
- Daily, weekly, monthly, and custom date range filter
- KPI cards: sales, transactions, outstanding debt
- Sales bar chart per day
- Low stock product list

## Roles

| Feature | Staff | Owner |
|---|:---:|:---:|
| POS / Transaksi | ✅ | ✅ |
| Inventory | ✅ | ✅ |
| Laporan | ✅ | ✅ |
| Audit Log | ❌ | ✅ |
| User Management | ❌ | ✅ |
| Profile Settings | ❌ | ✅ |

## Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18 & npm
- MySQL

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/loeychanpy/MaterialPOS.git
cd MaterialPOS

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate
```

## Configuration

Edit `.env` with your database credentials:

```env
APP_NAME=DagangGo
APP_URL=http://localhost/MaterialPOS/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file

FEATURE_KASBON=true
FEATURE_DELIVERY=true
```

> **FEATURE_KASBON** — enables the Tempo payment method and credit limit per customer.
> **FEATURE_DELIVERY** — enables Surat Jalan creation and printing.

## Database Setup

```bash
# Run migrations
php artisan migrate

# (Optional) Seed with dummy data
php artisan db:seed --class=DummyPOSSeeder
```

## Build & Run

```bash
# Build frontend assets
npm run build

# Or use hot-reload during development
npm run dev
```

Then open the app at `http://localhost/MaterialPOS/public`.

## Database Schema

| Table | Description |
|---|---|
| `users` | System users with role (owner / staff) |
| `categories` | Product categories |
| `units` | Units of measurement (pcs, kg, box, etc.) |
| `products` | Product catalog with stock and pricing |
| `customers` | Customer records with credit limit |
| `transactions` | Transaction header (invoice, total, status) |
| `transaction_details` | Line items per transaction |
| `transaction_payments` | Payment records for partial/full settlement |
| `stock_movements` | Stock in/out history per product |
| `deliveries` | Delivery data linked to transactions |
| `audit_logs` | Action audit trail per user |

## Project Structure

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── TransactionController.php
│   ├── ProductController.php
│   ├── LaporanController.php
│   ├── DeliveryController.php
│   ├── CustomerController.php
│   ├── UserController.php
│   └── AuditLogController.php
├── Models/
resources/
├── js/
│   ├── app.js
│   ├── transaction.js      # POS cart logic
│   ├── laporan.js          # Payment & delivery modals
│   ├── dashboard.js        # Chart & period filter
│   └── inventory.js        # Search auto-submit
├── views/
│   ├── layouts/
│   ├── transaction/
│   ├── inventory/
│   ├── laporan/
│   ├── deliveries/
│   ├── audit-log/
│   └── users/
routes/
└── web.php
```

## License

MIT
