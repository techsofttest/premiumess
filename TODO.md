# TODO - Customer Dashboard restructuring (Phase 1 & 2)

## Phase 1 (Fast)
- [ ] Understand auth/user source in frontend (where `localStorage('user')` comes from).
- [ ] Implement Laravel API endpoint: `GET /api/customer/dashboard-summary` returning:
  - [ ] total_orders
  - [ ] active_orders
  - [ ] saved_addresses_count
  - [ ] wishlist_count (placeholder or real if available)
  - [ ] last_5_orders (id, order_date, status, total)
- [ ] Add Laravel endpoint: `GET /api/me` for sidebar profile data.
- [ ] Update Next.js `ProfileSidebar` to fetch `/api/me` and render dynamic name/email/avatar.
- [ ] Create Next.js component `components/profile/DashboardSummary.tsx` implementing the UI:
  - [ ] Quick Stats cards (dynamic values)
  - [ ] Last 5 Orders table/list (dynamic)
  - [ ] Quick Actions placeholders (buttons without routing yet)
- [ ] Update Next.js `app/profile/page.tsx` to fetch dashboard summary and pass into `DashboardSummary`.
- [ ] Basic manual test: ensure authenticated user sees real values.

## Phase 2 (Full)
- [ ] Implement recently viewed products (DB or tracking approach).
- [ ] Implement recently purchased products (DB via orders).
- [ ] Implement Quick Actions links:
  - [ ] Reorder last purchase (create cart from last order)
  - [ ] View wishlist
  - [ ] Track current order
  - [ ] Manage addresses
  - [ ] Update profile
- [ ] Replace `OrderHistory.tsx` with DB-backed orders list.
- [ ] Replace `AddressBook.tsx` with DB-backed address CRUD.
- [ ] Replace wishlist with DB-backed wishlist (or sync count with DB).
- [ ] Manual regression test for existing profile pages.

# TODO - Payment Implementation

## Backend
- [x] Stripe SDK Integration
- [x] Payment Service (`PaymentGatewayInterface`, `StripePaymentService`)
- [x] PaymentIntent API (`/api/checkout`, `/api/checkout/retry`)
- [x] Secure Webhook Endpoint (`/api/webhooks/stripe`)
- [x] Transaction Logging (`payment_transactions` table)
- [x] Webhook Logging (`stripe_webhook_logs` table)
- [x] Retry Payment API
- [x] Payment Status API (implicitly via order status)
- [x] Use Enums for statuses (`OrderStatus`, `PaymentStatus`, `TransactionStatus`, `TransactionType`)
- [x] CSRF exemption for webhook route

## Frontend
- [ ] Stripe Payment Element integration
- [ ] Payment Processing Screen
- [ ] Success Screen
- [ ] Failure Screen
- [ ] Retry Payment UI
- [ ] Payment Status Display in order history

## Admin
- [ ] Filament Resource for Webhook Logs
- [ ] Enhance Order Resource with Payment Details
- [ ] Payment Timeline visualization in Order Resource
- [ ] Searchable Payment Records in Admin
- [ ] Refund functionality from Admin Panel

# TODO - AI-first Knowledge Base

- [ ] Create missing `docs/ai/*.md` files (database/models/services/repositories/authz/auth/workflows/modules/api/frontend/filament/payments/warehouse/delivery/notifications/integrations/indexes/glossary/changelog)
- [ ] Update existing `docs/ai/*.md` (README/architecture/conventions/coding-rules/project) to link to indexes + module docs
- [ ] Ensure no broken cross-links inside `docs/ai`
- [ ] Add “AI-first” navigation rules to `docs/ai/README.md`

