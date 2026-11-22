# ADMINISTRATOR OPERATING MANUAL
**Project Title:** MultiMart Master Control Center  

---

## 1. Accessing Administrative Controls
- Authenticate using the SuperAdmin credentials:
  - **Email:** `admin@multimart.com`
  - **Password:** `Admin@123`
- Direct Portal Link: **`http://localhost/MultiMart/admin/dashboard.php`**

---

## 2. Administrative Operations

### A. Executive Dashboard Overview (`admin/dashboard.php`)
- Review total registered users, corporate sellers, catalog listings, order volume, and gross merchandise volume (GMV).
- Analyze real-time Chart.js interactive bar charts showcasing revenue distribution across the four e-commerce models (B2C, B2B, C2C, C2B).

### B. User & Role Management (`admin/users.php`)
- Inspect all registered accounts, contact emails, assigned roles, and registration dates.
- To re-assign roles: Select a new role from the dropdown; the change persists automatically.
- To suspend an abusive account: Click **Suspend**; the user is immediately barred from authentication and protected routes.

### C. Catalog & Listing Moderation (`admin/products.php`)
- Review all product listings across retail, wholesale, and secondary markets.
- Toggle listing status between **Active** and **Inactive** to hide inappropriate items.
- Permanently purge fraudulent listings using the **Delete** action.

### D. Global Order & Dispatch Tracking (`admin/orders.php`)
- Monitor global purchase consignments and airway bill numbers.
- Transition order statuses through their formal lifecycle: *Confirmed -> Processing -> Shipped -> Out for Delivery -> Delivered*.

### E. Marketplace Taxonomy (`admin/categories.php`)
- Create new categories assigned to specific e-commerce business models (e.g. B2C Retail, B2B Wholesale, C2C Used, C2B Services).

### F. Moderation & Incident Reports (`admin/reports.php`)
- Review community reports submitted against misleading pre-owned listings.
- Action reports by clicking **De-List Item** (automatically disables the product) or **Dismiss**.

### G. Cybersecurity Audit Trail (`admin/activity_logs.php`)
- Review chronological audit logs capturing actor IDs, action codes, IP addresses, user agents, and timestamps for governance and forensics.
