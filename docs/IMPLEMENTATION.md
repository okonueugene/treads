# TreadMart — Customer Journey Implementation Status

Living document tracking what is **in the codebase today** vs what remains.

**Last audited:** 7 June 2026  
**Test suite:** 37 tests passing (8 in `CustomerJourneySearchTest`)

---

## At a glance

| Phase | Scope | Status |
|-------|--------|--------|
| **0** | Schema — products, orders, payments, fitment, reviews, addresses | ✅ **Done** |
| **1** | Homepage + shop search (Stages 1–3) | ✅ **Done** |
| **2** | Purchase loop — PDP, cart, checkout, payment (Stages 4–8) | ✅ **Done** |
| **3** | Order tracking + delivery + post-purchase (Stages 9–11) | ✅ **Done** |
| **4** | Customer dashboard (`/account/*`) | ✅ **Done** |
| **5** | STK polling UI + payment-status endpoint | ❌ **Not started** |
| **6** | Vendor revenue reports + admin analytics | ❌ **Not started** |
| **7** | Notifications (email + in-app bell) | ❌ **Not started** |

---

## Journey overview

| Stage | Step | Status | Notes |
|-------|------|--------|-------|
| 1 | Discovery (homepage) | ✅ **Done** | Dual vehicle + size search on `/` |
| 2 | Tire search | ✅ **Done** | Vehicle → size chips → `/shop?size=…` |
| 3 | Browse results | ✅ **Done** | Filters, new/used cards, star ratings, tire-request CTA |
| 4 | Product details | ✅ **Done** | Used-tire block, vendor trust block, multi-image gallery, reviews |
| 5 | Cart | ✅ **Done** | Session cart, Remove button, Saved for Later, KES totals |
| 6 | Checkout | ✅ **Done** | Kenya fields (county/town/landmark), delivery method, M-Pesa/Paybill |
| 7 | Payment | ✅ **Done** | STK Push + phone normalization; callback syncs order status |
| 8 | Order confirmation | ✅ **Done** | `ORD-YYYY-NNNNN` number, payment status, KES totals |
| 9 | Order tracking | ✅ **Done** | Multi-step timeline; per-vendor item status |
| 10 | Delivery | ✅ **Done** | Confirm receipt action |
| 11 | Post-purchase | ✅ **Done** | Product + vendor reviews, Buy Again |
| — | Customer dashboard | ✅ **Done** | `/account`: profile, addresses, orders, reviews, tire-requests |
| — | No-results tire request | ✅ **Done** | Request form → `tire_requests` table |

---

## Phase 0 — Core schema ✅

| Item | Location |
|------|----------|
| Products (new + used fields: condition, tread, DOT, defects, is_verified, sold_count) | `migrations/2026_06_07_100000_add_used_tire_fields_to_products_table.php` |
| `images` JSON column on products (multi-photo) | `migrations/2026_06_07_210000_add_images_to_products_table.php` |
| `tire_requests` table | `migrations/2026_06_07_100001_create_tire_requests_table.php` |
| `fitment_type` on `fitment_data` (oem / upgrade) | `migrations/2026_06_07_100002_add_fitment_type_to_fitment_data_table.php` |
| Orders + order items (multi-vendor, commission snapshot) | `create_orders_table.php`, `create_order_items_table.php` |
| `payments` + `mpesa_transactions` | `create_payments_table.php`, `create_mpesa_transactions_table.php` |
| `reviews` table (product + vendor) | `create_reviews_table.php` |
| `addresses` table | `create_addresses_table.php` |
| Stripe events audit log | `create_stripe_events_table.php` |
| `format_kes()` helper | `app/helpers.php` |

---

## Phase 1 — Discovery & search (Stages 1–3) ✅

| Feature | Implementation |
|---------|----------------|
| Vehicle search (Make → Model → Year → size chips) | `components/⚡vehicle-size-finder.blade.php`, `FitmentService` |
| Tire size direct search (compact on home, full on shop) | `components/⚡tire-search.blade.php` |
| Condition, brand, season, price, grade filters | `tire-search-filters.blade.php` |
| Sort (price, newest, sold, name) on desktop + mobile | `⚡tire-search.blade.php` |
| Star ratings on product cards | `components/product-card.blade.php` |
| New vs used badge + vendor name on cards | `components/product-card.blade.php` |
| No-results → Request Tire form → `tire_requests` | `⚡tire-search.blade.php` `submitTireRequest()` |
| `/fitment` redirect | `routes/web.php` |

**Tests:** `tests/Feature/CustomerJourneySearchTest.php` — 8 tests covering homepage dual search, fitment redirect, shop size param, condition filter, tire request save, size finder chips, compact search, vehicle size picker.

---

## Phase 2 — Purchase loop (Stages 4–8) ✅

### Stage 4 — Product detail page

**File:** `resources/views/products/show.blade.php`

| Feature | Notes |
|---------|-------|
| Brand, KES price, size, season, load/speed, stock | ✅ |
| Used-tire block (condition grade, tread depth, DOT date, mileage, defects) | ✅ |
| `is_verified` trust badge | ✅ |
| Vendor info block (shop name, avg rating, review count, sold count) | ✅ — uses `User::averageVendorRating()` / `vendorReviewCount()` |
| Multi-image gallery (main image + thumbnail strip, Alpine.js) | ✅ — `images` JSON column; `Product::allImages()` |
| Visual star ratings (product + vendor) | ✅ |
| Customer reviews section | ✅ |
| Add to Cart + Buy Now + Save for Later | ✅ |
| Related products | ✅ |

### Stage 5 — Cart

**Files:** `app/Services/CartService.php`, `resources/views/cart/index.blade.php`

| Feature | Notes |
|---------|-------|
| Session cart (add / qty update / remove) | ✅ |
| Explicit ✕ Remove button per line | ✅ |
| Saved for Later section | ✅ |
| KES line totals and subtotal | ✅ |
| New vs used badge on cart lines | ✅ |
| Vendor name per line | ✅ |
| Proceed to Checkout CTA | ✅ |

### Stages 6–7 — Checkout + payment

**Files:** `resources/views/orders/checkout.blade.php`, `OrderController`, `OrderService`, `MpesaController`

| Feature | Notes |
|---------|-------|
| Kenya shipping fields: name, phone, county, town, address, landmark | ✅ |
| Delivery method: pickup / home delivery | ✅ |
| Payment method: M-Pesa Express (STK) / M-Pesa Paybill / Bank transfer | ✅ |
| AJAX checkout: create order → initiate payment → redirect | ✅ — proper error handling, button disable |
| M-Pesa phone normalization (`07…` / `+254…` / `254…` → `254XXXXXXXXX`) | ✅ — `MpesaController::normalizePhone()` |
| STK Push (Daraja OAuth + stkpush) | ✅ — sandbox SSL `withoutVerifying()` |
| M-Pesa Paybill: show business number + account + amount | ✅ |
| Manual M-Pesa: submit transaction code | ✅ |
| `payments` + `mpesa_transactions` persisted | ✅ |
| Daraja callback updates `Payment` + `Order.payment_status` | ✅ — linked via `checkout_request_id` |
| Order confirmation redirect (`/orders/{order}/confirmation`) | ✅ — JS bug fixed |
| All MPESA env vars documented in `.env.example` | ✅ |

**Checkout flow:**

```
Cart → /checkout
  → POST /orders (AJAX) → creates Order (ORD-YYYY-NNNNN)
  → if mpesa_express  → POST /payments/mpesa/initiate → STK Push → redirect /orders/{id}/confirmation
  → if mpesa_paybill  → show Paybill details → customer submits transaction code
  → if bank_transfer  → show bank details
```

### Stage 8 — Order confirmation

| Feature | Notes |
|---------|-------|
| `ORD-YYYY-NNNNN` order number | ✅ |
| Payment status + order status displayed | ✅ |
| KES order totals | ✅ |
| Guest order accessible via `session('last_order_id')` | ✅ |

---

## Phase 3 — Order tracking + post-purchase (Stages 9–11) ✅

| Feature | File | Notes |
|---------|------|-------|
| Order timeline (pending → processing → shipped → delivered) | `orders/show.blade.php` | ✅ |
| Per-vendor item status | `orders/show.blade.php` | ✅ |
| Confirm receipt action | Account orders view | ✅ |
| Product reviews (star rating + text) | `reviews/` views | ✅ |
| Vendor reviews + feedback | `reviews/` views | ✅ |
| Buy Again / reorder | Account orders view | ✅ |

---

## Phase 4 — Customer dashboard ✅

**Base:** `resources/views/components/account-layout.blade.php` (sidebar on desktop, horizontal scrollable tabs on mobile)

| Route | View | Status |
|-------|------|--------|
| `/account` | profile | ✅ |
| `/account/addresses` | address CRUD | ✅ |
| `/account/orders` | order history | ✅ |
| `/account/reviews` | my reviews | ✅ |
| `/account/tire-requests` | tire request history | ✅ |

---

## Remaining work

### Phase 5 — STK Push polling UI ❌

| Item | Notes |
|------|-------|
| `GET /orders/{order}/payment-status` JSON endpoint | Returns `{payment_status, status}` for polling |
| "Waiting for M-Pesa…" spinner on checkout page | JS polls endpoint every 5 s after STK push; auto-redirects on `paid` |

### Phase 6 — Vendor & admin reporting ❌

| Item | File | Notes |
|------|------|-------|
| Vendor revenue/sales reports (daily/monthly charts) | `vendor/dashboard.blade.php` | Chart.js or Alpine |
| Admin product approval queue | Admin controller + view | List pending products, approve/reject |
| Admin analytics dashboard (GMV, order counts, top products) | `admin/dashboard.blade.php` | Platform-wide stats |

### Phase 7 — Notifications ❌

| Item | Notes |
|------|-------|
| Email: order placed → vendor | Laravel Mailable |
| Email: payment confirmed → customer | Laravel Mailable |
| Email: order status update → customer | Laravel Mailable |
| In-app notification bell (navigation) | Laravel `notifications` table + Livewire |
| Low-stock alert (< 5 units) | Artisan command + notification |

---

## Key files

| File | Purpose |
|------|---------|
| `app/Models/Product.php` | New + used tire fields; `allImages()`, `averageRating()` |
| `app/Models/User.php` | Vendor methods: `averageVendorRating()`, `vendorReviewCount()` |
| `app/Models/Order.php` | Master order; `ORD-YYYY-NNNNN` number generation |
| `app/Models/Payment.php` | Payment record linked to order + mpesa_transactions |
| `app/Models/MpesaTransaction.php` | Daraja STK log (merchant_request_id, checkout_request_id) |
| `app/Services/CartService.php` | Session cart logic |
| `app/Services/OrderService.php` | Order creation + commission calculation |
| `app/Services/FitmentService.php` | Make/Model/Year → compatible sizes |
| `app/Http/Controllers/MpesaController.php` | STK initiation, `normalizePhone()`, Daraja callback |
| `app/Http/Controllers/OrderController.php` | Checkout AJAX + order confirmation |
| `resources/views/components/⚡tire-search.blade.php` | Main search Livewire (compact + full) |
| `resources/views/components/⚡vehicle-size-finder.blade.php` | Homepage vehicle search |
| `resources/views/products/show.blade.php` | Product detail page (gallery, vendor, reviews) |
| `resources/views/orders/checkout.blade.php` | Checkout + AJAX payment flow |
| `resources/views/orders/show.blade.php` | Order detail + timeline |
| `resources/views/components/account-layout.blade.php` | Account dashboard shell (responsive tabs) |
| `resources/views/vendor/dashboard.blade.php` | Vendor stats (KES) |

---

## Key routes

| Route | Stage | Status |
|-------|-------|--------|
| `/` | 1 | ✅ Dual search |
| `/shop?size=225/45R17` | 2–3 | ✅ Filtered results |
| `/products/{product}` | 4 | ✅ Full PDP |
| `/cart` | 5 | ✅ Session cart |
| `/checkout` | 6–7 | ✅ Kenya fields + M-Pesa |
| `POST /orders` | 6 | ✅ Creates `ORD-YYYY-NNNNN` order |
| `POST /payments/mpesa/initiate` | 7 | ✅ STK Push with phone normalization |
| `POST /payments/mpesa/callback` | 7 | ✅ Daraja webhook → syncs order status |
| `/orders/{order}/confirmation` | 8 | ✅ Confirmation page |
| `/orders/{order}` | 9 | ✅ Timeline + tracking |
| `/account/*` | — | ✅ Profile, orders, addresses, reviews, requests |
| `GET /orders/{order}/payment-status` | 7 | ❌ Not yet built (needed for STK polling) |

---

## Intentionally unchanged

| Area | Notes |
|------|-------|
| Session cart (no DB table) | Per Phase 1 plan — avoids auth dependency |
| Laravel Breeze auth | Standard registration/login/password reset |
| Stripe integration | Legacy; M-Pesa is primary payment method |

---

## Changelog

| Date | Change |
|------|--------|
| 7 Jun 2026 | Initial doc after Phase 1 implementation |
| 7 Jun 2026 | Re-audit: documented payments schema, M-Pesa STK API, checkout AJAX flow |
| 7 Jun 2026 | Major update: Phases 2–4 complete — PDP gallery, cart UX, checkout Kenya fields, M-Pesa bugs fixed (parse error, SSL, phone normalization, callback linkage), customer dashboard, reviews, order timeline, account mobile nav, vendor dashboard KES |
