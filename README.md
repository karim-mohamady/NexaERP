# NexaERP

NexaERP is a portfolio-ready enterprise ERP MVP built with Laravel 12, Sanctum, Spatie Permission, React, TypeScript, Vite, Tailwind CSS, Recharts, Axios, and lucide-react.

## Features

- Sanctum API authentication: login, register, logout, profile update, change password, forgot/reset password endpoints.
- Role-based access foundation with Super Admin, Admin, Manager, Accountant, HR Manager, Sales Manager, Inventory Manager, Employee, and Viewer.
- Multi-company and branch-aware schema.
- ERP modules for CRM, sales, purchases, inventory, HR, accounting, reports, settings, and AI insights.
- REST API endpoints under `/api`.
- React dashboard with KPI cards, charts, low stock alerts, recent invoices/customers, AI panel, dark/light theme, and English/Arabic direction support.
- Demo seed data for one company, two branches, all roles, customers, suppliers, products, warehouses, invoices, payments, employees, expenses, accounts, and settings.

## Demo Account

- Email: `admin@nexaerp.com`
- Password: `password`

## Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The default `.env.example` is MySQL-oriented. For quick local testing you can set `DB_CONNECTION=sqlite` and create `database/database.sqlite`.

## Frontend Setup

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Set `VITE_API_BASE_URL=http://localhost:8000/api` when the Laravel server runs on port 8000.

## AI Configuration

No paid API key is committed. Configure these only in your local environment:

```env
OPENAI_API_KEY=
AI_PROVIDER=openai
```

Without a key, `/api/ai/insights` returns smart mock insights based on ERP database data.

## API Overview

Important endpoints include:

- `POST /api/auth/login`
- `POST /api/auth/register`
- `POST /api/auth/logout`
- `GET /api/auth/me`
- `GET /api/dashboard/summary`
- CRUD: `/api/customers`, `/api/leads`, `/api/suppliers`, `/api/products`, `/api/warehouses`, `/api/stock-movements`, `/api/invoices`, `/api/payments`, `/api/purchase-orders`, `/api/employees`, `/api/attendance`, `/api/payrolls`, `/api/accounts`, `/api/journal-entries`, `/api/expenses`
- Reports: `/api/reports/sales`, `/api/reports/purchases`, `/api/reports/inventory`, `/api/reports/profit-loss`
- AI: `/api/ai/insights`, `/api/ai/analyze`
- Settings: `/api/settings`

## Deployment Notes

- Use MySQL or PostgreSQL-compatible schema settings in production.
- Set real `APP_KEY`, mail credentials, queue worker, scheduler, cache, and HTTPS CORS/Sanctum domains.
- Keep API keys and credentials in environment variables only.
- Add module-specific policies and deeper form request validation before production SaaS launch.

## Future Improvements

- Dedicated quotation/order/invoice item builders.
- PDF rendering pipeline for invoices and purchase orders.
- Full audit log hooks per model.
- Live OpenAI provider integration behind the existing AI service boundary.
- Advanced exports for reports and accounting statements.