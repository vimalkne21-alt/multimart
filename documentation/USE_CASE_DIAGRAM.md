# USE CASE SPECIFICATION & DIAGRAMS
**Project Title:** MultiMart: All-in-One Multi-Model E-Commerce Marketplace  

---

## 1. Unified System Use Case Diagram

```mermaid
flowchart LR
    subgraph Actors["System Actors"]
        Admin["Administrator"]
        BizSeller["Business Seller"]
        IndSeller["Individual Seller"]
        Customer["Customer / Buyer"]
        Freelancer["Freelancer"]
    end

    subgraph UseCases["MultiMart Platform Operations"]
        UC1["Sign In / Register / Recover Password"]
        UC2["Search Catalog with Autocomplete"]
        UC3["Manage Shopping Cart & Apply Coupons"]
        UC4["Checkout via Simulated Safe Gateway"]
        UC5["Track Consignment Lifecycle"]
        UC6["Print Commercial Tax Invoice"]
        UC7["Publish Wholesale Listings & MOQ"]
        UC8["Submit & Negotiate B2B RFQs"]
        UC9["List Pre-Owned C2C Item"]
        UC10["Direct Buyer-Seller Messaging"]
        UC11["Publish Freelance Service Gigs"]
        UC12["Hire Freelancers on Milestone Contract"]
        UC13["Moderate Listings & Review Reports"]
        UC14["View GMV Analytics & Audit Logs"]
    end

    Customer --> UC1 & UC2 & UC3 & UC4 & UC5 & UC6 & UC8 & UC10 & UC12
    BizSeller --> UC1 & UC7 & UC8 & UC10 & UC14
    IndSeller --> UC1 & UC9 & UC10
    Freelancer --> UC1 & UC10 & UC11 & UC12
    Admin --> UC1 & UC2 & UC13 & UC14
```

---

## 2. Actor Functional Responsibilities

| Actor | Access Scope & Primary Operations |
| :--- | :--- |
| **Customer** | Browse B2C retail goods, manage cart, apply coupons, simulate payment, track orders, message sellers, submit B2B RFQs, hire freelancers. |
| **Business Seller** | Manage corporate profile, list wholesale items with MOQ, manage warehouse inventory, respond to customer orders, submit RFQ bids. |
| **Individual Seller** | List pre-owned merchandise with condition ratings, set asking prices, communicate with local buyers, mark items sold out. |
| **Freelancer** | Publish service offerings with 3-tier packages, manage incoming project contracts, deliver project milestones, receive ratings. |
| **Administrator** | Master oversight, user role modifications, catalog approvals, order dispatch overrides, dispute resolution, review audit logs. |
