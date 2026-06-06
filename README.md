# TreadMart — Multi-Vendor Tire Marketplace
## Final MVP Reference Document
**Stack:** Laravel 12 · Livewire · Tailwind CSS · MySQL · Shared Hosting  
**Version:** 1.0 · June 2026

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [User & Access Management](#2-user--access-management)
3. [Vendor Management](#3-vendor-management)
4. [Product Management](#4-product-management)
5. [Tire Search](#5-tire-search)
6. [Vehicle Fitment](#6-vehicle-fitment)
7. [Shopping Cart](#7-shopping-cart)
8. [Order Management](#8-order-management)
9. [Payment Management](#9-payment-management)
10. [Commission & Revenue](#10-commission--revenue)
11. [Review & Rating System](#11-review--rating-system)
12. [Product Request Module](#12-product-request-module)
13. [Trust & Safety](#13-trust--safety)
14. [Reporting & Analytics](#14-reporting--analytics)
15. [Notification System](#15-notification-system)
16. [Infrastructure & Deployment](#16-infrastructure--deployment)
17. [Core MVP Feature Checklist](#17-core-mvp-feature-checklist)

---

## 1. Project Overview

TreadMart is a multi-vendor online tire marketplace for Kenya, enabling independent vendors to sell new and used tires through a single unified platform. Customers can search by tire size or vehicle fitment, order from multiple vendors in a single checkout, and pay via M-Pesa Express or manual payment methods.

### Platform Supports

- New tire sales
- Used tire sales with condition verification
- Multi-vendor operations with independent order tracking
- Vehicle fitment search (Make → Model → Year → compatible sizes)
- Tire size search (e.g. `225/45R17`)
- M-Pesa Express (STK Push) payments
- Manual payment verification (M-Pesa transfer, bank transfer)
- Vendor and product approval workflows

---

## 2. User & Access Management

### 2.1 Authentication

| Feature | Detail |
|---|---|
| Registration | Email + password; customer or vendor role selection |
| Login | Session-based (web) · Token-based (API via Sanctum) |
| Password reset | Email link with expiring token |
| Remember me | Persistent session cookie |
| Profile management | Name, email, phone, address, avatar |

### 2.2 Roles

#### Admin
Full system access — approves vendors, approves products, manages commissions, resolves disputes, accesses all reports.

#### Vendor
- Product management (own products only)
- Order management (own order items only)
- Sales and revenue reports (own data only)

#### Customer
- Browse and search products
- Place orders and track shipments
- Submit product and vendor reviews
- Submit product requests for unavailable tires

### 2.3 Permissions (Spatie Laravel Permission)

| Permission | Admin | Vendor | Customer |
|---|---|---|---|
| `vendors.approve` | ✅ | ❌ | ❌ |
| `products.approve` | ✅ | ❌ | ❌ |
| `products.create` | ✅ | Own only | ❌ |
| `products.delete` | ✅ | Own only | ❌ |
| `orders.view_all` | ✅ | Own items | Own orders |
| `payments.verify` | ✅ | ❌ | ❌ |
| `reports.admin` | ✅ | ❌ | ❌ |
| `reports.vendor` | ✅ | Own only | ❌ |
| `reviews.moderate` | ✅ | ❌ | Create only |
| `disputes.mediate` | ✅ | Raise only | Raise only |

---

## 3. Vendor Management

### 3.1 Vendor Registration

Vendors complete an application form after registering a user account.

**Required fields:**

| Field | Notes |
|---|---|
| Store name | Must be unique across platform |
| Business description | Min 50 characters |
| Phone number | Kenyan format: `+254XXXXXXXXX` |
| Email address | Used for notifications |
| Logo | JPG/PNG, max 1MB |
| Store banner | JPG/PNG, max 2MB, recommended 1200×400px |

Status on submission: `pending` — vendor cannot list products until approved.

### 3.2 Vendor Approval Workflow

```
Vendor submits application
        ↓
Admin reviews in approval queue
        ↓
    ┌───────────────┐
    │  Approve       │ → status = approved → vendor notified → can list products
    │  Reject        │ → status = rejected → vendor notified with reason
    │  Suspend       │ → status = suspended → products hidden → vendor notified
    └───────────────┘
```

### 3.3 Vendor Dashboard Summary

| Widget | Description |
|---|---|
| Product statistics | Total listed, pending approval, out of stock, archived |
| Order statistics | Pending, processing, shipped, delivered counts |
| Revenue summary | Today, this week, this month, total earned |
| Inventory overview | Stock levels, low-stock alerts (threshold: < 5 units) |

---

## 4. Product Management

### 4.1 New Tire Fields

| Field | Type | Required |
|---|---|---|
| Brand | FK → brands | ✅ |
| Model / Name | String | ✅ |
| Width | Integer (e.g. 225) | ✅ |
| Aspect ratio | Integer (e.g. 45) | ✅ |
| Rim diameter | Integer (e.g. 17) | ✅ |
| Season | Enum: summer, winter, all_season, all_terrain | ✅ |
| Load index | String (e.g. 91) | ✅ |
| Speed rating | String (e.g. V) | ✅ |
| Price (KSh) | Decimal | ✅ |
| Sale price (KSh) | Decimal | Optional |
| Stock quantity | Integer | ✅ |
| SKU | String, unique per vendor | Optional |
| Weight (kg) | Decimal — used for shipping calc | Optional |
| Specifications | JSON — fuel efficiency, wet grip, noise dB | Optional |

> **Tire size composite index:** A database index on `(width, aspect_ratio, rim_diameter)` is required for fast size search performance.

### 4.2 Used Tire Additional Fields

| Field | Type | Notes |
|---|---|---|
| Condition | Enum: good, fair, poor | Required for used tires |
| Tread depth (mm) | Decimal | Required |
| DOT week | Integer (1–52) | Required — manufacture week |
| DOT year | Integer (e.g. 2019) | Required — manufacture year |
| Remaining mileage estimate | Integer (km) | Optional |
| Defects description | Text | Required if any defects |
| Quantity per listing | Integer | Default: 1; supports set listings (e.g. 4) |

> **DOT age warning:** System auto-calculates tire age from DOT year. Tires older than 6 years display a warning to admin during approval and to customers on the product page.

### 4.3 Product Statuses

| Status | Description |
|---|---|
| `draft` | Saved by vendor, not visible to customers |
| `active` | Live and searchable |
| `out_of_stock` | Stock = 0, auto-set; not purchasable |
| `archived` | Manually archived by vendor; hidden |

### 4.4 Product Approval Statuses

| Status | Set By | Description |
|---|---|---|
| `pending` | System | Default on creation |
| `approved` | Admin | Product becomes active |
| `rejected` | Admin | Vendor notified with reason |

> **Used tires always require admin approval.** New tires from trusted vendors can be auto-approved (configurable per vendor by admin).

### 4.5 Product Images

- Multiple images per product (max 6)
- Primary image selection (shown in listings)
- Drag-to-reorder image sequence
- Admin can flag/remove images during approval
- Allowed types: JPG, JPEG, PNG, WebP — max 2MB per file
- Auto-resized to: thumbnail (300×300), card (600×400), full (1200px wide)

---

## 5. Tire Search

### 5.1 Size Search

Customers can search by entering a size string or using dropdowns:

**String input:** `225/45R17` → parsed via regex into width=225, aspect_ratio=45, rim_diameter=17

**Dropdown input:**

| Dropdown | Options |
|---|---|
| Width | 155, 165, 175, 185, 195, 205, 215, 225, 235, 245, 255, 265, 275, 285, 295, 305 |
| Profile | 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80 |
| Rim | R13, R14, R15, R16, R17, R18, R19, R20, R22 |

### 5.2 Filters

| Filter | Type | Options |
|---|---|---|
| Condition | Toggle | New, Used |
| Brand | Multi-select checkboxes | Dynamic from stock |
| Season | Multi-select | Summer, Winter, All Season, All Terrain |
| Price range | Min/max inputs | KSh 0 – 100,000 |
| Availability | Toggle | In stock only |
| Run flat | Toggle | Yes / Any |

### 5.3 Sorting Options

| Sort | SQL Equivalent |
|---|---|
| Newest | `ORDER BY created_at DESC` |
| Lowest price | `ORDER BY effective_price ASC` |
| Highest price | `ORDER BY effective_price DESC` |
| Most popular | `ORDER BY total_sold DESC` |
| Best rated | `ORDER BY avg_rating DESC` |

---

## 6. Vehicle Fitment

### 6.1 Fitment Database (`fitment_data` table)

| Column | Type | Notes |
|---|---|---|
| `make` | String | e.g. Toyota |
| `model` | String | e.g. Hilux |
| `year_from` | Integer | Start of production range |
| `year_to` | Integer, nullable | Null = current |
| `width` | Integer | |
| `aspect_ratio` | Integer | |
| `rim_diameter` | Integer | |
| `fitment_type` | Enum | OEM, Upgrade |

Seeded from CSV. Admin can re-upload fitment data to update.

### 6.2 Fitment Search Flow

```
Customer selects Make  →  System returns available Models for that Make
Customer selects Model →  System returns available Years
Customer selects Year  →  System queries fitment_data for matching rows
                          → Returns OEM size (primary) + upgrade sizes
                          → Immediately queries products for matching size
                          → Displays compatible tire listings
```

### 6.3 Livewire Component Behaviour

- `updatedMake()` resets model, year, results
- `updatedModel()` resets year, results
- `updatedYear()` triggers fitment lookup + product query
- Results shown inline — no page reload

---

## 7. Shopping Cart

### 7.1 Features

| Feature | Detail |
|---|---|
| Add to cart | With quantity selector (respects stock limit) |
| Update quantity | Inline +/- controls |
| Remove item | Per-item remove button |
| Persistence | Session-based; survives page reload |
| Multi-vendor | Items from different vendors in one cart |
| Mixed condition | New and used tires can coexist in cart |

### 7.2 Cart Validation at Checkout

- Stock re-verified at checkout time (not just at add-to-cart)
- If stock changed since adding, customer is warned before proceeding
- Each item shows vendor name and estimated dispatch time

---

## 8. Order Management

### 8.1 Customer Features

| Feature | Detail |
|---|---|
| Checkout | Single flow for mixed-vendor carts |
| Order history | Paginated list with status badges |
| Order detail | Line items grouped by vendor, with per-item status |
| Order tracking | Tracking number per vendor's items |

### 8.2 Order Statuses (Master Order)

```
pending → paid → processing → shipped → delivered
                                      ↘ cancelled
```

| Status | Trigger |
|---|---|
| `pending` | Order created, payment not yet confirmed |
| `paid` | Payment confirmed (auto for M-Pesa, manual for others) |
| `processing` | Vendor has accepted and is preparing items |
| `shipped` | Vendor has dispatched items with tracking number |
| `delivered` | Customer confirms receipt (or auto-set after X days) |
| `cancelled` | Customer or admin cancels; stock restored |

### 8.3 Multi-Vendor Order Splitting

```
One customer checkout
         ↓
  Master Order created (orders table)
  Reference: ORD-2026-XXXXXX
         ↓
  One OrderItem per product (order_items table)
  Each tagged with vendor_id, commission snapshot
         ↓
  Each vendor sees only their own items
  Each vendor updates their own item's vendor_status
         ↓
  Master order = delivered only when ALL items = delivered
```

**Key rule:** Commission rate and amount are stored as snapshots at order creation time — immune to future rate changes.

---

## 9. Payment Management

### 9.1 M-Pesa Express (STK Push)

| Step | Detail |
|---|---|
| Initiation | Customer enters phone number at checkout → STK push sent |
| Prompt | Customer sees payment prompt on their phone |
| Timeout | 120 seconds — if expired, customer can retry |
| Callback | Daraja webhook updates payment and order status |
| Fallback | If callback delayed, frontend polls `/payments/{order}/status` |

**Required Daraja credentials:**

```
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=          # Paybill or Till number
MPESA_PASSKEY=
MPESA_CALLBACK_URL=       # Must be publicly accessible HTTPS URL
MPESA_ENVIRONMENT=        # sandbox | production
```

### 9.2 Manual M-Pesa Payment

Customer submits:
- M-Pesa transaction code
- Sending phone number
- Screenshot of confirmation (optional)

Admin reviews and manually confirms. Order stays `pending` until confirmed.

### 9.3 Bank Transfer

Customer submits:
- Bank transaction reference
- Payment proof (screenshot/PDF)

Admin reviews and manually confirms.

### 9.4 Payment Record Schema

| Column | Type | Notes |
|---|---|---|
| `order_id` | FK | |
| `method` | Enum | mpesa_express, mpesa_manual, bank_transfer |
| `amount` | Decimal | |
| `phone_number` | String | M-Pesa only |
| `transaction_code` | String | M-Pesa receipt or bank ref |
| `status` | Enum | pending, processing, paid, failed, cancelled, refunded |
| `gateway_response` | JSON | Full Daraja callback payload |
| `paid_at` | Timestamp | Null until confirmed |

### 9.5 M-Pesa Transaction Log Schema

| Column | Notes |
|---|---|
| `merchant_request_id` | From STK push initiation |
| `checkout_request_id` | From STK push initiation |
| `receipt_number` | From Daraja callback on success |
| `result_code` | 0 = success; others = various failures |
| `result_desc` | Human-readable result from Daraja |
| `callback_data` | Full JSON payload stored for audit |
| `transaction_status` | initiated, success, failed, timeout |

---

## 10. Commission & Revenue

### 10.1 Commission Tracking

Commission is calculated per order item at checkout time and stored as a snapshot:

```
commission_amount = unit_price × quantity × (commission_rate / 100)
vendor_amount = (unit_price × quantity) − commission_amount
```

| Column | Notes |
|---|---|
| `commission_rate` | Snapshot — vendor's rate at time of order |
| `commission_amount` | Platform's cut |
| `vendor_amount` | Vendor's payout |
| `settled_at` | Null until admin confirms payout |

### 10.2 Vendor Revenue Reports

| Report | Grouping |
|---|---|
| Daily sales | Revenue per day for selected date range |
| Monthly sales | Revenue per month for selected year |
| Revenue earned | Gross sales minus commissions |
| Outstanding orders | Orders not yet delivered (pending payout) |

---

## 11. Review & Rating System

### 11.1 Product Reviews

- Available only after order item reaches `delivered`
- Rating: 1–5 stars
- Review body: min 20 characters
- Flagged as `is_verified_purchase = true` for confirmed buyers
- Admin can hide/remove reviews
- Average rating stored on products table (recalculated on new review)

### 11.2 Vendor Reviews

- Rating: 1–5 stars
- Free-text feedback
- Tied to a completed order
- Shown on vendor's public profile page
- Admin can moderate

---

## 12. Product Request Module

Customers can request tires that are not currently listed.

**Captured fields:**

| Field | Type |
|---|---|
| Tire size | Width / aspect ratio / rim diameter |
| Vehicle info | Make, model, year (optional) |
| Tire type preference | New, used, or either |
| Contact details | Phone, email |
| Notes | Free text |

**Workflow:**

- Customer submits request → status: `open`
- Vendors can browse open requests and respond with a matching product or quote
- On response: customer notified
- Admin can close or mark fulfilled

---

## 13. Trust & Safety

### 13.1 Used Tire Verification

| Step | Action |
|---|---|
| Vendor submits | Minimum 3 photos required (tread, sidewall, DOT area) + DOT code + tread depth |
| DOT age check | System warns if tire is older than 6 years |
| Admin review | Views photos in approval queue — approve or reject with reason |
| On approval | Product goes live with `Used` badge and condition details visible |
| Duplicate DOT | System flags if same DOT code appears in another active listing |

### 13.2 Listing Reporting

Customers can report a listing for:
- Fraudulent or misleading information
- Incorrect tire specifications
- Suspicious or fake product photos

Reports go to admin queue. Admin can: dismiss, request vendor correction, or remove listing.

---

## 14. Reporting & Analytics

### 14.1 Admin Reports

| Report | Description |
|---|---|
| Total sales | Platform-wide order count and GMV by date range |
| Total revenue | Gross revenue, commissions collected, net platform income |
| Vendor performance | GMV, order count, fulfillment speed, dispute rate per vendor |
| Product performance | Best-selling tires by quantity and revenue |
| New vs Used analytics | Sales split by tire condition |
| Payment statistics | Breakdown by payment method, failure rates, pending manual verifications |

### 14.2 Vendor Reports

| Report | Description |
|---|---|
| Sales report | Orders and revenue by day/week/month |
| Revenue report | Gross sales, commissions deducted, net payout |
| Inventory report | Current stock, low-stock alerts, days-of-stock estimate |
| Order report | Orders by status, average fulfillment time |

---

## 15. Notification System

### 15.1 Notification Triggers

| Event | Recipient | Channel |
|---|---|---|
| Vendor application submitted | Admin | Email |
| Vendor approved / rejected | Vendor | Email + In-app |
| Product submitted for approval | Admin | Email |
| Product approved / rejected | Vendor | Email + In-app |
| New order placed | Vendor (per item) | Email + In-app |
| Payment confirmed | Customer + Vendor | Email + In-app |
| Order status updated | Customer | Email + In-app |
| Product request response | Customer | Email + In-app |
| Low stock alert (< 5 units) | Vendor | In-app |

### 15.2 Delivery Channels

- **Email:** Laravel Mailable + `MAIL_MAILER=smtp` (configure with SendGrid/Mailgun for reliability on shared hosting)
- **In-app:** Stored in `notifications` table (Laravel's built-in notification system); displayed in nav bell icon

---

## 16. Infrastructure & Deployment

### 16.1 Technology Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Blade + Livewire 3 + Alpine.js + Tailwind CSS |
| Database | MySQL 8 |
| Authentication | Laravel Breeze + Spatie Laravel Permission |
| Media | Spatie Laravel MediaLibrary + Intervention Image |
| Search | Laravel Scout (database driver — shared hosting safe) |
| Queue | Database driver with cron fallback |
| Payments | M-Pesa Daraja API |
| Storage | Local disk (S3-ready via `config/filesystems.php`) |

### 16.2 Key Packages

```bash
# Core
composer require laravel/breeze --dev
composer require spatie/laravel-permission
composer require spatie/laravel-medialibrary
composer require laravel/sanctum

# Frontend
composer require livewire/livewire

# Search & Filters
composer require laravel/scout
composer require spatie/laravel-query-builder

# Media
composer require intervention/image

# Dev tools
composer require barryvdh/laravel-debugbar --dev
```

### 16.3 Shared Hosting Deployment Checklist

| Step | Command / Action |
|---|---|
| File structure | Laravel root **outside** `public_html`; copy or symlink `public/` into `public_html/` |
| Environment | Set `APP_ENV=production` and `APP_DEBUG=false` in `.env` |
| Optimize | `php artisan config:cache && route:cache && view:cache` |
| Storage link | `php artisan storage:link` — run once after deploy |
| Permissions | `chmod -R 775 storage/ bootstrap/cache/` |
| Queue driver | `QUEUE_CONNECTION=database` — no Redis required |
| Cron job (cPanel) | `* * * * * /usr/local/bin/php /home/user/tire-marketplace/artisan schedule:run >> /dev/null 2>&1` |
| Scout driver | `SCOUT_DRIVER=database` — upgrade to Meilisearch on VPS later |
| PHP requirement | Verify: PHP 8.2+, PDO, BCMath, OpenSSL, Tokenizer, Fileinfo extensions |
| Session/cache | `SESSION_DRIVER=file`, `CACHE_STORE=file` — safe for shared hosting |

### 16.4 Database Indexing Strategy

```sql
-- Core performance indexes
ALTER TABLE products ADD INDEX idx_tire_size (width, aspect_ratio, rim_diameter);
ALTER TABLE products ADD INDEX idx_vendor_status (vendor_id, is_approved, is_active);
ALTER TABLE products ADD INDEX idx_brand (brand_id);
ALTER TABLE fitment_data ADD INDEX idx_vehicle (make, model);
ALTER TABLE fitment_data ADD INDEX idx_size (width, aspect_ratio, rim_diameter);
ALTER TABLE orders ADD INDEX idx_user_status (user_id, status);
ALTER TABLE order_items ADD INDEX idx_vendor (vendor_id, vendor_status);
```

### 16.5 Cron Schedule (`routes/console.php`)

```php
Schedule::command('queue:work --stop-when-empty')->everyMinute();
Schedule::command('inventory:check-low-stock')->dailyAt('08:00');
Schedule::command('reports:generate-daily')->dailyAt('00:05');
Schedule::command('orders:auto-complete')->dailyAt('02:00'); // auto-deliver after X days
```

---

## 17. Core MVP Feature Checklist

| # | Feature | Module | Priority |
|---|---|---|---|
| 1 | User registration, login, password reset | Auth | 🔴 Must |
| 2 | Role-based access (admin, vendor, customer) | Auth | 🔴 Must |
| 3 | Vendor application and approval workflow | Vendor | 🔴 Must |
| 4 | Vendor dashboard (stats, orders, revenue) | Vendor | 🔴 Must |
| 5 | New tire product creation | Product | 🔴 Must |
| 6 | Used tire product creation with DOT + photos | Product | 🔴 Must |
| 7 | Product approval workflow | Product | 🔴 Must |
| 8 | Multiple product images with primary selection | Product | 🔴 Must |
| 9 | Tire size search (string + dropdowns) | Search | 🔴 Must |
| 10 | Faceted filters (condition, brand, season, price) | Search | 🔴 Must |
| 11 | Vehicle fitment checker (Make/Model/Year) | Fitment | 🔴 Must |
| 12 | Fitment database seeding from CSV | Fitment | 🔴 Must |
| 13 | Shopping cart (multi-vendor, persistent) | Cart | 🔴 Must |
| 14 | Checkout with address and payment selection | Orders | 🔴 Must |
| 15 | Multi-vendor order splitting | Orders | 🔴 Must |
| 16 | Order status tracking per vendor | Orders | 🔴 Must |
| 17 | M-Pesa Express STK Push | Payments | 🔴 Must |
| 18 | Daraja callback handling | Payments | 🔴 Must |
| 19 | Manual M-Pesa payment verification | Payments | 🔴 Must |
| 20 | Bank transfer payment verification | Payments | 🟡 Should |
| 21 | Commission snapshot and tracking | Commission | 🔴 Must |
| 22 | Product reviews (verified purchase only) | Reviews | 🟡 Should |
| 23 | Vendor ratings | Reviews | 🟡 Should |
| 24 | Product request submission | Requests | 🟡 Should |
| 25 | Vendor response to product requests | Requests | 🟡 Should |
| 26 | Used tire photo verification (admin) | Trust | 🔴 Must |
| 27 | Listing reporting by customers | Trust | 🟡 Should |
| 28 | Admin analytics dashboard | Reports | 🟡 Should |
| 29 | Vendor sales and inventory reports | Reports | 🟡 Should |
| 30 | Email notifications | Notifications | 🟡 Should |
| 31 | In-app notifications | Notifications | 🟢 Nice |
| 32 | CSV bulk product import | Product | 🟢 Nice |
| 33 | Shared hosting deployment config | Infra | 🔴 Must |
| 34 | Database backups (cron) | Infra | 🟡 Should |

---

## Phase 2 (Post-MVP)

These features are explicitly out of scope for the MVP and will be addressed in subsequent releases:

- Mobile applications (iOS / Android)
- Automated vendor payouts via M-Pesa B2C API
- Card payment integration (Stripe / Flutterwave)
- Delivery integration (e.g. Sendy, G4S Courier)
- Real-time vendor-customer messaging
- Advanced marketplace analytics (cohort analysis, LTV)
- Meilisearch upgrade for full-text search
- ERP / accounting system integrations

---

*TreadMart MVP Reference — v1.0 — June 2026*