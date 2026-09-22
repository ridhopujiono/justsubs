# JustSubs Demo Application

This is a realistic showcase and demo application for the **JustSubs** Laravel package. 
It uses JustSubs as a local Composer dependency via path repository, representing a real consumer integration.

## Features

- Pre-configured `User` and `Company` (Polymorphic) models with `HasSubscriptions` trait.
- Minimalistic authentication setup for demo purposes.
- Pre-seeded realistic data:
  - 4 Subscription Plans (Starter, Basic, Pro, Business)
  - Users and Companies 
  - Dozens of Subscriptions in various states (Active, Expiring Soon, Expired, Cancelled)
  - Invoices (Paid, Unpaid, Void)
  - Manual Payments

## Requirements

- PHP 8.2+
- Composer
- SQLite (Default)

## Installation & Setup

1. **Install Dependencies**
   Run composer install in this directory.
   ```bash
   composer install
   ```

2. **Database & Seed**
   Run the migrations and the demo seeder. This will create a fresh SQLite database (`database/database.sqlite`) and populate it with realistic data.
   ```bash
   php artisan migrate:fresh --seed
   ```

3. **Run Application**
   Start the local development server:
   ```bash
   php artisan serve
   ```
   
   Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.
   
## Authentication

A default demo admin user is created during seeding:

- **Email**: `admin@justsubs.test`
- **Password**: `password`

You can click the "Open Dashboard" button on the landing page, which will automatically authenticate you as this admin user and redirect you to the JustSubs dashboard.

## Resetting Demo Data

If you need to reset the data back to its original state, simply re-run:

```bash
php artisan migrate:fresh --seed
```
