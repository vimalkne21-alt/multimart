# MODULE DESCRIPTION SPECIFICATION
**Project Title:** MultiMart Multi-Model E-Commerce Platform  

---

## 1. Authentication & Role-Based Authorization Module (`auth/`)
- **Key Files:** `login.php`, `register.php`, `logout.php`, `forgot_password.php`, `reset_password.php`, `profile.php`.
- **Functionality:** Handles multi-role registration (Customer, Business Seller, Individual Seller, Freelancer), Bcrypt password encryption, session lifecycle management, CSRF validation, and dynamic role-based redirection.

---

## 2. B2C Retail E-Commerce Module (`b2c/`, `products/`)
- **Key Files:** `b2c/index.php`, `products/detail.php`, `products/search.php`, `products/search_api.php`, `products/coupons.php`.
- **Functionality:** Provides direct retail product discovery, multi-criteria filtering by category and price bounds, live autocomplete query indexing, customer review publishing, and related product recommendations.

---

## 3. B2B Wholesale & RFQ Module (`b2b/`)
- **Key Files:** `b2b/index.php`, `b2b/detail.php`, `b2b/rfq_create.php`, `b2b/rfq_list.php`.
- **Functionality:** Manages wholesale master carton catalogs, MOQ enforcement, tiered volume pricing calculators, formal Request for Quotation (RFQ) generation, supplier bid response processing, and quote acceptance.

---

## 4. C2C Pre-Owned Consumer Module (`c2c/`)
- **Key Files:** `c2c/index.php`, `c2c/sell.php`, `c2c/detail.php`, `c2c/report.php`.
- **Functionality:** Facilitates peer-to-peer consumer listings with condition classifications (*Brand New*, *Like New*, *Good*, *Fair*), location metadata, direct buyer-seller message initiation, and community listing moderation flags.

---

## 5. C2B Professional Freelancing Module (`c2b/`, `services/`)
- **Key Files:** `c2b/index.php`, `c2b/detail.php`, `c2b/hire.php`, `services/dashboard.php`, `services/create.php`.
- **Functionality:** Connects enterprise clients with specialized technical freelancers (Data Science, Full-Stack Development, UI/UX Design). Features 3-tier engagement packages (Basic, Standard, Premium), proposal management, milestone status transitions, and client rating feedback.

---

## 6. Cart, Checkout & Simulated Payment Module (`cart/`, `checkout/`, `orders/`)
- **Key Files:** `cart/index.php`, `cart/cart_action.php`, `checkout/index.php`, `checkout/process_order.php`, `orders/confirmation.php`, `orders/track.php`, `orders/invoice.php`.
- **Functionality:** Manages shopping cart line item subtotals, coupon calculations (`WELCOME10`, `MULTISAVE20`), shipping logic, tax calculations, stock reservation, simulated safe payment authorization (UPI, Card, Net Banking, COD), 6-stage order tracking, and printable tax invoices.

---

## 7. Master Admin & Operations Module (`admin/`)
- **Key Files:** `admin/dashboard.php`, `admin/users.php`, `admin/products.php`, `admin/orders.php`, `admin/categories.php`, `admin/reports.php`, `admin/activity_logs.php`.
- **Functionality:** Administrative command center with Chart.js GMV metrics, user account status toggle, catalog moderation, global order status updates, taxonomy management, moderation incident handling, and cybersecurity audit logging.

---

## 8. In-App Messaging & Notifications Module (`messages/`, `customer/`)
- **Key Files:** `messages/inbox.php`, `messages/chat.php`, `customer/notifications.php`.
- **Functionality:** Facilitates private, chronological in-app communication between buyers, suppliers, and freelancers, with unread tracking and real-time internal event notification alerts.
