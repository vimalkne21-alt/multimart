# PROJECT OBJECTIVES
**Project Title:** MultiMart: All-in-One Multi-Model E-Commerce Marketplace  
**Program:** B.Tech Computer Science & Engineering / Data Science  

---

## 🎯 Primary Project Objectives

1. **Multi-Model Architectural Convergence:**
   - Design and implement a single web-scale software architecture capable of simultaneously supporting four primary commerce workflows:
     - **B2B:** Minimum Order Quantities, Wholesale Pricing Tiers, and Request for Quotation (RFQ) bidding cycles.
     - **B2C:** Direct-to-consumer catalog browsing, cart persistence, promotional coupons, and simulated payments.
     - **C2C:** Peer-to-peer used item listings with condition grading, location metadata, and direct messaging.
     - **C2B:** Professional technical freelancing desk with tiered project contracts and milestone deliverables.

2. **Normalized Relational Database Engineering:**
   - Architect a highly normalized relational database adhering to Third Normal Form (3NF) across 30 tables.
   - Enforce referential integrity using foreign keys, cascading deletions, unique constraints, and optimized index trees.
   - Ensure native compatibility with MySQL/MariaDB (XAMPP environment) as well as an automatic embedded SQLite3 fallback.

3. **Role-Based Access Control (RBAC):**
   - Implement secure session governance isolating permissions across 5 distinct system roles:
     1. Platform Administrator
     2. Business Seller (B2B & B2C)
     3. Individual Seller (C2C)
     4. Consumer / Customer
     5. Freelancer / Service Provider (C2B)
   - Ensure complete data isolation so vendors and freelancers only access their respective records.

4. **Cryptographic Security & Defense-in-Depth:**
   - Eliminate SQL Injection vulnerabilities by routing 100% of queries through PDO prepared statements with parameter binding.
   - Mitigate Cross-Site Scripting (XSS) via universal entity escaping (`htmlspecialchars(..., ENT_QUOTES)`).
   - Mitigate Cross-Site Request Forgery (CSRF) via session-bound anti-forgery cryptographic tokens on state-altering POST endpoints.
   - Protect credentials using native Bcrypt password hashing (`password_hash()`, cost factor 10).
   - Maintain an automated cybersecurity audit trail logging administrative and user actions with IP addresses and user agents.

5. **Safe Financial Gateway Simulation:**
   - Design an educational simulated payment processing engine supporting UPI, Debit/Credit Card, Net Banking, and COD.
   - Simulate realistic latency, authorization codes, and double-entry transaction ledgers without collecting or storing real banking card numbers, CVVs, or OTPs.

6. **Interactive Responsive User Experience:**
   - Develop an intuitive, accessible, and responsive user interface using HTML5, modern CSS3, and Bootstrap 5.3.
   - Integrate asynchronous AJAX features including debounced live search suggestions, real-time cart counter updates, and interactive Chart.js analytical dashboards.

7. **Comprehensive Academic Testing & Verification:**
   - Formulate and execute automated unit, integration, and security test suites achieving 100% test pass rates across all application modules.
   - Produce complete technical documentation, architectural blueprints, schema diagrams, and administrative manuals for engineering accreditation.
