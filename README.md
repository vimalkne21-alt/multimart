# MultiMart: All-in-One Multi-Model E-Commerce Marketplace
**Academic Major Project — Bachelor of Technology (B.Tech) Computer Science & Engineering / Data Science**

---

## 📌 Executive Summary
MultiMart is an enterprise-grade multi-model e-commerce marketplace engineered to harmonize the four foundational commercial paradigms onto a unified digital architecture:
1. **B2B (Business-to-Business):** Wholesale bulk supplies, Minimum Order Quantity (MOQ) enforcement, tiered volume pricing, and legally binding digital Request for Quotation (RFQ) pipelines.
2. **B2C (Business-to-Consumer):** Retail consumer shopping with full-text search, live autocomplete, persistent cart, discount coupons, and simulated safe checkout.
3. **C2C (Consumer-to-Consumer):** Peer-to-peer pre-owned marketplace with item condition grading, localized pickups, and in-app buyer-seller chat.
4. **C2B (Consumer-to-Business):** Professional freelance services marketplace where certified specialists offer technical skills (Data Science, Web Development, Design) to enterprise clients via multi-tier contract packages.

---

## 🛠️ Technology Stack
- **Frontend:** HTML5, CSS3, Modern Responsive Design System, Bootstrap 5.3, Bootstrap Icons, Font Awesome 6, Vanilla JavaScript with AJAX debouncing.
- **Backend:** PHP 8+, Clean Procedural-Modular MVC Architecture, Secure Session Management, CSRF Protection, XSS Sanitization.
- **Database:** MySQL / MariaDB (XAMPP Default) & SQLite3 fallback with 30 Third Normal Form (3NF) relational tables.
- **Payment Processing:** Safe Simulated Financial Gateway with zero real credential handling.
- **Local Server Compatibility:** XAMPP (`http://localhost/MultiMart/`) or standalone PHP CLI server.

---

## 🚀 Quick Local Setup Instructions (XAMPP)

### Option A: Standard XAMPP Setup (Recommended)
1. **Place Project Directory:**
   Copy or symlink the `MultiMart` folder into your XAMPP web root directory:
   - **Windows:** `C:\xampp\htdocs\MultiMart`
   - **macOS:** `/Applications/XAMPP/xamppfiles/htdocs/MultiMart`
   - **Linux:** `/opt/lampp/htdocs/MultiMart`

2. **Start Apache & MySQL:**
   - Launch the XAMPP Control Panel and start both **Apache** and **MySQL**.

3. **Import Database:**
   - Open your web browser and navigate to `http://localhost/phpmyadmin/`.
   - Click **New** to create a database named `multimart_db`.
   - Select the newly created `multimart_db`, navigate to the **Import** tab, choose `database/multimart_database.sql`, and click **Import**.

4. **Launch Application:**
   - Navigate to: **`http://localhost/MultiMart/`**

---

### Option B: Standalone PHP CLI Runner (Zero Setup / Instant Test)
If XAMPP is not currently installed on your operating system, MultiMart features an automatic SQLite fallback that works instantly using PHP's built-in web server:
```bash
cd /Users/vikramaditya/MultiMart
php -S localhost:8000
```
Then open `http://localhost:8000/` in your browser. All 30 tables and demo data will load automatically!

---

## 🔑 Pre-Seeded Academic Demo Accounts

For grading, evaluation, and live demonstration, MultiMart provides 1-click autofill buttons on the login page (`auth/login.php`):

| Role | Email Address | Password | Dedicated Dashboard Portal |
| :--- | :--- | :--- | :--- |
| **Platform Administrator** | `admin@multimart.com` | `Admin@123` | `/admin/dashboard.php` |
| **Business Seller (B2B/B2C)** | `apex.supplies@multimart.com` | `Seller@123` | `/seller/dashboard.php` |
| **Individual Seller (C2C)** | `john.doe@multimart.com` | `User@123` | `/c2c/index.php` |
| **Retail Customer** | `priya.sharma@multimart.com` | `Customer@123` | `/customer/dashboard.php` |
| **Freelancer (C2B)** | `rahul.tech@multimart.com` | `Freelance@123` | `/services/dashboard.php` |

*(Note: In accordance with academic sandbox convenience, password `password` is also accepted for all demo accounts).*

---

## 📂 Project Architecture & Directory Layout
```text
MultiMart/
├── config/             # Database connection (PDO), CSRF tokens, session security
├── database/           # multimart_database.sql (30 tables) & multimart.sqlite
├── includes/           # Header, navbar, footer, breadcrumbs, notification helpers
├── auth/               # Login, register, logout, password recovery, profile
├── admin/              # Admin dashboard, users, products, orders, categories, reports, audit logs
├── seller/             # Seller workspace, products add/edit, orders dispatch, B2B RFQ bids
├── customer/           # Customer hub, order history, wishlist, notifications
├── b2b/                # Wholesale marketplace, lot details, RFQ quotation request & list
├── b2c/                # Retail shopping catalog, filters, sorting
├── c2c/                # Peer-to-peer used items market, listing form, seller chat, report modal
├── c2b/                # Freelance talent desk, 3-tier packages, client hiring form
├── products/           # Product detail, live search API, search results, promo coupons
├── cart/               # Cart view, quantity update, coupon discounts
├── checkout/           # Address selection, simulated payment selector, order processing
├── orders/             # Order confirmation, visual 6-stage tracker, printable tax invoice
├── services/           # Freelancer gig creation and contract management
├── messages/           # In-app buyer-seller chat and messaging center
├── public/             # CSS stylesheets, JavaScript controllers, image assets
├── tests/              # Automated PHP integration test suite & Python test runner
└── documentation/      # Complete 20 B.Tech academic project documentation files
```

---

## 🛡️ Cybersecurity & Regulatory Safeguards
1. **Password Hashing:** Implemented with PHP's native `password_hash()` (Bcrypt, work factor 10).
2. **Prepared Statements:** 100% of SQL queries executed through PDO parameter binding, preventing SQL Injection.
3. **Cross-Site Scripting (XSS) Prevention:** Output rendering sanitized with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
4. **Cross-Site Request Forgery (CSRF):** Cryptographic session tokens validated on all state-altering POST requests.
5. **Simulated Payment Safety:** Strictly labeled as academic demonstration; real credit cards, bank accounts, CVVs, and OTPs are explicitly prohibited.
6. **Audit Trail:** Critical administrative events logged with client IP address, user agent, and timestamps in `activity_logs`.

---

## 🎓 Academic Evaluation Checklist
- [x] All 4 E-Commerce business models implemented (B2B, B2C, C2C, C2B).
- [x] Normalized 3NF relational database with 30 distinct tables.
- [x] Dual MySQL (XAMPP) & SQLite fallback compatibility.
- [x] Multi-role authentication & dashboard segregation.
- [x] Working shopping cart, coupon calculation, and stock validation.
- [x] Safe simulated payment gateway and airway bill tracking.
- [x] Printable commercial tax invoice with GST breakdown.
- [x] In-app buyer-seller messaging & community listing moderation.
- [x] 100% Automated Test Suite Passing (23/23 Assertions).
- [x] Complete 20-file academic documentation package.
