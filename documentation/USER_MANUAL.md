# USER MANUAL & OPERATING GUIDE
**Project Title:** MultiMart Multi-Model E-Commerce Platform  

---

## 1. Navigating the MultiMart Ecosystem
The MultiMart top navigation bar enables rapid switching between all four commerce portals:
- **All Portals (`index.php`):** Universal landing page highlighting featured retail items, wholesale supplies, pre-owned items, and professional services.
- **B2C Retail (`b2c/index.php`):** Consumer catalog with category filters, price range sliders, and sorting.
- **B2B Wholesale (`b2b/index.php`):** Bulk industrial lots with MOQ tags and RFQ triggers.
- **C2C Pre-Owned (`c2c/index.php`):** Used items with condition filters and localized pickup details.
- **C2B Services (`c2b/index.php`):** Technical freelance talent categorized by specialized domains.

---

## 2. Customer Workflow (B2C Retail & Services)
1. **Browse or Search:** Use the sticky search bar to find products with live autocomplete suggestions.
2. **Add to Cart:** Select quantity and click **Add to Cart**. A toast confirmation will appear and the cart badge increments.
3. **Apply Promo Coupon:** On the Cart page (`cart/index.php`), enter coupon `WELCOME10` or `MULTISAVE20` and click **Apply** to deduct discounts immediately.
4. **Checkout:** Select or enter a delivery address, choose your simulated payment method (UPI, Card, Net Banking, or COD), and click **Confirm & Process Order**.
5. **Tracking & Invoice:** View your consignment trajectory on `orders/track.php` or download a commercial tax invoice on `orders/invoice.php`.

---

## 3. Business Buyer Workflow (B2B Wholesale & RFQ)
1. Navigate to **B2B Wholesale** (`b2b/index.php`).
2. Identify a wholesale lot and click **Request Quote (RFQ)**.
3. Enter required quantity (minimum must satisfy the item's MOQ), target price per unit, and delivery specifications.
4. Review submitted quotes on **My RFQs** (`b2b/rfq_list.php`).
5. When the supplier responds with an offered rate, click **Accept Quote** to establish the commercial contract.

---

## 4. Individual Consumer Workflow (C2C Pre-Owned Selling)
1. Sign in to your account and click **Sell Used Item** (`c2c/sell.php`).
2. Enter item title, select appropriate C2C category, select condition (*Like New*, *Good*, *Fair*), set asking price, specify your city/state, and write description notes.
3. Click **Publish Pre-Owned Listing**. Your listing goes live instantly on the C2C marketplace.
4. Potential buyers can reach you via in-app messaging (`messages/inbox.php`).

---

## 5. Freelancer Workflow (C2B Skill Offering)
1. Sign in with a Freelancer account and access the **Freelancer Workspace** (`services/dashboard.php`).
2. Click **Create New Gig** (`services/create.php`) to define your service title, skills, GitHub portfolio URL, and 3 package tier prices (Basic, Standard, Premium).
3. When clients submit contracts, view incoming projects in your workspace and update milestone statuses (*In Progress -> Under Review -> Completed*).
