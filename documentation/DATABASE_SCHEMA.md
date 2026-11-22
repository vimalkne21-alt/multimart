# DATABASE SCHEMA DOCUMENTATION
**Database Name:** `multimart_db`  
**Standard:** 3NF (Third Normal Form) Relational Database Schema  
**Total Tables:** 30  

---

### 1. `roles`
Defines system access authorization tiers.
- `id` (INT, Primary Key, Auto Increment)
- `name` (VARCHAR 50, UNIQUE) — e.g. `admin`, `business_seller`, `individual_seller`, `customer`, `freelancer`.
- `display_name` (VARCHAR 100) — Human-readable role label.
- `description` (TEXT)
- `created_at` (TIMESTAMP)

### 2. `users`
Core user entity across all four commercial models.
- `id` (INT, Primary Key, Auto Increment)
- `role_id` (INT, Foreign Key -> `roles.id`)
- `full_name` (VARCHAR 150)
- `email` (VARCHAR 191, UNIQUE)
- `password_hash` (VARCHAR 255) — Bcrypt encrypted hash.
- `phone` (VARCHAR 25)
- `avatar` (VARCHAR 255)
- `status` (ENUM: `active`, `pending`, `suspended`)
- `email_verified` (TINYINT 1)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 3. `user_addresses`
Physical shipping and billing locations.
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key -> `users.id`)
- `address_type` (ENUM: `home`, `office`, `warehouse`, `business`)
- `recipient_name`, `phone`, `street_address`, `city`, `state`, `postal_code`, `country`
- `is_default` (TINYINT 1)

### 4. `businesses`
Corporate metadata for B2B vendors and institutional buyers.
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key -> `users.id`)
- `company_name`, `business_type`, `registration_number`, `tax_id_gst`, `official_email`, `phone`, `address`, `website`
- `verified_status` (ENUM: `verified`, `pending`, `rejected`)

### 5. `business_verifications`
Document audits for corporate vendors.
- `id`, `business_id`, `document_type`, `document_file`, `verification_status`, `reviewed_by`, `review_notes`, `submitted_at`, `reviewed_at`

### 6. `categories`
Hierarchical category structure spanning all 4 models.
- `id`, `name`, `slug`, `description`, `icon`, `parent_id`
- `module_type` (ENUM: `ALL`, `B2B`, `B2C`, `C2C`, `C2B`)
- `status` (ENUM: `active`, `inactive`)

### 7. `products`
Consolidated product catalog supporting B2C retail, B2B wholesale, and C2C pre-owned listings.
- `id`, `seller_id`, `business_id`, `category_id`, `title`, `slug`, `sku`, `short_description`, `description`
- `price` (DECIMAL 12,2), `discount_price` (DECIMAL 12,2)
- `stock_quantity` (INT), `min_order_qty` (INT)
- `bulk_price` (DECIMAL 12,2)
- `is_b2b` (TINYINT 1), `is_b2c` (TINYINT 1), `is_c2c` (TINYINT 1)
- `condition_type` (ENUM: `brand_new`, `like_new`, `good`, `fair`)
- `location`, `status`, `views_count`, `created_at`, `updated_at`

### 8. `product_images`
Image gallery for products.
- `id`, `product_id`, `image_url`, `is_primary`, `display_order`, `created_at`

### 9. `product_inventory`
Historical stock mutations ledger.
- `id`, `product_id`, `change_type`, `quantity`, `remaining_stock`, `notes`, `created_at`

### 10. `carts` & 11. `cart_items`
Persistent shopping cart session persistence.
- `carts`: `id`, `user_id`, `session_id`, `created_at`, `updated_at`
- `cart_items`: `id`, `cart_id`, `product_id`, `quantity`, `unit_price`, `created_at`

### 12. `wishlists` & 13. `wishlist_items`
User item bookmarking.
- `wishlists`: `id`, `user_id`, `created_at`
- `wishlist_items`: `id`, `wishlist_id`, `product_id`, `created_at`

### 14. `orders` & 15. `order_items`
Universal multi-model order fulfillment.
- `orders`: `id`, `order_number`, `user_id`, `business_id`, `order_type`, `total_amount`, `discount_amount`, `shipping_fee`, `tax_amount`, `final_amount`, `coupon_code`, `shipping_address`, `order_status`, `payment_status`, `tracking_number`, `created_at`
- `order_items`: `id`, `order_id`, `product_id`, `seller_id`, `product_title`, `unit_price`, `quantity`, `subtotal`, `status`

### 16. `payments` & 17. `transactions`
Simulated financial transaction records.
- `payments`: `id`, `order_id`, `user_id`, `payment_method`, `transaction_reference`, `amount`, `payment_status`, `gateway_response`, `is_demo`
- `transactions`: `id`, `payment_id`, `user_id`, `type`, `amount`, `reference_code`, `description`, `status`

### 18. `coupons`
Promotional discount rules engine.
- `id`, `code`, `description`, `discount_type`, `discount_value`, `min_spend`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `expiry_date`, `status`

### 19. `reviews`
Verified buyer product and service ratings.
- `id`, `product_id`, `service_id`, `user_id`, `rating`, `review_title`, `review_text`, `review_type`, `status`

### 20. `notifications`
Internal event alerts.
- `id`, `user_id`, `title`, `message`, `link`, `type`, `is_read`, `created_at`

### 21. `business_quotes` & 22. `quote_items`
B2B Request for Quotation (RFQ) negotiation pipeline.
- `business_quotes`: `id`, `quote_number`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `target_price`, `message`, `seller_quote_price`, `seller_notes`, `validity_date`, `status`
- `quote_items`: `id`, `quote_id`, `product_id`, `requested_qty`, `offered_unit_price`, `line_total`

### 23. `services`, 24. `service_requests` & 25. `proposals`
C2B Freelance professional marketplace.
- `services`: `id`, `freelancer_id`, `category_id`, `title`, `slug`, `description`, `skills_required`, `basic_price`, `standard_price`, `premium_price`, `delivery_days`, `portfolio_url`, `status`
- `service_requests`: `id`, `request_number`, `client_id`, `freelancer_id`, `service_id`, `title`, `requirements`, `budget`, `deadline`, `selected_tier`, `status`
- `proposals`: `id`, `service_request_id`, `freelancer_id`, `cover_letter`, `proposed_amount`, `delivery_days`, `status`

### 26. `conversations` & 27. `messages`
In-app buyer-seller chat system.
- `conversations`: `id`, `sender_id`, `receiver_id`, `subject`, `related_type`, `related_id`
- `messages`: `id`, `conversation_id`, `sender_id`, `message_text`, `is_read`, `created_at`

### 28. `reports`, 29. `returns` & 30. `activity_logs`
Moderation, refund workflow, and cybersecurity audit logs.
- `reports`: `id`, `reporter_id`, `reported_type`, `reported_id`, `reason`, `details`, `status`
- `returns`: `id`, `order_id`, `order_item_id`, `user_id`, `reason`, `return_status`, `refund_amount`
- `activity_logs`: `id`, `user_id`, `action`, `entity_type`, `entity_id`, `ip_address`, `user_agent`, `details`, `created_at`
