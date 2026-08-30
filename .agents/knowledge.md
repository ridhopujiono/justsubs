# JustSubs Knowledge Base

## Product Identity
Name: JustSubs
Tagline: Subscription Management for Laravel
Composer package: ridho/justsubs
Root namespace: Ridho\JustSubs
Artisan namespace: justsubs:
Config namespace/file: justsubs / config/justsubs.php
View namespace: justsubs::
Route name prefix: justsubs.

## Product Goal
JustSubs adalah reusable Laravel subscription-management package dengan dashboard bawaan dan payment-provider-agnostic architecture.

## Non Goals
Fitur berikut **belum menjadi tujuan V1**:
- Stripe integration
- Paddle integration
- Midtrans integration
- Xendit integration
- Duitku integration
- automatic card recurring charge
- tax engine
- coupon engine
- complex proration
- seat-based billing
- usage-based billing
- multi-currency conversion

Fitur-fitur ini tidak akan dikerjakan sebelum masuk roadmap secara resmi.

## Architecture Principles
- **Package Standalone:** Harus bisa berfungsi sebagai Composer package mandiri.
- **Provider Agnostic:** Core domain tidak bergantung pada satu payment gateway tertentu.
- **Business Logic Isolation:** Business logic berada di luar Controller/Views (harus di service/domain layer).
- **Polymorphic Subscriber:** Subscriber tidak boleh di-hardcode ke `App\Models\User`.
- **No Hardcoded Admin Role:** Otorisasi dashboard diserahkan ke host application.
- **Prefixed Tables:** Semua tabel menggunakan prefix `justsubs_` untuk mencegah bentrok.
- **Integer Monetary Amounts:** Uang selalu disimpan sebagai integer (tidak pernah float).
- **Database Transactions:** Digunakan untuk operasi yang melibatkan multi-write agar atomic.
- **Event-Driven:** Menyediakan event pada titik-titik krusial lifecycle.
- **Minimal Dependencies:** Jangan menambahkan library eksternal tanpa alasan kuat.
- **Independent UI Bundle:** UI Dashboard di-bundle secara mandiri, tidak bergantung pada frontend stack aplikasi host.
- **Automated Tests Mandatory:** Setiap fungsionalitas core wajib memiliki test.

## Naming Conventions
**Tabel Database:**
- justsubs_plans
- justsubs_subscriptions
- justsubs_invoices
- justsubs_payments

**Artisan Commands:**
- justsubs:install
- justsubs:status

## Domain Model
Domain utama: Plan, Subscription, Invoice, Payment.

```text
Subscriber (Polymorphic)
   │
   ├── Subscriptions ── Plan
   │
   └── Invoices ── Payments
```

## Subscriber Strategy
Subscriber bersifat polymorphic:
- `subscriber_type`
- `subscriber_id`

Subscriber dapat berupa: User, Company, Organization, atau custom Eloquent model dari host application. Tipe ID juga harus fleksibel (int, bigint, uuid, ulid).

## Subscription Rules
- **Active status:** Ditentukan oleh kolom `status` ditambah validasi date window (`starts_at`, `ends_at`).
- **Active Renewal:** Harus menambah periode dari `ends_at` yang sudah ada, bukan dari `now()`.
- **Expired Renewal:** Behavior harus eksplisit (UNDECIDED).
- **Plan Interval:** Menggunakan kombinasi `interval_count` dan `interval_unit` (misal: 1 month).
- **Cancellation Semantics:** Harus terdokumentasi saat implementasinya dikunci (UNDECIDED).
- **Lifecycle Actions:** Aksi harus berada di service/domain layer.

## Billing Rules
- **Amount:** Selalu disimpan sebagai integer.
- **Currency:** Menyimpan kode ISO (misal: IDR, USD).
- **Separation:** Invoice terpisah dari Subscription, Payment terpisah dari Invoice.
- **Payment Confirmation:** Dapat mengaktifkan atau melakukan renew pada Subscription.
- **Metadata:** Provider-specific payload (seperti respons JSON dari payment gateway) masuk ke kolom `metadata`.
- **Idempotency:** Pemrosesan duplicate payment / webhook harus bisa ditangani secara idempotent.

## Dashboard Rules
- **Stack:** Berbasis Blade dan Tailwind CSS. Alpine.js hanya digunakan jika sangat diperlukan.
- **Independence:** Tidak bergantung pada frontend stack/Vite dari host application.
- **Configurable:** Prefix route dan middleware dapat diatur melalui konfigurasi host.
- **Authorization:** Host application harus dapat mengonfigurasi gate/authorization. Production dashboard tidak boleh terbuka secara default.

## Testing Rules
- **Mandatory:** Test wajib untuk setiap operasi domain.
- **Time Travel:** Gunakan simulasi waktu (time travel/freezing) untuk menguji lifecycle subscription.
- **Package Test Environment:** Gunakan pendekatan khusus testing package (seperti Orchestra Testbench).
- **No Internet Dependency:** Test tidak boleh membutuhkan koneksi internet.
- **Regression Tests:** Setiap bug fix harus menyertakan regression test jika memungkinkan.

## Compatibility
- PHP >= 8.2
- Laravel 11–13
*(Catatan: Maintainability lebih diutamakan daripada membuat compatibility hack yang buruk.)*

## Current Development Stage
Current Stage: Stage 10 — Payment Domain & Manual Driver
Status: Completed
Last Completed: PaymentDriver contract, ManualPaymentDriver, Payment model & migrations, transaction workflows in BillingManager, idempotency via PaymentFailedException.
Next Planned: Stage 11

## Architecture Decision Log

| Date | Decision | Reason |
|---|---|---|
| 2026-08-29 | JustSubs dipilih sebagai nama package. | Nama singkat, jelas, dan branding yang kuat. |
| 2026-08-29 | Tables menggunakan prefix `justsubs_`. | Mencegah collision dengan package lain (seperti Cashier). |
| 2026-08-29 | Subscriber menggunakan polymorphic relation. | Fleksibilitas untuk host app yang menggunakan User, Company, dll. |
| 2026-08-29 | Core tidak bergantung payment provider. | Memungkinkan ekstensi dengan berbagai provider (atau manual transfer). |
| 2026-08-29 | Dashboard merupakan bagian resmi package. | Fitur inti, bukan sekadar playground/demo. |
| 2026-08-29 | UI tidak boleh bergantung frontend build host application. | Menjamin package bisa dipasang di project apa saja tanpa merusak stack mereka. |
| 2026-08-29 | Menggunakan PHPUnit untuk testing. | Standar komunitas, kompatibilitas bawaan dengan Orchestra Testbench. |
| 2026-08-29 | Menggunakan native PHP Enum untuk `IntervalUnit`. | Memberikan type-safety dan validasi otomatis saat query/penyimpanan ke model Plan. |
| 2026-08-29 | Kolom `subscriber_id` menggunakan tipe `string` (bukan unsignedBigInteger). | Memastikan dukungan default untuk integer, UUID, dan ULID tanpa perlu mengubah konfigurasi migration. |
| 2026-08-29 | Melarang overlapping active subscriptions. | `SubscriptionManager::subscribe` melempar `AlreadySubscribedException` jika dicoba pada subscriber yang masih aktif. Menjaga state tetap sederhana. |
| 2026-08-29 | Renewal behavior. | Active renew = `ends_at` + plan_interval. Expired renew = starts dari `now()`. Mencegah hilangnya sisa periode. |
| 2026-08-29 | Cancel semantics. | Graceful (`cancel(immediately: false)`) tetap active hingga `ends_at` tetapi merekam `cancelled_at`. Immediate (`cancel(immediately: true)`) mengganti status ke Cancelled dan mengubah `ends_at` ke `now()`. |
| 2026-08-29 | Status Expired. | Status tidak kadaluarsa secara magis. Fungsi `markAsExpired` dipanggil (misalnya via job/cron) untuk materialisasi status Expired, tapi method `active()` tetap selalu akurat berdasarkan time window. |
| 2026-08-29 | Change Plan behavior V1. | Pindah plan langsung memutus plan lama, memulai plan baru dari `now()` tanpa proration balance otomatis, demi menjaga kompleksitas V1 tetap rendah. |
| 2026-08-29 | Dashboard Authorization Pattern. | Otorisasi diserahkan pada Closure kustom melalui facade `JustSubs::auth()`. Secara default (jika callback tidak di-set), dashboard tertutup (403), kecuali di environment `local`. Ini menghindari expose tidak sengaja di production. |
| 2026-08-29 | Subscriber Resolver Strategy. | Daripada menebak atau menggunakan Dynamic SQL untuk melacak `name`/`email` dari Polymorphic Subscriber, kita menyediakan Public API: `JustSubs::resolveSubscriberNameUsing(Closure)`. Default *fallback*-nya mengecek object property `name` atau `email`. |
| 2026-08-30 | Collision-safe Invoice Numbering. | Nomor Invoice di-*generate* secara dinamis di model event `created` dengan format `INV-{YYYYMM}-{ID}` untuk menjamin `100% collision-safe` (karena `$id` dijamin unik di database). |
| 2026-08-30 | Payment Driver Contract. | Domain Payment dibuat independen (`PaymentDriver`) tanpa hard-code Midtrans/Stripe. Operasi pembayaran diletakkan di `BillingManager` dengan jaminan transaksi (`DB::transaction`) dan pencegahan duplikasi bayar (idempotency throw `PaymentFailedException`). |

## Updating This File
File `.agents/knowledge.md` ini HANYA diperbarui untuk keputusan yang memiliki dampak lintas tahap atau keputusan arsitektur jangka panjang.
**DILARANG** menaruh:
- Progress detail kecil
- Temporary debugging note
- Todo harian
- Output test panjang
