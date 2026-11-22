# ENTITY-RELATIONSHIP (ER) DIAGRAM
**Project Title:** MultiMart Multi-Model Database Architecture  

---

```mermaid
erDiagram
    ROLES ||--o{ USERS : "assigned_to"
    USERS ||--o{ USER_ADDRESSES : "maintains"
    USERS ||--o| BUSINESSES : "owns"
    BUSINESSES ||--o{ BUSINESS_VERIFICATIONS : "submits"
    CATEGORIES ||--o{ PRODUCTS : "classifies"
    USERS ||--o{ PRODUCTS : "lists"
    PRODUCTS ||--o{ PRODUCT_IMAGES : "contains"
    PRODUCTS ||--o{ PRODUCT_INVENTORY : "tracks"
    
    USERS ||--o| CARTS : "has"
    CARTS ||--o{ CART_ITEMS : "holds"
    PRODUCTS ||--o{ CART_ITEMS : "referenced_in"
    
    USERS ||--o| WISHLISTS : "saves"
    WISHLISTS ||--o{ WISHLIST_ITEMS : "contains"
    PRODUCTS ||--o{ WISHLIST_ITEMS : "bookmarked_in"
    
    USERS ||--o{ ORDERS : "places"
    ORDERS ||--o{ ORDER_ITEMS : "composed_of"
    PRODUCTS ||--o{ ORDER_ITEMS : "purchased_as"
    ORDERS ||--o| PAYMENTS : "settled_via"
    PAYMENTS ||--o{ TRANSACTIONS : "recorded_as"
    
    USERS ||--o{ REVIEWS : "writes"
    PRODUCTS ||--o{ REVIEWS : "evaluated_by"
    
    USERS ||--o{ NOTIFICATIONS : "receives"
    
    USERS ||--o{ BUSINESS_QUOTES : "requests_as_buyer"
    PRODUCTS ||--o{ BUSINESS_QUOTES : "quoted_for"
    BUSINESS_QUOTES ||--o{ QUOTE_ITEMS : "details"
    
    USERS ||--o{ SERVICES : "publishes_as_freelancer"
    CATEGORIES ||--o{ SERVICES : "categorizes"
    USERS ||--o{ SERVICE_REQUESTS : "hires_as_client"
    SERVICES ||--o{ SERVICE_REQUESTS : "engaged_in"
    SERVICE_REQUESTS ||--o{ PROPOSALS : "bidded_with"
    
    USERS ||--o{ CONVERSATIONS : "participates_in"
    CONVERSATIONS ||--o{ MESSAGES : "contains"
    
    USERS ||--o{ REPORTS : "files"
    ORDERS ||--o{ RETURNS : "disputed_in"
    USERS ||--o{ ACTIVITY_LOGS : "audited_in"
```

---

## Relationship Cardinalities
1. **One-to-Many (`1 : N`):**
   - `roles` to `users`: One role can be held by multiple users.
   - `categories` to `products`: One category contains many products across models.
   - `products` to `product_images`: One product holds multiple images.
   - `orders` to `order_items`: One order contains multiple line items.
   - `conversations` to `messages`: One conversation thread holds multiple messages.
2. **One-to-One (`1 : 1`):**
   - `users` to `businesses`: A registered business seller owns one corporate profile.
   - `users` to `carts`: A user possesses one active shopping cart instance.
   - `orders` to `payments`: An order is settled via a primary simulated payment transaction.
