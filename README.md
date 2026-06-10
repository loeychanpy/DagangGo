# dagangGo — Point of Sale System

**dagangGo** (*Digitalization of Assets, Goods, and General Network for Growth Operations*) is a modern, web-based Point of Sale (POS) application built with Laravel 12. Designed specifically to support the digitalization of Micro, Small, and Medium Enterprises (UMKM).

Employing a **Retail-Agnostic** approach, dagangGo is highly adaptable to various types of retail commodities. It features a scalable architecture with a **Feature Toggle** system and implements a **Cash-Based Profit Calculation** method to ensure highly accurate financial reporting.

## 👥 Development Team

Developed by Informatics undergraduate students at Universitas Kristen Krida Wacana (UKRIDA):

- **Janisha Jaya** — Project Manager & Backend Engineer
- **Josh Valentino** — Frontend & Backend Developer
- **Richard Devin Sutisna** — Creative Director
- **Alejandro Julian Mac Athur Simanjuntak** — Field Researcher

## 🚀 Core Features

### POS / Transaksi (Automated Cashier)
- Product search and category filter
- Dynamic cart with quantity control and discount input
- Payment methods: **Cash**, **Transfer**, **QRIS**, and **Tempo (Kasbon)**
- Tracks due dates and remaining bills per customer for Tempo transactions
- Upload payment proof photo for transfer/QRIS
- Print invoice PDF immediately after checkout

### Inventory (Real-Time Stock Movement)
- Full product CRUD (name, SKU, category, unit, purchase price, selling price, minimum stock)
- Automated stock reduction and ledger recording upon successful checkout
- Intuitive stock badges: green (normal) · yellow (low) · red (out of stock)
- Low stock count badge in the navbar, visible on every page
- Search by name and filter by category with auto-submit

### Dashboard
- Daily, weekly, monthly, and custom date range filter
- KPI cards: sales, transactions, outstanding debt
- Sales bar chart per day
- Low stock product list

### Laporan (Financial Report)
- Date range filter with KPI cards: Total Omzet, Total Uang Masuk, Total Piutang
- Cash-Based Profit Calculation separating paid revenue from unpaid debts
- Export full report as PDF or CSV
- Record partial or full payment for outstanding tempo bills

### Surat Jalan & Audit Log
- **Surat Jalan:** Create delivery data per transaction (shipping address, driver name, license plate) and print delivery notes
- **Audit Log:** Tracks key system actions (new transactions, product changes, user management, and payment logging) — exclusively visible to the Owner

### User Management
- Owner can create staff accounts and assign roles (owner / staff)
- Role-based access: staff can access POS , Dashboard and Inventory; owner has full access

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 · Laravel 12 |
| Frontend | Blade Templating · Tailwind CSS v3 · Alpine.js |
| Build tool | Vite |
| Database | MySQL |
| PDF generation | barryvdh/laravel-dompdf |
| Fonts | Hanken Grotesk · JetBrains Mono |

## ⚙️ Installation & Setup

**Requirements:** PHP >= 8.2, Composer, Node.js >= 18 & npm, MySQL.

```bash
# 1. Clone the repository
git clone https://github.com/loeychanpy/DagangGo.git
cd DagangGo

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Link storage directory (required for image uploads)
php artisan storage:link

# 5. Database setup (configure .env DB credentials first, then run)
php artisan migrate
php artisan db:seed --class=UserSeeder

# 6. Build frontend assets
npm run build

# 7. Start the server
php artisan serve
```

## 🔧 Configuration

Edit `.env` with your database credentials and feature flags:

```env
APP_NAME=DagangGo
APP_URL=http://localhost/DagangGo/public

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

## 🎛️ Feature Toggle

| Flag | Default | Description |
|---|:---:|---|
| `FEATURE_KASBON` | `true` | Enables Tempo payment method and credit tracking. Set to `false` for strict Cash & Carry stores. |
| `FEATURE_DELIVERY` | `true` | Enables Surat Jalan (Delivery Note) creation and printing. |

## 🔒 User Roles

| Feature | Staff (Kasir) | Owner |
|---|:---:|:---:|
| Dashboard | ✅ | ✅ |
| POS / Transaksi | ✅ | ✅ |
| Inventory | ✅ | ✅ |
| Laporan | ❌ | ✅ |
| Audit Log | ❌ | ✅ |
| User Management | ❌ | ✅ |
| Profile Settings | ❌ | ✅ |

## 🗄️ Database Schema

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

## 📁 Project Structure

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

## 📄 License

MIT
