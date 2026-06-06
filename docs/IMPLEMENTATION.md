# TreadMart — Customer Journey Implementation Status

Living document tracking what is **in the codebase today** vs what the [customer journey plan](.cursor/plans/customer_journey_screens_101d89f8.plan.md) still requires.

**Last audited:** 7 June 2026  
**Test suite:** 37 tests passing (8 in `CustomerJourneySearchTest`)

---

## At a glance

| Phase | Scope | Status |
|-------|--------|--------|
| **0** | Schema for discovery + used tires + requests | **Done** |
| **0+** | Payments / M-Pesa schema + API | **Partial** — tables + STK API; UI/env gaps |
| **1** | Homepage + shop search (Stages 1–3) | **Done** |
| **2** | Purchase loop (Stages 4–11) | **Mostly not started** — checkout/payment partially wired |

---

## Journey overview

| Stage | Step | Status | Summary |
|-------|------|--------|---------|
| 1 | Discovery (homepage) | ✅ **Done** | Dual vehicle + size search on `/` |
| 2 | Tire search | ✅ **Done** | Vehicle → size chips → `/shop?size=…` |
| 3 | Browse results | ✅ **Done** | Filters, new/used cards, tire-request CTA |
| 4 | Product details | 🟡 **Partial** | Basic PDP; no used-tire/vendor/trust blocks |
| 5 | Cart | 🟡 **Partial** | Session cart, qty controls, KES totals |
| 6 | Checkout | 🟡 **Partial** | US-style address; M-Pesa option on same page |
| 7 | Payment | 🟡 **Partial** | M-Pesa STK API + checkout AJAX; no Paybill/bank UI |
| 8 | Order confirmation | 🟡 **Partial** | Order page exists; not a dedicated paid confirmation |
| 9 | Order tracking | ❌ **Not started** | No history, no multi-step timeline |
| 10 | Delivery | ❌ **Not started** | No confirm-receipt |
| 11 | Post-purchase | ❌ **Not started** | No reviews or Buy Again |
| — | Customer dashboard | ❌ **Not started** | No `/account/*` (orders, requests, addresses) |

---

## Phase 0 — Core schema

### ✅ Done

| Item | Location |
|------|----------|
| Used/new product fields (`condition`, `condition_grade`, tread, DOT, defects, `is_verified`, `sold_count`) | `database/migrations/2026_06_07_100000_add_used_tire_fields_to_products_table.php`, `app/Models/Product.php` |
| `tire_requests` table + model | `database/migrations/2026_06_07_100001_create_tire_requests_table.php`, `app/Models/TireRequest.php` |
| `fitment_type` on `fitment_data` (`oem` / `upgrade`) | `database/migrations/2026_06_07_100002_add_fitment_type_to_fitment_data_table.php` |
| CSV importer accepts new product fields | `app/Services/ProductCsvImporter.php` |
| `format_kes()` helper | `app/helpers.php` |
| Test factories (`Product`, `Brand`) | `database/factories/` |
| Orders + order items (multi-vendor snapshots) | `database/migrations/2026_06_06_144415_create_orders_table.php`, `create_order_items_table.php` |
| Order payment snapshot fields (`payment_status`, `stripe_session_id`) | `database/migrations/2026_06_06_150211_add_payment_fields_to_orders_table.php` |
| Stripe webhook audit log | `database/migrations/2026_06_06_185300_create_stripe_events_table.php` |

### 🟡 Partial — payments schema (added after Phase 1)

| Item | Location | Gap |
|------|----------|-----|
| `payments` table | `database/migrations/2026_06_07_013000_create_payments_table.php`, `app/Models/Payment.php` | Not linked from `Order` model; `payment_status` on order not updated from M-Pesa callback consistently |
| `mpesa_transactions` table | `database/migrations/2026_06_07_013100_create_mpesa_transactions_table.php`, `app/Models/MpesaTransaction.php` | Callback links payment by phone+amount heuristic (fragile) |

### ❌ Still missing (schema)

| Item | Needed for |
|------|------------|
| `addresses` table | Saved customer addresses |
| `reviews` table | Product + vendor ratings |
| `order_items.vendor_status`, `tracking_number` | Per-vendor shipment tracking |
| Product media (multi-photo) | Used-tire verification gallery — Spatie MediaLibrary installed, not wired |
| Vendor notification on `tire_requests` | Alert vendors when customer requests a size |

---

## Phase 1 — Discovery & search (Stages 1–3) ✅

### Stage 1 — Homepage

| Feature | Implementation |
|---------|----------------|
| Vehicle search (Make → Model → Year) | `resources/views/components/⚡vehicle-size-finder.blade.php` |
| Recommended size chips (OEM first) | `app/Services/FitmentService.php` |
| Tire size search (compact) | `livewire:tire-search` with `compact` prop on `resources/views/home/index.blade.php` |
| Featured products below search | `resources/views/home/index.blade.php` |
| `/fitment` → `/#vehicle-search` | `routes/web.php` |
| Nav “Find by Vehicle” | `resources/views/layouts/navigation.blade.php` |

### Stage 2 — Tire search routing

| Feature | Implementation |
|---------|----------------|
| Size chip → shop deep link | `route('shop.index', ['size' => …, 'make' => …])` |
| URL params on shop mount | `resources/views/shop/index.blade.php` → `⚡tire-search` |
| Vehicle-only params → size picker banner | `⚡tire-search.blade.php` + `FitmentService` |
| Shared fitment logic | `app/Services/FitmentService.php` |
| Legacy fitment checker refactored | `resources/views/components/⚡fitment-checker.blade.php` |

### Stage 3 — Browse & filter

| Feature | Implementation |
|---------|----------------|
| Condition filter (new / used) | `⚡tire-search.blade.php`, `tire-search-filters.blade.php` |
| Brand multi-select | ✅ |
| Season multi-select | ✅ |
| Price range (KES min/max) | ✅ |
| Used condition grade | ✅ |
| Sort (price, newest, sold, name) | ✅ |
| New vs used product cards + vendor | `resources/views/components/product-card.blade.php` |
| Shop grid: View Details only | `show-add-to-cart="false"` on shop |
| No-results → Request Tire form | Saves to `tire_requests` via `submitTireRequest()` |
| KES on listings + cart + PDP | `format_kes()` — **see currency note below** |

### Tests

`tests/Feature/CustomerJourneySearchTest.php` (8 tests):

- Homepage dual search
- Fitment redirect
- Shop `?size=225/45R17`
- Condition filter
- Tire request save
- Vehicle size finder chips
- Compact search redirect
- Shop vehicle size picker

### Minor Phase 1 gaps

| Gap | Priority |
|-----|----------|
| Predefined width/profile/rim dropdowns (README §5.1) | Low |
| Star ratings on cards | Low — needs reviews module |
| Vendor notification on tire request | Medium |
| Used-tire demo data in seeder | Low — seeder still lists all as new |
| Admin fitment CSV re-upload UI | Low |

---

## Phase 2 — Purchase loop

### P2 — Product details (Stage 4) 🟡 Partial

**File:** `resources/views/products/show.blade.php`

| Done | Missing |
|------|---------|
| Brand, KES price, size, season, load/speed, stock | Used-tire block (condition, tread, DOT, mileage, defects) |
| Description, add-to-cart with qty | Vendor info block (shop name, rating, sold count) |
| Related products | `is_verified` badge, DOT age warning (>6 yrs) |
| | Buy Now, Save for Later |
| | Multi-image gallery |

---

### P3 — Cart (Stage 5) 🟡 Partial

**Files:** `app/Services/CartService.php`, `resources/views/cart/index.blade.php`

| Done | Missing |
|------|---------|
| Session cart (add / update / remove) | Group lines by vendor |
| Quantity +/- controls | Vendor name per line |
| KES line totals and subtotal | New vs used badge on lines |
| Proceed to checkout | Stock re-check warning before checkout |

---

### P4 — Checkout + payment (Stages 6–7) 🟡 Partial

**Files:** `resources/views/orders/checkout.blade.php`, `app/Http/Controllers/OrderController.php`, `app/Services/OrderService.php`, `app/Http/Controllers/MpesaController.php`, `app/Http/Controllers/PaymentController.php`

| Done | Missing |
|------|---------|
| Shipping form (name, address, city, state, ZIP, phone, notes) | Kenya fields: county, town, landmark |
| Two-step UI hint (Shipping → Payment) | Delivery method: pickup vs home delivery |
| Payment method radio: M-Pesa STK / Stripe | M-Pesa Paybill (manual transaction code) |
| AJAX: create order → initiate STK → redirect to order | Bank transfer option |
| `POST /payments/mpesa/initiate` — Daraja OAuth + STK push | `MPESA_*` vars not in `.env.example` |
| `POST /payments/mpesa/callback` — parses STK callback, updates `Payment` + order `status` | No payment-status polling endpoint |
| `payments` + `mpesa_transactions` persistence | Dedicated `/payment` step (payment merged into checkout) |
| Stripe checkout session + webhook (existing) | `Order.payment_status` not set on M-Pesa success |
| | Checkout/order summary still shows **USD `$`** (not KES) |
| | No automated tests for M-Pesa flow |

**Checkout flow today:**

```
Cart → /checkout
  → Place order (AJAX POST /orders)
  → if payment_method = mpesa_express
       → POST /payments/mpesa/initiate
       → alert + redirect /orders/{id}
  → else redirect /orders/{id}
```

Credentials required: `MPESA_CONSUMER_KEY`, `MPESA_CONSUMER_SECRET`, `MPESA_SHORTCODE`, `MPESA_PASSKEY`, `MPESA_CALLBACK_URL`, `MPESA_ENVIRONMENT`.

---

### P5 — Order confirmation + tracking (Stages 8–9) 🟡 / ❌

**Files:** `resources/views/orders/show.blade.php`, `resources/views/checkout/success.blade.php`

| Done | Missing |
|------|---------|
| Single order view with status badge | Customer order history (`My Orders`) |
| One timeline step (“Order placed”) | Full timeline: pending → processing → shipped → delivered |
| Line items with SKU + qty | Per-vendor item status + tracking numbers |
| Stripe success/cancel pages | `ORD-YYYY-NNNNN` order numbers (still `TM-RANDOM`) |
| | Auth/ownership guard on `orders.show` |
| | Payment status displayed on order page |
| | KES on order view (still `$`) |

---

### P6 — Delivery + post-purchase (Stages 10–11) ❌

| Missing |
|---------|
| Confirm receipt action |
| Product reviews (rating + text) |
| Vendor reviews / feedback |
| Buy Again / reorder |

---

### P7 — Customer dashboard ❌

**Current:** Breeze `dashboard.blade.php` + `profile/edit` only.

**Planned MVP nav (not built):**

```
/account
├── profile
├── addresses
├── orders
├── reviews
└── tire-requests
```

---

## Currency consistency

KES is used in: product cards, PDP, cart.

**Still USD (`$`) in:**

- `resources/views/orders/checkout.blade.php` (order summary sidebar)
- `resources/views/orders/show.blade.php`
- `resources/views/vendor/dashboard.blade.php`

---

## Livewire components (customer-facing)

| Component | File | Used on |
|-----------|------|---------|
| `vehicle-size-finder` | `resources/views/components/⚡vehicle-size-finder.blade.php` | Home |
| `tire-search` | `resources/views/components/⚡tire-search.blade.php` | Home (compact), Shop (full) |
| `fitment-checker` | `resources/views/components/⚡fitment-checker.blade.php` | Unused route (redirects to home) |

---

## Key routes

| Route | Stage | Status |
|-------|-------|--------|
| `/` | 1 | ✅ Dual search |
| `/shop?size=225/45R17` | 2–3 | ✅ Filtered results |
| `/products/{product}` | 4 | 🟡 Basic PDP |
| `/cart` | 5 | 🟡 Session cart |
| `/checkout` | 6–7 | 🟡 Shipping + payment on one page |
| `POST /orders` | 6 | ✅ Creates order (JSON or redirect) |
| `POST /payments/mpesa/initiate` | 7 | 🟡 STK API (needs env) |
| `POST /payments/mpesa/callback` | 7 | 🟡 Daraja webhook |
| `/orders/{order}` | 8–9 | 🟡 Minimal view |
| `/fitment` | 1 | ✅ Redirect → `/#vehicle-search` |
| `/account/*` | — | ❌ Not implemented |

---

## Intentionally unchanged

| Area | Notes |
|------|-------|
| Session cart (no DB table) | Per Phase 1 plan |
| Vendor / admin dashboards | No customer-journey changes |
| Laravel Breeze auth | Standard registration/login |

---

## Recommended next steps

1. **Product details (P2)** — used-tire + vendor trust blocks; highest impact before more checkout work  
2. **Currency pass** — KES on checkout + order views  
3. **Checkout Kenya fields** — county, town, landmark, delivery method  
4. **Payment hardening** — `payment_status` sync, `.env.example`, M-Pesa tests, Paybill UI  
5. **Order history + timeline (P5)** — close the post-purchase loop  
6. **Customer dashboard (P7)** — orders, tire requests, addresses  
7. **Reviews + delivery confirmation (P6)** — retention and trust  

---

## Changelog

| Date | Change |
|------|--------|
| 7 Jun 2026 | Initial doc after Phase 1 implementation |
| 7 Jun 2026 | Re-audit: documented `payments` / `mpesa_transactions` tables, M-Pesa STK API, checkout AJAX flow; corrected payment stage from “not started” to “partial”; noted currency inconsistencies |
