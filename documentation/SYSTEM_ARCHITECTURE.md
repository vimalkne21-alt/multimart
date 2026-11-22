# SYSTEM ARCHITECTURE SPECIFICATION
**Project Title:** MultiMart: All-in-One Multi-Model E-Commerce Marketplace  

---

## 1. High-Level Architectural Model
MultiMart is engineered as a robust **3-Tier Distributed Architecture** featuring a clean separation of concerns:

```mermaid
flowchart TD
    subgraph ClientLayer["Tier 1: Client Presentation Layer"]
        B["Web Browser / Smartphone / Tablet"]
        UI["Bootstrap 5 UI + Vanilla JS + Chart.js"]
    end

    subgraph AppLayer["Tier 2: Business Logic & Application Layer (PHP 8+)"]
        Router["HTTP Request Router & Base URL Handler"]
        AuthMiddleware["RBAC Authentication & Session Validator"]
        SecurityLayer["CSRF Validator & XSS Sanitizer"]
        
        subgraph Modules["E-Commerce Business Engines"]
            B2C["B2C Retail Engine"]
            B2B["B2B Wholesale & RFQ Engine"]
            C2C["C2C Pre-Owned Market Engine"]
            C2B["C2B Freelance Talent Engine"]
            CartCheckout["Cart, Coupon & Checkout Processor"]
            SimPayment["Safe Simulated Payment Gateway"]
        end
    end

    subgraph DataLayer["Tier 3: Persistence & Database Layer"]
        PDOLayer["PHP Data Objects (PDO) Prepared Statement Interface"]
        MySQL[("MySQL 8.0 / MariaDB (XAMPP Primary)")]
        SQLite[("SQLite3 Embedded Fallback")]
    end

    B --> UI
    UI -->|HTTPS / HTTP REST| Router
    Router --> AuthMiddleware
    AuthMiddleware --> SecurityLayer
    SecurityLayer --> Modules
    Modules --> PDOLayer
    PDOLayer --> MySQL
    PDOLayer -.->|Fallback| SQLite
```

---

## 2. Architectural Layers

### Tier 1: Client Presentation Layer
- **Responsive Layout:** Responsive grid adapting dynamically to smartphones, tablets, and desktops.
- **Asynchronous AJAX Interactivity:**
  - Live search suggestions with debounce to optimize server load.
  - Asynchronous add-to-cart operations updating cart badge counters without page reloads.
  - Interactive Chart.js graphs displaying revenue distribution across models.

### Tier 2: Application / Business Logic Layer (PHP 8+)
- **Security Interceptor:** Validates session credentials, role permissions, and CSRF nonces before processing requests.
- **Modular Engines:**
  - `B2C Engine`: Catalogs, search, product reviews, cart management.
  - `B2B Engine`: Wholesale master cartons, Minimum Order Quantities (MOQ), and RFQ negotiations.
  - `C2C Engine`: Consumer listings, condition grading, and localized pickup tags.
  - `C2B Engine`: Freelancer profiles, 3-tier engagement packages, and contract milestones.
  - `Simulated Payment Gateway`: Processes simulated UPI, Card, Net Banking, and COD authorizations.

### Tier 3: Persistence Layer (Data Store)
- **Universal PDO Abstraction:** Ensures transparent SQL execution across both MySQL and SQLite.
- **Strict 3NF Normalization:** 30 tables with strict foreign key constraints, cascading deletions, and audit trails.
