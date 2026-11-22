# FORMAL TEST CASES SPECIFICATION
**Project Title:** MultiMart Multi-Model E-Commerce Platform  
**Testing Framework:** Unit, Integration, Regression, and Security Test Suites  

---

| Test ID | Module / Feature | Test Description | Test Steps | Expected Result | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-01** | Database Engine | Dual Driver Detection | Initialize PDO via `db.php` | Successfully binds to MySQL or SQLite fallback | **PASS** |
| **TC-02** | Schema Integrity | 30 Tables Normalization | Query table master list | All 30 required relational tables exist | **PASS** |
| **TC-03** | Auth System | User Registration | POST to `auth/register.php` with valid data | New user record created with Bcrypt password hash | **PASS** |
| **TC-04** | Auth System | Password Hashing | Verify `password_verify()` on stored Bcrypt | Plaintext password matches encrypted hash | **PASS** |
| **TC-05** | Security | Role-Based Access Control | Attempt accessing `admin/dashboard.php` as customer | Access denied and redirected to home | **PASS** |
| **TC-06** | B2C Catalog | Product Indexing | Browse `b2c/index.php` | Retail products render with price, image, rating | **PASS** |
| **TC-07** | B2C Search | Live Autocomplete API | Send GET query to `products/search_api.php?q=head` | Returns JSON array matching Quantum Pro headphones | **PASS** |
| **TC-08** | Shopping Cart | Add Item to Basket | Send POST to `cart/cart_action.php` with `action=add` | Item staged in cart and badge counter increments | **PASS** |
| **TC-09** | Cart Engine | Coupon Discount Computation | Apply `WELCOME10` on cart totaling ₹ 3,999.00 | Exactly 10% (₹ 399.90) deducted from subtotal | **PASS** |
| **TC-10** | Checkout | Address Resolution | Select address on `checkout/index.php` | Delivery address captured in order snapshot | **PASS** |
| **TC-11** | Payment Gateway | Simulated Transaction Code | Confirm checkout with simulated UPI | Generates unique `TXN_SIM_...` reference code | **PASS** |
| **TC-12** | Inventory | Stock Quantity Deduction | Place order for quantity of 1 | Product warehouse stock decrements by exactly 1 | **PASS** |
| **TC-13** | Orders | Order Confirmation Screen | Redirect to `orders/confirmation.php` | Displays order #, waybill, and invoice print link | **PASS** |
| **TC-14** | Orders | Consignment Life Cycle Tracker | Navigate to `orders/track.php` | Renders visual 6-stage tracker | **PASS** |
| **TC-15** | Invoicing | Commercial Tax Invoice | Open `orders/invoice.php?order=...` | Renders printable GST tax invoice with seal | **PASS** |
| **TC-16** | B2B Wholesale | Minimum Order Quantity (MOQ) | Attempt RFQ submission below MOQ | Form blocks submission with validation error | **PASS** |
| **TC-17** | B2B Wholesale | RFQ Negotiation Lifecycle | Buyer requests quote -> Seller quotes rate -> Buyer accepts | Status transitions: Requested -> Responded -> Accepted | **PASS** |
| **TC-18** | C2C Market | Pre-Owned Listing | Post used textbook on `c2c/sell.php` | Item appears on C2C market with condition tag | **PASS** |
| **TC-19** | C2B Talent | Freelance Service Packages | View `c2b/detail.php?id=1` | Basic, Standard, and Premium packages render | **PASS** |
| **TC-20** | C2B Talent | Client Hire Contract | Submit project proposal on `c2b/hire.php` | Contract created in `service_requests` table | **PASS** |
| **TC-21** | In-App Messaging | Direct Buyer-Seller Chat | Send message on `messages/chat.php` | Message logged in `messages` and partner notified | **PASS** |
| **TC-22** | Moderation | Community Report Submission | File report on `c2c/report.php` | Incident record logged in `reports` with pending status | **PASS** |
| **TC-23** | Security | CSRF Protection | Submit POST request with invalid CSRF token | Transaction rejected with security mismatch error | **PASS** |
| **TC-24** | Security | Cross-Site Scripting (XSS) | Input `<script>alert('xss')</script>` in title | Rendered as escaped entities `&lt;script&gt;` | **PASS** |
| **TC-25** | Admin Portal | Executive GMV Analytics | Load `admin/dashboard.php` | Chart.js renders model revenue distribution | **PASS** |
