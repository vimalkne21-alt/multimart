# MAJOR PROJECT COMPREHENSIVE TECHNICAL REPORT
**Title:** MULTIMART – All-in-One Multi-Model E-Commerce Marketplace  
**Academic Degree:** Bachelor of Technology (B.Tech) in Computer Science & Engineering / Data Science  
**Academic Session:** 2026–2027  

---

## 1. INTRODUCTION
Electronic commerce has fundamentally transformed global trade and consumer procurement over the past two decades. However, the ecosystem has historically evolved through disparate, isolated applications. A merchant operating in both retail and wholesale markets is typically forced to manage two independent e-commerce subscriptions. Similarly, consumers seeking to liquidate pre-owned equipment must migrate to classified advertising boards, while technical freelancers and software developers must rely on specialized gig marketplaces.

**MultiMart** is conceived and engineered as an end-to-end unified multi-model marketplace that eliminates this structural division. By integrating Business-to-Business (B2B), Business-to-Consumer (B2C), Consumer-to-Consumer (C2C), and Consumer-to-Business (C2B) commerce into a shared database and authentication infrastructure, MultiMart provides a seamless, scalable, and secure operational framework for modern commercial interactions.

---

## 2. PROBLEM STATEMENT
Current web applications suffer from multiple foundational deficiencies:
1. **Siloed Architectures:** Existing solutions (e.g. Amazon for B2C, IndiaMART/Alibaba for B2B, OLX for C2C, Upwork/Fiverr for C2B) operate entirely isolated user authentication databases and inventory catalogs.
2. **Duplicated Overhead for Vendors:** Corporate manufacturers selling both retail items and wholesale lots must deploy redundant software instances, duplicating overhead, synchronization latencies, and hosting expenses.
3. **Absence of Unified Identity:** A user cannot seamlessly switch between purchasing groceries as a consumer, procuring wholesale supplies as a business, selling a pre-owned textbook as a student, and offering programming services as a freelancer.
4. **Vulnerability to Financial Credential Exposure in Academic Environments:** Many student major projects attempt fragile mock integrations with real payment APIs that risk leaking real banking keys or collecting sensitive customer data improperly.

---

## 3. EXISTING SYSTEM VS. PROPOSED MULTIMART SYSTEM

| Dimension | Conventional Existing Systems | Proposed MultiMart Platform |
| :--- | :--- | :--- |
| **Business Model Coverage** | Single model per platform (e.g., only B2C or only B2B) | All 4 models unified (B2B, B2C, C2C, C2B) |
| **Database Structure** | Disconnected disparate schemas | Normalized 3NF relational schema (30 tables) |
| **User Authentication** | Multiple registrations required across portals | Single-Sign-On (SSO) with active role switching |
| **Wholesale RFQ Cycle** | Manual email exchanges or static inquiry forms | Complete digital RFQ workflow with validity & bidding |
| **Peer-to-Peer Safety** | Unregulated listings without grading standards | Strict condition grading, moderation, & in-app chat |
| **Payment Handling** | Fragile or dangerous exposure of dummy credentials | Safe, audited cryptographic simulated gateway |
| **Local Portability** | Complex Docker/microservice dependencies | Zero-friction local setup on XAMPP with SQLite fallback |

---

## 4. SYSTEM SCOPE & DELIVERABLES
MultiMart delivers a fully operational web application providing:
- Full-text search with asynchronous debounced live suggestions.
- Persistent multi-item cart management with automated shipping and tax calculation.
- Promotional coupon validation engine with ceiling caps and minimum thresholds.
- Safe simulated checkout with UPI, Cards, Net Banking, and COD.
- Complete order lifecycle tracking across 6 distinct stages.
- Commercial tax invoice generation with GST breakdown and digital seals.
- Dedicated role-based portals for Administrators, Corporate Sellers, Freelancers, and Customers.

---

## 5. HARDWARE & SOFTWARE SPECIFICATIONS

### Hardware Requirements
- **Development & Server Node:** Dual-Core x86_64 or ARM64 Processor (Apple Silicon / Intel / AMD) @ 2.0 GHz+.
- **RAM:** Minimum 4 GB RAM (8 GB recommended for concurrent Apache/MySQL execution).
- **Disk Storage:** 500 MB free hard disk space for codebase, database dumps, and media assets.
- **Client Devices:** Desktop, laptop, tablet, or smartphone supporting modern HTML5 web standards.

### Software Requirements
- **Operating System:** Cross-platform (macOS, Windows 10/11, Ubuntu Linux).
- **Web Server:** Apache HTTP Server 2.4+ (Standard XAMPP distribution).
- **Backend Runtime:** PHP 8.0, 8.1, 8.2, or 8.3 with PDO and OpenSSL extensions.
- **Database Engine:** MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.4+, phpMyAdmin.
- **Client Browsers:** Google Chrome, Mozilla Firefox, Apple Safari, Microsoft Edge.
- **Development Tooling:** Visual Studio Code, Git, Terminal / zsh / bash.

---

## 6. TECHNOLOGY STACK
- **Frontend Layer:** Semantic HTML5, CSS3, Bootstrap 5.3 Framework, Bootstrap Icons, Font Awesome 6, Chart.js for data visualizations, Vanilla JavaScript (ES6+).
- **Backend Architecture:** PHP 8+ procedural-modular architecture with clean separation of configuration, logic, and view components.
- **Database Layer:** Relational MySQL schema with strict Foreign Key constraints and SQLite3 fallback engine.
- **Security Primitives:** Native Bcrypt hashing (`password_hash()`), cryptographic tokens (`random_bytes()`), PDO parameterized queries, and output escaping (`htmlspecialchars()`).

---

## 7. SYSTEM ARCHITECTURE
MultiMart follows a clean 3-Tier Layered Architecture:
1. **Presentation Tier (Client):** Responsive HTML5/Bootstrap templates, dynamic CSS styles, asynchronous AJAX handlers, and Chart.js dashboards.
2. **Application / Business Logic Tier (Server):** Modular PHP handlers (`auth/`, `b2b/`, `b2c/`, `c2c/`, `c2b/`, `cart/`, `checkout/`, `admin/`, `seller/`, `customer/`).
3. **Data Persistence Tier (Storage):** 30 normalized relational tables in MySQL/MariaDB with referential integrity rules and automatic local SQLite backup.

---

## 8. THE FOUR E-COMMERCE BUSINESS MODELS

### Model A: Business-to-Consumer (B2C)
- Retail consumer storefront where verified corporate and individual vendors sell brand-new merchandise.
- Features category browsing, multi-parameter price and brand filtering, customer reviews and star ratings, add-to-cart, wishlist persistence, and promotional discount application.

### Model B: Business-to-Business (B2B)
- Wholesale supply portal designed for bulk procurement.
- Enforces Minimum Order Quantities (MOQs), displays bulk lot pricing and tiered volume discounts.
- Implements a Request for Quotation (RFQ) cycle: Buyer inputs required quantity, target price, and technical specs -> Supplier reviews RFQ -> Supplier issues legally binding digital quote with expiry date -> Buyer accepts quote and confirms contract order.

### Model C: Consumer-to-Consumer (C2C)
- Peer-to-peer pre-owned marketplace designed for secondary item re-sale (textbooks, electronics, study desks).
- Standardized condition classifications: *Brand New*, *Like New*, *Good Condition*, *Fair Condition*.
- Features geographical location tags, direct in-app buyer-seller messaging, and community moderation flagging.

### Model D: Consumer-to-Business (C2B)
- Professional freelancing marketplace where independent technical specialists contract directly with businesses.
- Three standardized engagement tiers:
  - **Basic Tier:** Prototype script and exploratory data visualizations.
  - **Standard Tier:** Production-grade pipeline, model tuning, and REST API endpoints.
  - **Premium Tier:** Enterprise containerization, analytical dashboards, and priority support.
- Milestone tracking across states: *Requested -> Hired -> In Progress -> Under Review -> Completed*.

---

## 9. DATABASE ARCHITECTURE (30 NORMALIZED TABLES)
The database schema strictly adheres to Third Normal Form (3NF) to eliminate data redundancy and insertion/deletion anomalies:
1. `roles` – Platform authorization levels.
2. `users` – User authentication and credentials.
3. `user_addresses` – Normalized delivery and billing addresses.
4. `businesses` – Corporate registry and GST metadata.
5. `business_verifications` – Institutional audit records.
6. `categories` – Taxonomy classification across models.
7. `products` – Multi-model product listings.
8. `product_images` – Multi-image gallery associations.
9. `product_inventory` – Stock mutation ledger.
10. `carts` – User shopping baskets.
11. `cart_items` – Individual items staged in cart.
12. `wishlists` – User item bookmarks.
13. `wishlist_items` – Product bookmarks mapping.
14. `orders` – Consolidated multi-model orders.
15. `order_items` – Line items bound to orders.
16. `payments` – Simulated financial transaction logs.
17. `transactions` – Financial ledger accounting entries.
18. `coupons` – Promotional voucher validation rules.
19. `reviews` – Verified customer feedback and ratings.
20. `notifications` – Real-time internal event dispatches.
21. `business_quotes` – B2B Request for Quotation (RFQ) records.
22. `quote_items` – Line items in B2B bids.
23. `services` – Freelancer professional offerings.
24. `service_requests` – Client project contracts.
25. `proposals` – Freelancer bids and milestone schedules.
26. `conversations` – In-app messaging threads.
27. `messages` – Individual message dispatches.
28. `reports` – Community moderation flags.
29. `returns` – Order refund requests and items received.
30. `activity_logs` – Cybersecurity audit trail.

---

## 10. CYBERSECURITY CONTROLS
- **SQL Injection Defense:** Zero dynamic string concatenations in database queries. 100% of statements execute via PDO parameterized placeholders (`?`).
- **XSS Mitigation:** All dynamic variables rendered in HTML are sanitized through the custom `e()` helper invoking `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')`.
- **CSRF Token Validation:** Every state-altering HTTP POST request incorporates a 64-character cryptographic token validated using `hash_equals()`.
- **Credential Storage:** All user passwords are encrypted using PHP native Bcrypt (`PASSWORD_BCRYPT`, cost factor 10).
- **Session Security:** Session cookies are configured with `httponly=1`, `use_only_cookies=1`, and `samesite=Lax`.

---

## 11. TESTING & RESULTS
The application underwent automated integration testing via `tests/run_tests.py` and `tests/test_suite.php`. 
- **Total Automated Assertions Executed:** 23
- **Passed:** 23 (100.0%)
- **Failed:** 0 (0.0%)
- **Functional Verification Areas:** Database schema completeness, dual MySQL/SQLite engine connectivity, user role segregation, B2B RFQ creation, B2C cart computations, coupon discounts, simulated payment references, in-app messaging, and cybersecurity audit logging.

---

## 12. SCREENSHOT INSTRUCTIONS FOR EVALUATORS
When capturing screenshots for academic viva presentation slides:
1. **Homepage Showcase:** Capture `index.php` showing the hero banner, 4-model hub cards, and product carousels.
2. **B2B Wholesale & RFQ:** Capture `b2b/index.php` and `b2b/rfq_create.php` highlighting MOQ badges and quote bidding.
3. **B2C Product & Cart:** Capture `products/detail.php` and `cart/index.php` demonstrating coupon discount deduction.
4. **Simulated Payment Gateway:** Capture `checkout/index.php` showing simulated payment options and demo notices.
5. **Printable Invoice:** Capture `orders/invoice.php` showcasing the commercial tax invoice with GST breakdown.
6. **Admin Dashboard:** Capture `admin/dashboard.php` showing Chart.js GMV bar charts and order status breakdowns.
7. **Freelancer Workspace:** Capture `services/dashboard.php` showcasing active service contracts.

---

## 13. LIMITATIONS
- Financial transactions are strictly simulated in safe demo mode and do not process real credit card payments.
- Real-time chat operates via asynchronous polling and database storage rather than raw persistent WebSockets.
- Email verification uses in-app simulation tokens rather than an external live SMTP relay.

---

## 14. FUTURE ENHANCEMENTS
- Integration with legitimate production payment gateways (Razorpay, Stripe, or PayPal Sandbox).
- Machine learning recommendation engine utilizing collaborative filtering algorithms based on customer browsing vectors.
- Native mobile applications developed with React Native or Flutter communicating with MultiMart's RESTful endpoints.
- Real-time bi-directional WebSockets integration via Ratchet or Node.js for live typing indicators in chat.

---

## 15. CONCLUSION
The MultiMart project successfully demonstrates the viability of unifying all four foundational e-commerce archetypes—B2B, B2C, C2C, and C2B—within a single, performant, normalized, and secure web application. Engineered to B.Tech major project academic standards, it provides complete frontend reactivity, modular backend logic, full database normalization, and robust cybersecurity safeguards.
