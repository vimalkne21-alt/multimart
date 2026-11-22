-- =====================================================================
-- MULTIMART: ALL-IN-ONE MULTI-MODEL E-COMMERCE MARKETPLACE
-- Academic Major Project Database Schema & Demo Seed Data
-- Database: multimart_db
-- Compatible with MySQL 5.7+, MySQL 8.0+, MariaDB, and phpMyAdmin
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `multimart_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `multimart_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. ROLES TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `display_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`) VALUES
(1, 'admin', 'Administrator', 'Platform administrator with total oversight, management and approval permissions'),
(2, 'business_seller', 'Business Seller', 'Corporate vendor offering B2B wholesale and B2C retail merchandise'),
(3, 'individual_seller', 'Individual Seller', 'Peer-to-peer consumer selling used or secondary market items (C2C)'),
(4, 'customer', 'Customer', 'General consumer purchasing retail goods and requesting services'),
(5, 'freelancer', 'Freelancer / Service Provider', 'Professional individual offering skills and services to businesses (C2B)');

-- ---------------------------------------------------------------------
-- 2. USERS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT UNSIGNED NOT NULL DEFAULT 4,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(25) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
  `status` ENUM('active', 'pending', 'suspended') DEFAULT 'active',
  `email_verified` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo accounts (passwords: Admin@123, Seller@123, User@123, Customer@123, Freelance@123)
-- Standard bcrypt hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi represents 'password'
INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `password_hash`, `phone`, `avatar`, `status`, `email_verified`) VALUES
(1, 1, 'MultiMart SuperAdmin', 'admin@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 43210', 'assets/images/admin-avatar.png', 'active', 1),
(2, 2, 'Apex Industrial Technologies Ltd', 'apex.supplies@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 11111', 'assets/images/vendor-apex.png', 'active', 1),
(3, 3, 'Johnathan Miller (Individual)', 'john.doe@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 22222', 'assets/images/user-john.png', 'active', 1),
(4, 4, 'Priya Sharma (Shopper)', 'priya.sharma@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 33333', 'assets/images/user-priya.png', 'active', 1),
(5, 5, 'Rahul Verma (Data & AI Specialist)', 'rahul.tech@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 44444', 'assets/images/freelancer-rahul.png', 'active', 1);

-- ---------------------------------------------------------------------
-- 3. USER ADDRESSES TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `address_type` ENUM('home', 'office', 'warehouse', 'business') DEFAULT 'home',
  `recipient_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `street_address` VARCHAR(255) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `postal_code` VARCHAR(20) NOT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `is_default` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_addr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_addresses` (`id`, `user_id`, `address_type`, `recipient_name`, `phone`, `street_address`, `city`, `state`, `postal_code`, `country`, `is_default`) VALUES
(1, 4, 'home', 'Priya Sharma', '+91 98765 33333', 'Flat 402, Sunshine Heights, Outer Ring Road', 'Bengaluru', 'Karnataka', '560103', 'India', 1),
(2, 2, 'business', 'Apex Industrial Tech Supply Depot', '+91 98765 11111', 'Plot 88, Electronic City Phase II', 'Bengaluru', 'Karnataka', '560100', 'India', 1),
(3, 3, 'home', 'John Miller', '+91 98765 22222', '12B Green Valley Apartments, Bandra West', 'Mumbai', 'Maharashtra', '400050', 'India', 1);

-- ---------------------------------------------------------------------
-- 4. BUSINESSES TABLE (B2B Sellers & Corporate Buyers)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `businesses`;
CREATE TABLE `businesses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `company_name` VARCHAR(200) NOT NULL,
  `business_type` ENUM('Manufacturer', 'Wholesaler', 'Distributor', 'Retailer', 'Service Enterprise') DEFAULT 'Wholesaler',
  `registration_number` VARCHAR(100) NOT NULL,
  `tax_id_gst` VARCHAR(100) NOT NULL,
  `official_email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `address` TEXT NOT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `verified_status` ENUM('verified', 'pending', 'rejected') DEFAULT 'verified',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_biz_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `businesses` (`id`, `user_id`, `company_name`, `business_type`, `registration_number`, `tax_id_gst`, `official_email`, `phone`, `address`, `website`, `verified_status`) VALUES
(1, 2, 'Apex Industrial Technologies Pvt Ltd', 'Manufacturer', 'CIN-U72200KA2020PTC139882', '29AABCU9603R1ZM', 'procurement@apexindustrial.com', '+91 98765 11111', 'Plot 88, Electronic City Phase II, Bengaluru, KA 560100', 'https://apexindustrial.example.com', 'verified');

-- ---------------------------------------------------------------------
-- 5. BUSINESS VERIFICATIONS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `business_verifications`;
CREATE TABLE `business_verifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `business_id` INT UNSIGNED NOT NULL,
  `document_type` VARCHAR(100) NOT NULL,
  `document_file` VARCHAR(255) NOT NULL,
  `verification_status` ENUM('approved', 'pending', 'rejected') DEFAULT 'approved',
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `review_notes` TEXT,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `fk_biz_verif` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `business_verifications` (`id`, `business_id`, `document_type`, `document_file`, `verification_status`, `reviewed_by`, `review_notes`, `reviewed_at`) VALUES
(1, 1, 'Certificate of Incorporation & GST Certificate', 'uploads/documents/apex_cin_gst.pdf', 'approved', 1, 'Verified against official corporate registry portal.', NOW());

-- ---------------------------------------------------------------------
-- 6. CATEGORIES TABLE (Multi-Model Hierarchy)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` TEXT,
  `icon` VARCHAR(60) DEFAULT 'bi-tag',
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `module_type` ENUM('ALL', 'B2B', 'B2C', 'C2C', 'C2B') DEFAULT 'ALL',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `parent_id`, `module_type`) VALUES
-- B2C Retail Categories
(1, 'Electronics & Gadgets', 'electronics-gadgets', 'Smartphones, laptops, audio, smart home devices', 'bi-laptop', NULL, 'B2C'),
(2, 'Fashion & Apparel', 'fashion-apparel', 'Men and women trendy clothing and footwear', 'bi-bag', NULL, 'B2C'),
(3, 'Home & Living', 'home-living', 'Furniture, kitchen appliances, home decor', 'bi-house-door', NULL, 'B2C'),
(4, 'Beauty & Wellness', 'beauty-wellness', 'Personal skincare, haircare and health items', 'bi-heart-pulse', NULL, 'B2C'),
-- B2B Wholesale Categories
(5, 'Industrial Machinery & Automation', 'industrial-machinery', 'Heavy equipment, pneumatic components, micro-controllers', 'bi-gear-wide-connected', NULL, 'B2B'),
(6, 'Bulk Textiles & Raw Garments', 'bulk-textiles', 'Organic cotton yarn, polyester fabrics, wholesale apparel', 'bi-palette', NULL, 'B2B'),
(7, 'Commercial Packaging & Containers', 'commercial-packaging', 'Corrugated mailers, eco-pouches, shipping crates', 'bi-box-seam', NULL, 'B2B'),
(8, 'Chemicals & Raw Materials', 'chemicals-raw-materials', 'Polymers, specialty resins, industrial solvents', 'bi-eyedropper', NULL, 'B2B'),
-- C2C Peer-to-Peer Categories
(9, 'Pre-Owned Electronics', 'pre-owned-electronics', 'Used phones, gaming consoles, vintage cameras', 'bi-camera', NULL, 'C2C'),
(10, 'Used Books & Textbooks', 'used-books', 'College syllabus books, academic reference, fiction', 'bi-book', NULL, 'C2C'),
(11, 'Second-Hand Furniture & Living', 'second-hand-furniture', 'Study tables, ergonomic chairs, bed frames', 'bi-lamp', NULL, 'C2C'),
(12, 'Vehicles & Bicycles', 'vehicles-bicycles', 'Commuter bicycles, road bikes, used auto parts', 'bi-bicycle', NULL, 'C2C'),
-- C2B Freelancing & Professional Services
(13, 'Web & Mobile App Development', 'web-mobile-development', 'Full-stack web, PHP, React, Node.js, Flutter', 'bi-code-slash', NULL, 'C2B'),
(14, 'Data Science & Machine Learning', 'data-science-ml', 'Predictive modeling, NLP, data visualization, Python', 'bi-graph-up-arrow', NULL, 'C2B'),
(15, 'Graphic Design & Creative Branding', 'graphic-design-branding', 'Logos, UI/UX mockups, brand identity, marketing collaterals', 'bi-brush', NULL, 'C2B'),
(16, 'Digital Marketing & Content Strategy', 'digital-marketing-seo', 'SEO audits, technical blogging, PPC performance ads', 'bi-megaphone', NULL, 'C2B');

-- ---------------------------------------------------------------------
-- 7. PRODUCTS TABLE (B2C, B2B Wholesale, and C2C Listings)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `seller_id` INT UNSIGNED NOT NULL,
  `business_id` INT UNSIGNED DEFAULT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(280) NOT NULL UNIQUE,
  `sku` VARCHAR(100) DEFAULT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `description` LONGTEXT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `discount_price` DECIMAL(12,2) DEFAULT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 1,
  `min_order_qty` INT NOT NULL DEFAULT 1,
  `bulk_price` DECIMAL(12,2) DEFAULT NULL,
  `is_b2b` TINYINT(1) DEFAULT 0,
  `is_b2c` TINYINT(1) DEFAULT 1,
  `is_c2c` TINYINT(1) DEFAULT 0,
  `condition_type` ENUM('brand_new', 'like_new', 'good', 'fair') DEFAULT 'brand_new',
  `location` VARCHAR(150) DEFAULT 'Bengaluru, India',
  `status` ENUM('active', 'pending', 'inactive', 'sold_out') DEFAULT 'active',
  `views_count` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_prod_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `seller_id`, `business_id`, `category_id`, `title`, `slug`, `sku`, `short_description`, `description`, `price`, `discount_price`, `stock_quantity`, `min_order_qty`, `bulk_price`, `is_b2b`, `is_b2c`, `is_c2c`, `condition_type`, `location`, `status`) VALUES
-- B2C Retail Products (Authentic Commercial Consumer Hardware)
(1, 2, 1, 1, 'Apple MacBook Pro 14" M3 Pro (18GB Unified Memory, 512GB SSD, Space Black)', 'apple-macbook-pro-14-m3-pro-space-black', 'APL-MBP14-M3P-BLK', '14.2-inch Liquid Retina XDR display, Apple M3 Pro 11-core CPU, 14-core GPU.', 'Engineered for pro workflows, machine learning models, and heavy 4K video rendering. Liquid Retina XDR display with 1600 nits peak HDR brightness and ProMotion 120Hz.', 199900.00, 189900.00, 24, 1, NULL, 0, 1, 0, 'brand_new', 'Bengaluru, KA', 'active'),
(2, 2, 1, 1, 'Sony WH-1000XM5 Wireless Industry-Leading Noise Cancelling Headphones', 'sony-wh1000xm5-noise-cancelling-silver', 'SNY-WH1000XM5-SLV', 'Auto NC Optimizer, 8 microphones, 30-hour battery life with quick charge.', 'Two processors and eight microphones deliver magnanimous active noise cancellation. Crystal-clear hands-free calling with 4 beamforming microphones.', 29990.00, 26990.00, 48, 1, NULL, 0, 1, 0, 'brand_new', 'Mumbai, MH', 'active'),
(3, 2, 1, 1, 'Samsung Galaxy S24 Ultra 5G (Titanium Gray, 12GB RAM, 256GB Storage)', 'samsung-galaxy-s24-ultra-5g-titanium-gray', 'SAM-S24U-256-GRY', 'Snapdragon 8 Gen 3 for Galaxy, 200MP Quad Telephoto Camera, Built-in S-Pen.', '6.8" Dynamic AMOLED 2X display with Corning Gorilla Armor. Titanium frame exterior with IP68 water resistance and integrated S-Pen stylus.', 129999.00, 121999.00, 30, 1, NULL, 0, 1, 0, 'brand_new', 'Delhi NCR', 'active'),
(4, 2, 1, 3, 'Dyson V12 Detect Slim Total Clean Cordless Vacuum Cleaner', 'dyson-v12-detect-slim-cordless-vacuum', 'DYS-V12-SLIM-VCM', 'Laser reveals microscopic dust, Piezo sensor counts particles, 150 AW suction.', 'Dyson lightest intelligent cordless vacuum with laser illumination. Scientifically proves a deep clean with a real-time LCD screen displaying particle counts.', 55900.00, 49900.00, 18, 1, NULL, 0, 1, 0, 'brand_new', 'Bengaluru, KA', 'active'),

-- B2B Wholesale Products (Bulk, MOQ, Tiered Pricing)
(5, 2, 1, 5, 'Adani Solar 550W Mono PERC Bifacial Photovoltaic Panels (Pallet of 31 Units)', 'adani-solar-550w-mono-perc-bifacial-pallet', 'B2B-SLR-550W-PL31', 'Grade A+ Tier 1 144-cell dual glass bifacial solar modules with 25-year performance warranty.', 'Industrial grade high-efficiency solar modules designed for commercial rooftop and ground-mount PV plants. Up to 21.3% module efficiency with GST tax credit invoice.', 341000.00, 341000.00, 310, 31, 11000.00, 1, 0, 0, 'brand_new', 'Mundra SEZ / Ahmedabad, GJ', 'active'),
(6, 2, 1, 5, 'SS-304 Grade Hex Bolts & Nuts Industrial Fastener Assortment (Carton of 500 pcs)', 'ss304-hex-bolts-nuts-carton-500', 'B2B-FST-SS304-CT500', 'Corrosion resistant Austenitic Stainless Steel M8/M10/M12 fasteners with mill test certificates.', 'Standard DIN 933 / ISO 4017 fully threaded precision bolts with matching nylon lock nuts. Designed for structural engineering and automotive manufacturing.', 14500.00, 14500.00, 120, 5, 2900.00, 1, 0, 0, 'brand_new', 'Chennai Industrial Estate, TN', 'active'),
(7, 2, 1, 7, '3-Ply Heavy-Duty Corrugated Shipping Boxes (Bundle of 100 pcs, 14x10x8")', '3-ply-corrugated-shipping-boxes-bundle-100', 'B2B-PKG-BOX-BD100', '180 GSM Kraft paper, bursting strength 16 kg/sq.cm, precision die-cut for e-commerce dispatch.', 'Tested to edge-crush standard ECT 32. 100% recyclable, flat-packed for compact warehouse storage. Dimensions 14x10x8 inches.', 4800.00, 4500.00, 400, 10, 450.00, 1, 0, 0, 'brand_new', 'Faridabad, HR', 'active'),

-- C2C Peer-to-Peer Pre-Owned Listings
(8, 3, NULL, 9, 'Apple iPad Air 5th Gen (M1 Chip, 64GB, Wi-Fi, Space Grey) + Apple Pencil 2', 'apple-ipad-air-m1-64gb-pencil2-used', 'C2C-IPD-AIR5-M1', 'Purchased Nov 2023, 98% battery health, Spigen tempered glass applied from day one.', 'Personal iPad Air M1 used strictly for college notes. Includes original box, Apple 20W USB-C charger, and Apple Pencil 2nd Generation with magnetic charging.', 42000.00, 42000.00, 1, 1, NULL, 0, 0, 1, 'like_new', 'Bengaluru, KA', 'active'),
(9, 3, NULL, 9, 'Sony PlayStation 5 Disc Edition + 2 DualSense Controllers + God of War Ragnarok', 'sony-ps5-disc-edition-2-controllers-used', 'C2C-SNY-PS5-DISC', 'Indian retail unit (CFI-1208A), runs whisper quiet, includes HDMI 2.1 cable and retail box.', 'Mint condition PS5 disc console. Comes with two original DualSense wireless controllers (Midnight Black and Cosmic Red) and physical disc copy of God of War Ragnarok.', 41500.00, 41500.00, 1, 1, NULL, 0, 0, 1, 'like_new', 'Mumbai, MH', 'active'),
(10, 3, NULL, 11, 'Herman Miller Sayl Ergonomic Office Chair (Fog White Frame & Slate Mesh)', 'herman-miller-sayl-ergonomic-chair-used', 'C2C-HML-SAYL-WHT', 'Original Herman Miller 3D Intelligent suspension back, fully adjustable arms and seat depth.', 'Purchased for home office during remote work. Unmatched lumbar support and spine alignment. All hydraulic pistons, tilt tensioners, and armrests function like new.', 36000.00, 36000.00, 1, 1, NULL, 0, 0, 1, 'good', 'Bengaluru, KA', 'active');

-- ---------------------------------------------------------------------
-- 8. PRODUCT IMAGES TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `display_order` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_prod_img` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_primary`, `display_order`) VALUES
(1, 1, 'assets/images/products/headphones-1.jpg', 1, 1),
(2, 2, 'assets/images/products/laptop-1.jpg', 1, 1),
(3, 3, 'assets/images/products/backpack-1.jpg', 1, 1),
(4, 4, 'assets/images/products/diffuser-1.jpg', 1, 1),
(5, 5, 'assets/images/products/relay-module-1.jpg', 1, 1),
(6, 6, 'assets/images/products/tshirt-blanks-1.jpg', 1, 1),
(7, 7, 'assets/images/products/corrugated-boxes-1.jpg', 1, 1),
(8, 8, 'assets/images/products/cs-textbooks-1.jpg', 1, 1),
(9, 9, 'assets/images/products/sony-camera-1.jpg', 1, 1),
(10, 10, 'assets/images/products/study-desk-1.jpg', 1, 1);

-- ---------------------------------------------------------------------
-- 9. PRODUCT INVENTORY TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `product_inventory`;
CREATE TABLE `product_inventory` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `change_type` ENUM('initial', 'purchase', 'sale', 'adjustment', 'return') DEFAULT 'initial',
  `quantity` INT NOT NULL,
  `remaining_stock` INT NOT NULL,
  `notes` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_inv_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_inventory` (`id`, `product_id`, `change_type`, `quantity`, `remaining_stock`, `notes`) VALUES
(1, 1, 'initial', 100, 100, 'Opening inventory batch'),
(2, 1, 'sale', -15, 85, 'Initial retail sales dispatch'),
(3, 2, 'initial', 25, 25, 'Manufacturer allocation'),
(4, 5, 'initial', 500, 500, 'Warehouse warehouse staging for industrial clients');

-- ---------------------------------------------------------------------
-- 10. CARTS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(191) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `carts` (`id`, `user_id`, `session_id`) VALUES
(1, 4, 'sess_demo_priya_sharma_cart');

-- ---------------------------------------------------------------------
-- 11. CART ITEMS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ci_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ci_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 1, 3999.00),
(2, 1, 4, 1, 1299.00);

-- ---------------------------------------------------------------------
-- 12. WISHLISTS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_wl_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `wishlists` (`id`, `user_id`) VALUES
(1, 4);

-- ---------------------------------------------------------------------
-- 13. WISHLIST ITEMS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `wishlist_items`;
CREATE TABLE `wishlist_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `wishlist_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_wli_wl` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wli_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `wishlist_items` (`id`, `wishlist_id`, `product_id`) VALUES
(1, 1, 2),
(2, 1, 3);

-- ---------------------------------------------------------------------
-- 14. ORDERS TABLE (Unified Multi-Model Order Management)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `business_id` INT UNSIGNED DEFAULT NULL,
  `order_type` ENUM('B2C', 'B2B', 'C2C') DEFAULT 'B2C',
  `total_amount` DECIMAL(12,2) NOT NULL,
  `discount_amount` DECIMAL(12,2) DEFAULT 0.00,
  `shipping_fee` DECIMAL(12,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
  `final_amount` DECIMAL(12,2) NOT NULL,
  `coupon_code` VARCHAR(50) DEFAULT NULL,
  `shipping_address` TEXT NOT NULL,
  `billing_address` TEXT,
  `order_status` ENUM('Pending', 'Confirmed', 'Processing', 'Shipped', 'Out for Delivery', 'Delivered', 'Cancelled', 'Refunded') DEFAULT 'Confirmed',
  `payment_status` ENUM('Unpaid', 'Paid', 'Pending', 'Refunded') DEFAULT 'Paid',
  `tracking_number` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ord_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `business_id`, `order_type`, `total_amount`, `discount_amount`, `shipping_fee`, `tax_amount`, `final_amount`, `coupon_code`, `shipping_address`, `order_status`, `payment_status`, `tracking_number`) VALUES
(1, 'MM-ORD-2026-88101', 4, 1, 'B2C', 3999.00, 399.90, 0.00, 180.00, 3779.10, 'WELCOME10', 'Priya Sharma, Flat 402, Sunshine Heights, Outer Ring Road, Bengaluru - 560103, Phone: +91 98765 33333', 'Shipped', 'Paid', 'DELHIVERY-BLR-984421'),
(2, 'MM-ORD-2026-88102', 4, 1, 'B2C', 1899.00, 0.00, 99.00, 95.00, 2093.00, NULL, 'Priya Sharma, Flat 402, Sunshine Heights, Outer Ring Road, Bengaluru - 560103, Phone: +91 98765 33333', 'Delivered', 'Paid', 'BLUEDART-EXP-110294');

-- ---------------------------------------------------------------------
-- 15. ORDER ITEMS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `seller_id` INT UNSIGNED NOT NULL,
  `product_title` VARCHAR(255) NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `status` ENUM('confirmed', 'dispatched', 'delivered', 'returned', 'cancelled') DEFAULT 'confirmed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_oi_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `seller_id`, `product_title`, `unit_price`, `quantity`, `subtotal`, `status`) VALUES
(1, 1, 1, 2, 'Quantum Pro Wireless ANC Headphones with Spatial Audio', 3999.00, 1, 3999.00, 'dispatched'),
(2, 2, 3, 2, 'Nordic All-Weather Waterproof Commuter Backpack (30L)', 1899.00, 1, 1899.00, 'delivered');

-- ---------------------------------------------------------------------
-- 16. PAYMENTS TABLE (Simulated Safe Demo Gateway)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `payment_method` ENUM('UPI', 'Debit Card', 'Credit Card', 'Net Banking', 'Digital Wallet', 'Cash on Delivery') NOT NULL,
  `transaction_reference` VARCHAR(100) NOT NULL UNIQUE,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_status` ENUM('Success', 'Pending', 'Failed', 'Refunded') DEFAULT 'Success',
  `gateway_response` TEXT,
  `is_demo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pay_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payments` (`id`, `order_id`, `user_id`, `payment_method`, `transaction_reference`, `amount`, `payment_status`, `gateway_response`, `is_demo`) VALUES
(1, 1, 4, 'UPI', 'TXN_SIM_UPI_9831411200', 3779.10, 'Success', '{"status":"SUCCESS","auth_code":"SIM-AUTH-2026","provider":"BHIM-UPI","simulated":true}', 1),
(2, 2, 4, 'Credit Card', 'TXN_SIM_CC_7749102231', 2093.00, 'Success', '{"status":"SUCCESS","auth_code":"SIM-AUTH-9912","last4":"4242","simulated":true}', 1);

-- ---------------------------------------------------------------------
-- 17. TRANSACTIONS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `payment_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('debit', 'credit', 'refund') DEFAULT 'debit',
  `amount` DECIMAL(12,2) NOT NULL,
  `reference_code` VARCHAR(100) NOT NULL UNIQUE,
  `description` VARCHAR(255) NOT NULL,
  `status` ENUM('completed', 'pending', 'reversed') DEFAULT 'completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_txn_pay` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `transactions` (`id`, `payment_id`, `user_id`, `type`, `amount`, `reference_code`, `description`, `status`) VALUES
(1, 1, 4, 'debit', 3779.10, 'LEDGER-REF-001', 'Settlement for Order #MM-ORD-2026-88101 via Simulated UPI', 'completed'),
(2, 2, 4, 'debit', 2093.00, 'LEDGER-REF-002', 'Settlement for Order #MM-ORD-2026-88102 via Simulated Card', 'completed');

-- ---------------------------------------------------------------------
-- 18. COUPONS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `description` VARCHAR(255) NOT NULL,
  `discount_type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_spend` DECIMAL(10,2) DEFAULT 500.00,
  `max_discount` DECIMAL(10,2) DEFAULT 1000.00,
  `usage_limit` INT DEFAULT 500,
  `used_count` INT DEFAULT 1,
  `start_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `coupons` (`id`, `code`, `description`, `discount_type`, `discount_value`, `min_spend`, `max_discount`, `start_date`, `expiry_date`, `status`) VALUES
(1, 'WELCOME10', 'Welcome bonus: Flat 10% discount on first retail order', 'percentage', 10.00, 500.00, 500.00, '2026-01-01', '2027-12-31', 'active'),
(2, 'MULTISAVE20', 'Festival Mega Deal: 20% off on orders above Rs. 2,000', 'percentage', 20.00, 2000.00, 1000.00, '2026-01-01', '2027-12-31', 'active'),
(3, 'FLAT500', 'Corporate bulk discount: Flat Rs. 500 off on B2B purchases', 'fixed', 500.00, 5000.00, 500.00, '2026-01-01', '2027-12-31', 'active');

-- ---------------------------------------------------------------------
-- 19. REVIEWS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `service_id` INT UNSIGNED DEFAULT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `review_title` VARCHAR(150) NOT NULL,
  `review_text` TEXT NOT NULL,
  `review_type` ENUM('product', 'service', 'seller') DEFAULT 'product',
  `status` ENUM('approved', 'pending', 'rejected') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reviews` (`id`, `product_id`, `service_id`, `user_id`, `rating`, `review_title`, `review_text`, `review_type`, `status`) VALUES
(1, 1, NULL, 4, 5, 'Superb sound quality & comfy ear-cups!', 'Tested this headset for online classes and music mixing. The active noise cancellation cuts out ceiling fan buzz effortlessly. Battery life easily lasts four days on medium volume.', 'product', 'approved'),
(2, 3, NULL, 4, 4, 'Very durable backpack for daily commute', 'Plenty of compartments, safe padded laptop section and genuine waterproof fabric.', 'product', 'approved');

-- ---------------------------------------------------------------------
-- 20. NOTIFICATIONS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `type` ENUM('order', 'payment', 'quote', 'service', 'message', 'system') DEFAULT 'system',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `type`, `is_read`) VALUES
(1, 4, 'Order Dispatched!', 'Your order #MM-ORD-2026-88101 has been dispatched with tracking #DELHIVERY-BLR-984421.', 'orders/track.php?order=MM-ORD-2026-88101', 'order', 0),
(2, 2, 'New B2B Quotation Request Received', 'A wholesale inquiry was initiated for CNC Micro-Controller Relay Modules.', 'seller/rfqs.php', 'quote', 0),
(3, 5, 'New Project Proposal Invitation', 'Apex Industrial Technologies viewed your Data Science profile and sent a requirement inquiry.', 'services/requests.php', 'service', 0);

-- ---------------------------------------------------------------------
-- 21. BUSINESS QUOTES TABLE (B2B RFQ - Request for Quotations)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `business_quotes`;
CREATE TABLE `business_quotes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `quote_number` VARCHAR(50) NOT NULL UNIQUE,
  `buyer_id` INT UNSIGNED NOT NULL,
  `seller_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL,
  `target_price` DECIMAL(12,2) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `seller_quote_price` DECIMAL(12,2) DEFAULT NULL,
  `seller_notes` TEXT,
  `validity_date` DATE DEFAULT NULL,
  `status` ENUM('requested', 'responded', 'accepted', 'rejected', 'expired') DEFAULT 'requested',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_bq_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bq_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bq_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `business_quotes` (`id`, `quote_number`, `buyer_id`, `seller_id`, `product_id`, `quantity`, `target_price`, `message`, `seller_quote_price`, `seller_notes`, `validity_date`, `status`) VALUES
(1, 'RFQ-2026-0091', 4, 2, 5, 100, 18000.00, 'We require 100 sets of CNC Relay modules for our institutional IoT hardware robotics lab. Looking for institutional tax invoice and 1 year replacement warranty.', 19500.00, 'We can provide institutional pricing of Rs. 195/unit including GST and shipping with 1 year depot warranty.', '2026-12-31', 'responded');

-- ---------------------------------------------------------------------
-- 22. QUOTE ITEMS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `quote_items`;
CREATE TABLE `quote_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `quote_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `requested_qty` INT NOT NULL,
  `offered_unit_price` DECIMAL(12,2) NOT NULL,
  `line_total` DECIMAL(12,2) NOT NULL,
  `notes` VARCHAR(255) DEFAULT NULL,
  CONSTRAINT `fk_qi_quote` FOREIGN KEY (`quote_id`) REFERENCES `business_quotes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quote_items` (`id`, `quote_id`, `product_id`, `requested_qty`, `offered_unit_price`, `line_total`, `notes`) VALUES
(1, 1, 5, 100, 195.00, 19500.00, 'Pre-tested industrial standard grade modules');

-- ---------------------------------------------------------------------
-- 23. SERVICES TABLE (C2B Freelancer Service Offerings)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `freelancer_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(280) NOT NULL UNIQUE,
  `description` LONGTEXT NOT NULL,
  `skills_required` VARCHAR(255) NOT NULL,
  `basic_price` DECIMAL(10,2) NOT NULL,
  `standard_price` DECIMAL(10,2) NOT NULL,
  `premium_price` DECIMAL(10,2) NOT NULL,
  `delivery_days` INT NOT NULL DEFAULT 5,
  `portfolio_url` VARCHAR(255) DEFAULT 'https://github.com/freelancer-portfolio',
  `status` ENUM('active', 'paused') DEFAULT 'active',
  `views_count` INT UNSIGNED DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_srv_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_srv_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`id`, `freelancer_id`, `category_id`, `title`, `slug`, `description`, `skills_required`, `basic_price`, `standard_price`, `premium_price`, `delivery_days`, `portfolio_url`, `status`) VALUES
(1, 5, 14, 'End-to-End Enterprise Data Science Pipeline & Interactive ML Dashboard', 'enterprise-data-science-pipeline-ml-dashboard', 'Will build robust ETL data pipelines, statistical model training (Scikit-Learn/PyTorch), and interactive analytical dashboards with Streamlit or Plotly for enterprise business intelligence.', 'Python, Pandas, Scikit-Learn, Streamlit, SQL, Data Visualization', 4999.00, 12999.00, 24999.00, 7, 'https://github.com/rahulverma-ai', 'active'),
(2, 5, 13, 'Full-Stack Custom PHP & React E-Commerce Microservices Development', 'fullstack-php-react-ecommerce-development', 'Professional development of secure, scalable web applications with PHP 8, RESTful APIs, MySQL database design, role-based dashboards and payment gateway simulation.', 'PHP 8, MySQL, Bootstrap 5, JavaScript, REST API, Git', 3499.00, 8999.00, 18999.00, 5, 'https://github.com/rahulverma-dev', 'active');

-- ---------------------------------------------------------------------
-- 24. SERVICE REQUESTS TABLE (C2B Hiring & Project Contracts)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE `service_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `request_number` VARCHAR(50) NOT NULL UNIQUE,
  `client_id` INT UNSIGNED NOT NULL,
  `freelancer_id` INT UNSIGNED NOT NULL,
  `service_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `requirements` TEXT NOT NULL,
  `budget` DECIMAL(12,2) NOT NULL,
  `deadline` DATE NOT NULL,
  `selected_tier` ENUM('basic', 'standard', 'premium', 'custom') DEFAULT 'standard',
  `status` ENUM('requested', 'proposal_sent', 'hired', 'in_progress', 'review', 'completed', 'cancelled') DEFAULT 'in_progress',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sr_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sr_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sr_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `service_requests` (`id`, `request_number`, `client_id`, `freelancer_id`, `service_id`, `title`, `requirements`, `budget`, `deadline`, `selected_tier`, `status`) VALUES
(1, 'REQ-2026-SRV-1001', 2, 5, 1, 'Predictive Inventory Restock Forecasting Model for Apex Supplies', 'We need a machine learning forecasting script that analyzes historical purchase logs and predicts demand spikes 30 days in advance.', 12999.00, '2026-12-15', 'standard', 'in_progress');

-- ---------------------------------------------------------------------
-- 25. PROPOSALS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `proposals`;
CREATE TABLE `proposals` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `service_request_id` INT UNSIGNED NOT NULL,
  `freelancer_id` INT UNSIGNED NOT NULL,
  `cover_letter` TEXT NOT NULL,
  `proposed_amount` DECIMAL(12,2) NOT NULL,
  `delivery_days` INT NOT NULL,
  `status` ENUM('submitted', 'accepted', 'rejected') DEFAULT 'accepted',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_prop_sr` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prop_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `proposals` (`id`, `service_request_id`, `freelancer_id`, `cover_letter`, `proposed_amount`, `delivery_days`, `status`) VALUES
(1, 1, 5, 'I have built time-series forecasting models (ARIMA & XGBoost) for supply chain analytics. I will deliver clean modular Python code with automated data ingestion and interactive HTML forecasting charts.', 12999.00, 7, 'accepted');

-- ---------------------------------------------------------------------
-- 26. CONVERSATIONS TABLE (Internal Messaging System)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `conversations`;
CREATE TABLE `conversations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT UNSIGNED NOT NULL,
  `receiver_id` INT UNSIGNED NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `related_type` ENUM('product', 'quote', 'service', 'general') DEFAULT 'general',
  `related_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_conv_snd` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conv_rcv` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `conversations` (`id`, `sender_id`, `receiver_id`, `subject`, `related_type`, `related_id`) VALUES
(1, 4, 3, 'Inquiry regarding Sony A6000 Camera condition (C2C Listing)', 'product', 9),
(2, 2, 5, 'Milestone review for Inventory Restock ML Project', 'service', 1);

-- ---------------------------------------------------------------------
-- 27. MESSAGES TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT UNSIGNED NOT NULL,
  `sender_id` INT UNSIGNED NOT NULL,
  `message_text` TEXT NOT NULL,
  `attachment` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `message_text`, `is_read`) VALUES
(1, 1, 4, 'Hello John! Is the Sony A6000 camera still available? Can we do local pickup near Bandra?', 1),
(2, 1, 3, 'Hi Priya! Yes, it is fully available. Local pickup around Bandra station works great for me this weekend.', 1),
(3, 2, 2, 'Hi Rahul, we reviewed your initial dataset analysis notebook. The preliminary accuracy looks promising!', 0);

-- ---------------------------------------------------------------------
-- 28. REPORTS TABLE (C2C Moderation & Safety)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reporter_id` INT UNSIGNED NOT NULL,
  `reported_type` ENUM('product', 'service', 'user') DEFAULT 'product',
  `reported_id` INT UNSIGNED NOT NULL,
  `reason` VARCHAR(150) NOT NULL,
  `details` TEXT,
  `status` ENUM('pending', 'reviewed', 'dismissed', 'action_taken') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_rep_user` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reports` (`id`, `reporter_id`, `reported_type`, `reported_id`, `reason`, `details`, `status`) VALUES
(1, 4, 'product', 8, 'Incorrect Category or Details Check', 'User requested quick confirmation of edition publication year.', 'reviewed');

-- ---------------------------------------------------------------------
-- 29. RETURNS TABLE
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `returns`;
CREATE TABLE `returns` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `order_item_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `return_status` ENUM('requested', 'approved', 'item_received', 'refund_issued', 'rejected') DEFAULT 'requested',
  `refund_amount` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ret_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ret_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 30. ACTIVITY LOGS TABLE (Audit Trail for Cybersecurity)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) DEFAULT NULL,
  `entity_id` INT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT '127.0.0.1',
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`) VALUES
(1, 1, 'SYSTEM_INIT', 'DATABASE', 1, 'Database schema initialized with multi-model architecture.'),
(2, 4, 'USER_LOGIN', 'AUTH', 4, 'Customer authenticated successfully via standard login form.'),
(3, 4, 'CHECKOUT_COMPLETED', 'ORDER', 1, 'Simulated payment verified for Order #MM-ORD-2026-88101.');

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- END OF MULTIMART DATABASE SCHEMA
-- =====================================================================
