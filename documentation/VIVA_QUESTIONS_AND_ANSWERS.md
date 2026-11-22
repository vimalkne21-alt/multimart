# B.TECH MAJOR PROJECT VIVA QUESTIONS & ANSWERS
**Project Title:** MULTIMART – All-in-One Multi-Model E-Commerce Marketplace  
**Domain:** Full-Stack Web Engineering, Database Systems & Cybersecurity  

---

### Q1. What is the fundamental novelty of MultiMart compared to standard e-commerce projects?
**Answer:** Most academic e-commerce projects are simple B2C clones that only allow browsing products and adding them to a cart. MultiMart unifies all four major business models on a single normalized relational database schema (30 tables) and single-sign-on authentication:
1. **B2B (Business-to-Business):** Minimum Order Quantities (MOQ), tiered bulk pricing, and formal Request for Quotation (RFQ) negotiation cycles.
2. **B2C (Business-to-Consumer):** Retail consumer shopping with full-text search, persistent cart, coupons, and tracking.
3. **C2C (Consumer-to-Consumer):** Peer-to-peer used items market with condition grading (*Like New*, *Good*, *Fair*) and in-app buyer chat.
4. **C2B (Consumer-to-Business):** Freelancing marketplace where specialists offer skills with 3-tier engagement packages (Basic, Standard, Premium).

---

### Q2. How is your database designed and how many tables are implemented?
**Answer:** The database consists of 30 normalized tables adhering to Third Normal Form (3NF). It eliminates data anomalies by decoupling users, corporate business profiles, categories, products, inventory mutations, carts, wishlists, orders, payments, transactions, coupons, reviews, notifications, RFQs, service contracts, messaging, reports, returns, and activity audit logs. Referential integrity is strictly enforced with primary and foreign keys.

---

### Q3. Why did you choose PHP 8+ and PDO rather than a heavy full-stack framework?
**Answer:** The choice was deliberate for maximum maintainability, portability, and performance:
1. **Zero-Friction Local Deployment:** Running on standard XAMPP requires zero heavy package managers, container compilation, or complex Node build steps.
2. **Security through Native PDO:** PHP Data Objects (PDO) enforces parameterized prepared statements across 100% of queries, guaranteeing immunity against SQL Injection attacks.
3. **Dual Database Engine Resilience:** By using PDO, MultiMart cleanly abstracts the database layer, allowing seamless execution on MySQL/MariaDB in XAMPP or immediate fallback to embedded SQLite3 without changing application code.

---

### Q4. How does the simulated payment gateway work, and why not use real bank credentials?
**Answer:** In academic evaluative settings, requesting or storing real banking card numbers, CVVs, UPI PINs, or OTPs violates basic cybersecurity best practices and creates liability. MultiMart implements a simulated safe payment gateway that models the real transaction lifecycle:
- It supports UPI, Credit/Debit Card, Net Banking, Digital Wallet, and Cash on Delivery.
- It validates stock before deduction, generates unique cryptographic transaction references (e.g. `TXN_SIM_...`), records double-entry ledger entries in `transactions`, transitions order fulfillment to `Confirmed`, decrements inventory, and issues printable commercial tax invoices with GSTIN and digital seals.

---

### Q5. How does MultiMart protect against OWASP Top 10 vulnerabilities?
**Answer:**
1. **SQL Injection (A03:2021):** Completely prevented via PDO prepared statements with parameter binding.
2. **Broken Access Control (A01:2021):** Enforced via server-side session role middleware (`require_role()`), preventing vertical or horizontal privilege escalation.
3. **Cryptographic Failures (A02:2021):** User passwords are encrypted using native Bcrypt with a work factor cost of 10 (`password_hash()`), never serialized in cleartext.
4. **Cross-Site Scripting (XSS - A03:2021):** Output escaping helper `e()` invokes `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` on all user-supplied content.
5. **Cross-Site Request Forgery (CSRF - A01:2021):** Cryptographic session tokens (`csrf_token()`) are validated using `hash_equals()` on all POST operations.
6. **Security Logging & Monitoring (A09:2021):** Administrative events, authentication failures, and order settlements are logged with IP addresses and user agents in `activity_logs`.

---

### Q6. Walk me through the B2B Request for Quotation (RFQ) cycle.
**Answer:**
1. A buyer discovers a wholesale product on the B2B portal (`b2b/index.php`).
2. The buyer inputs a requested quantity (which must be $\ge$ the product's Minimum Order Quantity) and target budget, submitting an RFQ (`b2b/rfq_create.php`).
3. An RFQ record is created in `business_quotes` with status `requested`, and the supplier receives an internal notification.
4. The supplier accesses their Seller RFQ Desk (`seller/rfqs.php`), reviews the request, calculates institutional taxes/shipping, inputs an offered quote price, specifies a quote validity expiration date, and submits the bid (`status = 'responded'`).
5. The buyer reviews the quote on `b2b/rfq_list.php` and clicks **Accept Quote**, transitioning the status to `accepted` and creating a confirmed commercial order.

---

### Q7. How does the C2B Freelancing module operate?
**Answer:**
- In the C2B model, individuals provide skills to corporate entities.
- Freelancers create professional profiles detailing skills and GitHub portfolio URLs, listing services with three standardized package tiers: Basic (Proof-of-Concept), Standard (Production Ready), and Premium (Enterprise Deployment).
- Business clients select a package tier, enter project requirements and deadlines, and establish a service contract in `service_requests`.
- The freelancer tracks project milestones (*Hired -> In Progress -> Under Review -> Completed*) directly in the Freelancer Workspace (`services/dashboard.php`).

---

### Q8. What happens during checkout if an item in the cart runs out of stock?
**Answer:** MultiMart executes pre-transaction stock validation. Before establishing the order record or deducting payment, the checkout processor queries the live stock quantity in `products`. If stock is insufficient, the transaction is rejected with an informative error message, preventing overselling anomalies. Once confirmed, stock is immediately decremented and an entry is recorded in `product_inventory`.

---

### Q9. How do you calculate discounts when a coupon is applied?
**Answer:** Coupons support both `percentage` and `fixed` discount models stored in the `coupons` table. The calculation engine verifies:
1. That the coupon is active and has not expired.
2. That the cart subtotal meets the coupon's `min_spend` threshold.
3. If percentage-based, the discount is calculated as $\text{Subtotal} \times \frac{\text{Value}}{100}$, with the result capped at `max_discount`.
4. If fixed-based, the exact face value is deducted from the subtotal.

---

### Q10. What are the key limitations and prospective future enhancements?
**Answer:**
- **Limitations:** Financial gateway operates in simulated academic demo mode; messaging uses database persistence rather than WebSockets.
- **Future Scope:** Integrating machine learning collaborative filtering algorithms for personalized product recommendations, WebSockets for live typing indicators, and mobile applications built with Flutter or React Native communicating via MultiMart's RESTful endpoints.
