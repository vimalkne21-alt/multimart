<?php
/**
 * MultiMart - Database Connection Layer (PDO)
 * Supports MySQL / MariaDB (XAMPP Default) with seamless error-handling and auto-provisioning
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    private static string $driver = 'mysql';

    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = DB_HOST;
        $port = DB_PORT;
        $dbname = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;

        // Try MySQL first (standard XAMPP / production)
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            self::$instance = new PDO($dsn, $user, $pass, $options);
            self::$driver = 'mysql';
            return self::$instance;
        } catch (PDOException $e) {
            // Check if failure is due to database not existing yet, attempt to create it
            if ($e->getCode() == 1049 || stripos($e->getMessage(), 'Unknown database') !== false) {
                try {
                    $rootDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                    $rootPdo = new PDO($rootDsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    
                    // Import SQL schema
                    $sqlFile = dirname(__DIR__) . '/database/multimart_database.sql';
                    if (file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        // Filter out CREATE DATABASE / USE to avoid collision
                        $rootPdo->exec("USE `{$dbname}`;");
                        $rootPdo->exec($sql);
                    }
                    
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                    self::$driver = 'mysql';
                    return self::$instance;
                } catch (Exception $inner) {
                    // fall through to fallback
                }
            }

            // Fallback: SQLite local file database for standalone execution / testing
            $sqlitePath = dirname(__DIR__) . '/database/multimart.sqlite';
            try {
                $needsSetup = !file_exists($sqlitePath) || filesize($sqlitePath) === 0;
                self::$instance = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                self::$driver = 'sqlite';

                if ($needsSetup) {
                    self::initializeSqliteDatabase(self::$instance);
                }

                return self::$instance;
            } catch (Exception $sqliteEx) {
                die("Critical Database Connection Failure: Could not establish MySQL or SQLite connection. Error: " . htmlspecialchars($e->getMessage()));
            }
        }
    }

    public static function getDriver(): string {
        return self::$driver;
    }

    private static function initializeSqliteDatabase(PDO $pdo): void {
        // Build SQLite compatible schema matching the 30 tables
        $schema = "
        CREATE TABLE IF NOT EXISTS roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            display_name TEXT NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO roles (id, name, display_name, description) VALUES
        (1, 'admin', 'Administrator', 'Platform administrator'),
        (2, 'business_seller', 'Business Seller', 'Corporate vendor offering B2B & B2C'),
        (3, 'individual_seller', 'Individual Seller', 'Peer-to-peer consumer selling used items'),
        (4, 'customer', 'Customer', 'General consumer and service buyer'),
        (5, 'freelancer', 'Freelancer / Service Provider', 'Professional individual offering services');

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            role_id INTEGER NOT NULL DEFAULT 4,
            full_name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            phone TEXT,
            avatar TEXT DEFAULT 'assets/images/default-avatar.png',
            status TEXT DEFAULT 'active',
            email_verified INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO users (id, role_id, full_name, email, password_hash, phone, avatar, status, email_verified) VALUES
        (1, 1, 'MultiMart SuperAdmin', 'admin@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 43210', 'assets/images/admin-avatar.png', 'active', 1),
        (2, 2, 'Apex Industrial Technologies Ltd', 'apex.supplies@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 11111', 'assets/images/vendor-apex.png', 'active', 1),
        (3, 3, 'Johnathan Miller (Individual)', 'john.doe@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 22222', 'assets/images/user-john.png', 'active', 1),
        (4, 4, 'Priya Sharma (Shopper)', 'priya.sharma@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 33333', 'assets/images/user-priya.png', 'active', 1),
        (5, 5, 'Rahul Verma (Data & AI Specialist)', 'rahul.tech@multimart.com', '$2y$10$e8460pAELt153.zXG/eGq.3wPvZf2i6FvC0b4d081lKzS2mYyO3p2', '+91 98765 44444', 'assets/images/freelancer-rahul.png', 'active', 1);

        CREATE TABLE IF NOT EXISTS user_addresses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            address_type TEXT DEFAULT 'home',
            recipient_name TEXT NOT NULL,
            phone TEXT NOT NULL,
            street_address TEXT NOT NULL,
            city TEXT NOT NULL,
            state TEXT NOT NULL,
            postal_code TEXT NOT NULL,
            country TEXT DEFAULT 'India',
            is_default INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO user_addresses (id, user_id, address_type, recipient_name, phone, street_address, city, state, postal_code, country, is_default) VALUES
        (1, 4, 'home', 'Priya Sharma', '+91 98765 33333', 'Flat 402, Sunshine Heights, Outer Ring Road', 'Bengaluru', 'Karnataka', '560103', 'India', 1),
        (2, 2, 'business', 'Apex Industrial Tech Supply Depot', '+91 98765 11111', 'Plot 88, Electronic City Phase II', 'Bengaluru', 'Karnataka', '560100', 'India', 1);

        CREATE TABLE IF NOT EXISTS businesses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            company_name TEXT NOT NULL,
            business_type TEXT DEFAULT 'Wholesaler',
            registration_number TEXT NOT NULL,
            tax_id_gst TEXT NOT NULL,
            official_email TEXT NOT NULL,
            phone TEXT NOT NULL,
            address TEXT NOT NULL,
            website TEXT,
            verified_status TEXT DEFAULT 'verified',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO businesses (id, user_id, company_name, business_type, registration_number, tax_id_gst, official_email, phone, address, website, verified_status) VALUES
        (1, 2, 'Apex Industrial Technologies Pvt Ltd', 'Manufacturer', 'CIN-U72200KA2020PTC139882', '29AABCU9603R1ZM', 'procurement@apexindustrial.com', '+91 98765 11111', 'Plot 88, Electronic City Phase II, Bengaluru, KA 560100', 'https://apexindustrial.example.com', 'verified');

        CREATE TABLE IF NOT EXISTS business_verifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            business_id INTEGER NOT NULL,
            document_type TEXT NOT NULL,
            document_file TEXT NOT NULL,
            verification_status TEXT DEFAULT 'approved',
            reviewed_by INTEGER,
            review_notes TEXT,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            reviewed_at DATETIME
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            description TEXT,
            icon TEXT DEFAULT 'bi-tag',
            parent_id INTEGER,
            module_type TEXT DEFAULT 'ALL',
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO categories (id, name, slug, description, icon, parent_id, module_type) VALUES
        (1, 'Electronics & Gadgets', 'electronics-gadgets', 'Smartphones, laptops, audio, smart home', 'bi-laptop', NULL, 'B2C'),
        (2, 'Fashion & Apparel', 'fashion-apparel', 'Men and women trendy clothing and footwear', 'bi-bag', NULL, 'B2C'),
        (3, 'Home & Living', 'home-living', 'Furniture, kitchen appliances, home decor', 'bi-house-door', NULL, 'B2C'),
        (4, 'Beauty & Wellness', 'beauty-wellness', 'Personal skincare, haircare and health items', 'bi-heart-pulse', NULL, 'B2C'),
        (5, 'Industrial Machinery & Automation', 'industrial-machinery', 'Heavy equipment, pneumatic components', 'bi-gear-wide-connected', NULL, 'B2B'),
        (6, 'Bulk Textiles & Raw Garments', 'bulk-textiles', 'Organic cotton yarn, polyester fabrics', 'bi-palette', NULL, 'B2B'),
        (7, 'Commercial Packaging & Containers', 'commercial-packaging', 'Corrugated mailers, eco-pouches', 'bi-box-seam', NULL, 'B2B'),
        (8, 'Chemicals & Raw Materials', 'chemicals-raw-materials', 'Polymers, specialty resins', 'bi-eyedropper', NULL, 'B2B'),
        (9, 'Pre-Owned Electronics', 'pre-owned-electronics', 'Used phones, gaming consoles, vintage cameras', 'bi-camera', NULL, 'C2C'),
        (10, 'Used Books & Textbooks', 'used-books', 'College syllabus books, academic reference', 'bi-book', NULL, 'C2C'),
        (11, 'Second-Hand Furniture & Living', 'second-hand-furniture', 'Study tables, ergonomic chairs', 'bi-lamp', NULL, 'C2C'),
        (12, 'Vehicles & Bicycles', 'vehicles-bicycles', 'Commuter bicycles, road bikes', 'bi-bicycle', NULL, 'C2C'),
        (13, 'Web & Mobile App Development', 'web-mobile-development', 'Full-stack web, PHP, React, Node.js', 'bi-code-slash', NULL, 'C2B'),
        (14, 'Data Science & Machine Learning', 'data-science-ml', 'Predictive modeling, NLP, data visualization', 'bi-graph-up-arrow', NULL, 'C2B'),
        (15, 'Graphic Design & Creative Branding', 'graphic-design-branding', 'Logos, UI/UX mockups, brand identity', 'bi-brush', NULL, 'C2B'),
        (16, 'Digital Marketing & Content Strategy', 'digital-marketing-seo', 'SEO audits, technical blogging', 'bi-megaphone', NULL, 'C2B');

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            seller_id INTEGER NOT NULL,
            business_id INTEGER,
            category_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            sku TEXT,
            short_description TEXT,
            description TEXT NOT NULL,
            price REAL NOT NULL,
            discount_price REAL,
            stock_quantity INTEGER DEFAULT 1,
            min_order_qty INTEGER DEFAULT 1,
            bulk_price REAL,
            is_b2b INTEGER DEFAULT 0,
            is_b2c INTEGER DEFAULT 1,
            is_c2c INTEGER DEFAULT 0,
            condition_type TEXT DEFAULT 'brand_new',
            location TEXT DEFAULT 'Bengaluru, India',
            status TEXT DEFAULT 'active',
            views_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO products (id, seller_id, business_id, category_id, title, slug, sku, short_description, description, price, discount_price, stock_quantity, min_order_qty, bulk_price, is_b2b, is_b2c, is_c2c, condition_type, location, status) VALUES
        (1, 2, 1, 1, 'Quantum Pro Wireless ANC Headphones with Spatial Audio', 'quantum-pro-wireless-anc-headphones', 'B2C-EL-001', 'Active Noise Cancelling over-ear headphones with 45-hour battery life.', 'Experience supreme auditory fidelity with the Quantum Pro with 40mm titanium drivers and 42dB noise cancellation.', 4999.00, 3999.00, 85, 1, NULL, 0, 1, 0, 'brand_new', 'Bengaluru, KA', 'active'),
        (2, 2, 1, 1, 'UltraBook Swift X 15.6\" OLED Laptop (Core i7 16GB/1TB SSD)', 'ultrabook-swift-x-oled-laptop', 'B2C-EL-002', 'Slim magnesium-alloy workstation notebook designed for engineering students.', 'Boasting a 15.6-inch 2.8K 120Hz OLED display, Core i7, 16GB RAM and 1TB SSD.', 74999.00, 68999.00, 24, 1, NULL, 0, 1, 0, 'brand_new', 'Bengaluru, KA', 'active'),
        (3, 2, 1, 2, 'Nordic All-Weather Waterproof Commuter Backpack (30L)', 'nordic-all-weather-waterproof-commuter-backpack', 'B2C-FA-003', 'Ergonomic water-resistant daypack with TSA laptop compartment.', 'Crafted from 900D Oxford nylon with sealed YKK weather-guard zippers.', 2499.00, 1899.00, 120, 1, NULL, 0, 1, 0, 'brand_new', 'Bengaluru, KA', 'active'),
        (4, 2, 1, 3, 'Smart Aroma Ultrasonic Diffuser & Ambient LED Humidifier', 'smart-aroma-ultrasonic-diffuser-humidifier', 'B2C-HL-004', 'Whisper-quiet essential oil diffuser with WiFi app timer.', 'Elevate your workspace with cool-mist ultrasonic vaporization and mood ring illumination.', 1799.00, 1299.00, 60, 1, NULL, 0, 1, 0, 'brand_new', 'Bengaluru, KA', 'active'),
        (5, 2, 1, 5, 'Industrial CNC Micro-Controller Relay Modules (Lot of 100 units)', 'industrial-cnc-micro-controller-relay-modules-lot', 'B2B-IND-101', 'High-current 8-channel optocoupler isolated relay interface boards.', 'Industrial grade relay modules rated at 250V AC/10A. Master carton wholesale lot.', 25000.00, 22000.00, 500, 50, 200.00, 1, 0, 0, 'brand_new', 'Bengaluru, KA', 'active'),
        (6, 2, 1, 6, 'Grade-A 100% Combed Cotton T-Shirt Blanks (Wholesale Pack of 500)', 'grade-a-combed-cotton-t-shirt-blanks-bulk', 'B2B-TEX-202', '180 GSM bio-washed pre-shrunk cotton blank tees ready for printing.', 'Premium textile stock for apparel brands. Minimum order 100 pieces.', 85000.00, 75000.00, 1000, 100, 150.00, 1, 0, 0, 'brand_new', 'Bengaluru, KA', 'active'),
        (7, 2, 1, 7, 'Heavy-Duty 3-Ply Corrugated Shipping Boxes (Bundle of 250 Boxes)', 'heavy-duty-3ply-corrugated-shipping-boxes-bulk', 'B2B-PKG-303', 'Eco-friendly kraft fluted mailer boxes for e-commerce warehouses.', 'Tested to edge-crush standard ECT 32. Dimensions 10x8x4 inches.', 6250.00, 5500.00, 800, 250, 22.00, 1, 0, 0, 'brand_new', 'Bengaluru, KA', 'active'),
        (8, 3, NULL, 10, 'Operating System Concepts (Silberschatz) + CLRS Algorithms (9th & 3rd Ed)', 'operating-system-concepts-clrs-algorithms-textbooks', 'C2C-BK-001', 'Clean academic textbooks used for 2 semesters of CSE major.', 'Original print engineering standard textbooks. Pristine condition with slight notes.', 1400.00, 1100.00, 1, 1, NULL, 0, 0, 1, 'good', 'Mumbai, MH', 'active'),
        (9, 3, NULL, 9, 'Sony Alpha A6000 Mirrorless Camera + 16-50mm Power Zoom Lens', 'sony-alpha-a6000-mirrorless-camera-used', 'C2C-EL-002', '24.3 MP APS-C mirrorless camera body with low shutter count.', 'Includes charger, strap, 64GB card, and 16-50mm kit lens. Sensor spotless.', 28000.00, 26500.00, 1, 1, NULL, 0, 0, 1, 'like_new', 'Mumbai, MH', 'active'),
        (10, 3, NULL, 11, 'Solid Teak Finish Ergonomic Study Desk with Wire Grommet (48x24\") ', 'solid-teak-finish-ergonomic-study-desk', 'C2C-FUR-003', 'Sturdy wooden desk with cable management tray and headphone hook.', 'Scratch-resistant laminate on steel legs. Relocating soon.', 3800.00, 3200.00, 1, 1, NULL, 0, 0, 1, 'good', 'Mumbai, MH', 'active');

        CREATE TABLE IF NOT EXISTS product_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            image_url TEXT NOT NULL,
            is_primary INTEGER DEFAULT 0,
            display_order INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO product_images (id, product_id, image_url, is_primary) VALUES
        (1, 1, 'assets/images/products/headphones-1.jpg', 1),
        (2, 2, 'assets/images/products/laptop-1.jpg', 1),
        (3, 3, 'assets/images/products/backpack-1.jpg', 1),
        (4, 4, 'assets/images/products/diffuser-1.jpg', 1),
        (5, 5, 'assets/images/products/relay-module-1.jpg', 1),
        (6, 6, 'assets/images/products/tshirt-blanks-1.jpg', 1),
        (7, 7, 'assets/images/products/corrugated-boxes-1.jpg', 1),
        (8, 8, 'assets/images/products/cs-textbooks-1.jpg', 1),
        (9, 9, 'assets/images/products/sony-camera-1.jpg', 1),
        (10, 10, 'assets/images/products/study-desk-1.jpg', 1);

        CREATE TABLE IF NOT EXISTS product_inventory (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            change_type TEXT DEFAULT 'initial',
            quantity INTEGER NOT NULL,
            remaining_stock INTEGER NOT NULL,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS carts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            session_id TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO carts (id, user_id, session_id) VALUES (1, 4, 'sess_demo_priya_sharma_cart');

        CREATE TABLE IF NOT EXISTS cart_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cart_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            unit_price REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO cart_items (id, cart_id, product_id, quantity, unit_price) VALUES
        (1, 1, 1, 1, 3999.00),
        (2, 1, 4, 1, 1299.00);

        CREATE TABLE IF NOT EXISTS wishlists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO wishlists (id, user_id) VALUES (1, 4);

        CREATE TABLE IF NOT EXISTS wishlist_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            wishlist_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO wishlist_items (id, wishlist_id, product_id) VALUES
        (1, 1, 2),
        (2, 1, 3);

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_number TEXT UNIQUE NOT NULL,
            user_id INTEGER NOT NULL,
            business_id INTEGER,
            order_type TEXT DEFAULT 'B2C',
            total_amount REAL NOT NULL,
            discount_amount REAL DEFAULT 0.00,
            shipping_fee REAL DEFAULT 0.00,
            tax_amount REAL DEFAULT 0.00,
            final_amount REAL NOT NULL,
            coupon_code TEXT,
            shipping_address TEXT NOT NULL,
            billing_address TEXT,
            order_status TEXT DEFAULT 'Confirmed',
            payment_status TEXT DEFAULT 'Paid',
            tracking_number TEXT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO orders (id, order_number, user_id, business_id, order_type, total_amount, discount_amount, shipping_fee, tax_amount, final_amount, coupon_code, shipping_address, order_status, payment_status, tracking_number) VALUES
        (1, 'MM-ORD-2026-88101', 4, 1, 'B2C', 3999.00, 399.90, 0.00, 180.00, 3779.10, 'WELCOME10', 'Priya Sharma, Flat 402, Sunshine Heights, Outer Ring Road, Bengaluru - 560103, Phone: +91 98765 33333', 'Shipped', 'Paid', 'DELHIVERY-BLR-984421'),
        (2, 'MM-ORD-2026-88102', 4, 1, 'B2C', 1899.00, 0.00, 99.00, 95.00, 2093.00, NULL, 'Priya Sharma, Flat 402, Sunshine Heights, Outer Ring Road, Bengaluru - 560103, Phone: +91 98765 33333', 'Delivered', 'Paid', 'BLUEDART-EXP-110294');

        CREATE TABLE IF NOT EXISTS order_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            seller_id INTEGER NOT NULL,
            product_title TEXT NOT NULL,
            unit_price REAL NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 1,
            subtotal REAL NOT NULL,
            status TEXT DEFAULT 'confirmed',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO order_items (id, order_id, product_id, seller_id, product_title, unit_price, quantity, subtotal, status) VALUES
        (1, 1, 1, 2, 'Quantum Pro Wireless ANC Headphones with Spatial Audio', 3999.00, 1, 3999.00, 'dispatched'),
        (2, 2, 3, 2, 'Nordic All-Weather Waterproof Commuter Backpack (30L)', 1899.00, 1, 1899.00, 'delivered');

        CREATE TABLE IF NOT EXISTS payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            payment_method TEXT NOT NULL,
            transaction_reference TEXT UNIQUE NOT NULL,
            amount REAL NOT NULL,
            payment_status TEXT DEFAULT 'Success',
            gateway_response TEXT,
            is_demo INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO payments (id, order_id, user_id, payment_method, transaction_reference, amount, payment_status, gateway_response, is_demo) VALUES
        (1, 1, 4, 'UPI', 'TXN_SIM_UPI_9831411200', 3779.10, 'Success', '{\"status\":\"SUCCESS\",\"simulated\":true}', 1),
        (2, 2, 4, 'Credit Card', 'TXN_SIM_CC_7749102231', 2093.00, 'Success', '{\"status\":\"SUCCESS\",\"simulated\":true}', 1);

        CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            payment_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            type TEXT DEFAULT 'debit',
            amount REAL NOT NULL,
            reference_code TEXT UNIQUE NOT NULL,
            description TEXT NOT NULL,
            status TEXT DEFAULT 'completed',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO transactions (id, payment_id, user_id, type, amount, reference_code, description, status) VALUES
        (1, 1, 4, 'debit', 3779.10, 'LEDGER-REF-001', 'Settlement for Order #MM-ORD-2026-88101', 'completed'),
        (2, 2, 4, 'debit', 2093.00, 'LEDGER-REF-002', 'Settlement for Order #MM-ORD-2026-88102', 'completed');

        CREATE TABLE IF NOT EXISTS coupons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code TEXT UNIQUE NOT NULL,
            description TEXT NOT NULL,
            discount_type TEXT DEFAULT 'percentage',
            discount_value REAL NOT NULL,
            min_spend REAL DEFAULT 500.00,
            max_discount REAL DEFAULT 1000.00,
            usage_limit INTEGER DEFAULT 500,
            used_count INTEGER DEFAULT 1,
            start_date DATE NOT NULL,
            expiry_date DATE NOT NULL,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO coupons (id, code, description, discount_type, discount_value, min_spend, max_discount, start_date, expiry_date, status) VALUES
        (1, 'WELCOME10', 'Welcome 10% off', 'percentage', 10.00, 500.00, 500.00, '2026-01-01', '2027-12-31', 'active'),
        (2, 'MULTISAVE20', '20% off over Rs. 2000', 'percentage', 20.00, 2000.00, 1000.00, '2026-01-01', '2027-12-31', 'active'),
        (3, 'FLAT500', 'Flat Rs. 500 off', 'fixed', 500.00, 5000.00, 500.00, '2026-01-01', '2027-12-31', 'active');

        CREATE TABLE IF NOT EXISTS reviews (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER,
            service_id INTEGER,
            user_id INTEGER NOT NULL,
            rating INTEGER NOT NULL,
            review_title TEXT NOT NULL,
            review_text TEXT NOT NULL,
            review_type TEXT DEFAULT 'product',
            status TEXT DEFAULT 'approved',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO reviews (id, product_id, service_id, user_id, rating, review_title, review_text, review_type, status) VALUES
        (1, 1, NULL, 4, 5, 'Superb sound quality & comfy ear-cups!', 'Tested this headset for online classes and music mixing. The active noise cancellation cuts out ceiling fan buzz effortlessly.', 'product', 'approved'),
        (2, 3, NULL, 4, 4, 'Very durable backpack for daily commute', 'Plenty of compartments, safe padded laptop section and genuine waterproof fabric.', 'product', 'approved');

        CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            message TEXT NOT NULL,
            link TEXT,
            type TEXT DEFAULT 'system',
            is_read INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO notifications (id, user_id, title, message, link, type, is_read) VALUES
        (1, 4, 'Order Dispatched!', 'Your order #MM-ORD-2026-88101 has been dispatched.', 'orders/track.php?order=MM-ORD-2026-88101', 'order', 0),
        (2, 2, 'New B2B Quotation Request Received', 'A wholesale inquiry was initiated for CNC Relay Modules.', 'seller/rfqs.php', 'quote', 0),
        (3, 5, 'New Project Proposal Invitation', 'Apex Industrial Technologies viewed your Data Science profile.', 'services/requests.php', 'service', 0);

        CREATE TABLE IF NOT EXISTS business_quotes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            quote_number TEXT UNIQUE NOT NULL,
            buyer_id INTEGER NOT NULL,
            seller_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            target_price REAL,
            message TEXT NOT NULL,
            seller_quote_price REAL,
            seller_notes TEXT,
            validity_date DATE,
            status TEXT DEFAULT 'requested',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO business_quotes (id, quote_number, buyer_id, seller_id, product_id, quantity, target_price, message, seller_quote_price, seller_notes, validity_date, status) VALUES
        (1, 'RFQ-2026-0091', 4, 2, 5, 100, 18000.00, 'Need 100 sets for institutional IoT hardware robotics lab.', 19500.00, 'Can provide institutional pricing Rs. 195/unit with 1 year warranty.', '2026-12-31', 'responded');

        CREATE TABLE IF NOT EXISTS quote_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            quote_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            requested_qty INTEGER NOT NULL,
            offered_unit_price REAL NOT NULL,
            line_total REAL NOT NULL,
            notes TEXT
        );
        INSERT OR IGNORE INTO quote_items (id, quote_id, product_id, requested_qty, offered_unit_price, line_total, notes) VALUES
        (1, 1, 5, 100, 195.00, 19500.00, 'Pre-tested industrial grade modules');

        CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            freelancer_id INTEGER NOT NULL,
            category_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            description TEXT NOT NULL,
            skills_required TEXT NOT NULL,
            basic_price REAL NOT NULL,
            standard_price REAL NOT NULL,
            premium_price REAL NOT NULL,
            delivery_days INTEGER DEFAULT 5,
            portfolio_url TEXT DEFAULT 'https://github.com/freelancer-portfolio',
            status TEXT DEFAULT 'active',
            views_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO services (id, freelancer_id, category_id, title, slug, description, skills_required, basic_price, standard_price, premium_price, delivery_days, portfolio_url, status) VALUES
        (1, 5, 14, 'End-to-End Enterprise Data Science Pipeline & Interactive ML Dashboard', 'enterprise-data-science-pipeline-ml-dashboard', 'Will build robust ETL data pipelines, statistical model training and interactive dashboards.', 'Python, Pandas, Scikit-Learn, Streamlit, SQL', 4999.00, 12999.00, 24999.00, 7, 'https://github.com/rahulverma-ai', 'active'),
        (2, 5, 13, 'Full-Stack Custom PHP & React E-Commerce Microservices Development', 'fullstack-php-react-ecommerce-development', 'Professional development of secure, scalable web applications with PHP 8 and RESTful APIs.', 'PHP 8, MySQL, Bootstrap 5, JavaScript', 3499.00, 8999.00, 18999.00, 5, 'https://github.com/rahulverma-dev', 'active');

        CREATE TABLE IF NOT EXISTS service_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            request_number TEXT UNIQUE NOT NULL,
            client_id INTEGER NOT NULL,
            freelancer_id INTEGER NOT NULL,
            service_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            requirements TEXT NOT NULL,
            budget REAL NOT NULL,
            deadline DATE NOT NULL,
            selected_tier TEXT DEFAULT 'standard',
            status TEXT DEFAULT 'in_progress',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO service_requests (id, request_number, client_id, freelancer_id, service_id, title, requirements, budget, deadline, selected_tier, status) VALUES
        (1, 'REQ-2026-SRV-1001', 2, 5, 1, 'Predictive Inventory Restock Forecasting Model for Apex Supplies', 'Need an ML forecasting script analyzing historical purchase logs.', 12999.00, '2026-12-15', 'standard', 'in_progress');

        CREATE TABLE IF NOT EXISTS proposals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            service_request_id INTEGER NOT NULL,
            freelancer_id INTEGER NOT NULL,
            cover_letter TEXT NOT NULL,
            proposed_amount REAL NOT NULL,
            delivery_days INTEGER NOT NULL,
            status TEXT DEFAULT 'accepted',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO proposals (id, service_request_id, freelancer_id, cover_letter, proposed_amount, delivery_days, status) VALUES
        (1, 1, 5, 'I have built time-series forecasting models (ARIMA & XGBoost). Delivering clean Python code.', 12999.00, 7, 'accepted');

        CREATE TABLE IF NOT EXISTS conversations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sender_id INTEGER NOT NULL,
            receiver_id INTEGER NOT NULL,
            subject TEXT NOT NULL,
            related_type TEXT DEFAULT 'general',
            related_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO conversations (id, sender_id, receiver_id, subject, related_type, related_id) VALUES
        (1, 4, 3, 'Inquiry regarding Sony A6000 Camera condition (C2C Listing)', 'product', 9),
        (2, 2, 5, 'Milestone review for Inventory Restock ML Project', 'service', 1);

        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            conversation_id INTEGER NOT NULL,
            sender_id INTEGER NOT NULL,
            message_text TEXT NOT NULL,
            attachment TEXT,
            is_read INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO messages (id, conversation_id, sender_id, message_text, is_read) VALUES
        (1, 1, 4, 'Hello John! Is the Sony A6000 camera still available? Can we do local pickup near Bandra?', 1),
        (2, 1, 3, 'Hi Priya! Yes, it is fully available. Local pickup around Bandra station works great.', 1),
        (3, 2, 2, 'Hi Rahul, we reviewed your initial dataset analysis notebook. Looking good!', 0);

        CREATE TABLE IF NOT EXISTS reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reporter_id INTEGER NOT NULL,
            reported_type TEXT DEFAULT 'product',
            reported_id INTEGER NOT NULL,
            reason TEXT NOT NULL,
            details TEXT,
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO reports (id, reporter_id, reported_type, reason, details, status) VALUES
        (1, 4, 'product', 'Incorrect Category or Details Check', 'User requested quick confirmation of edition.', 'reviewed');

        CREATE TABLE IF NOT EXISTS returns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            order_item_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            reason TEXT NOT NULL,
            return_status TEXT DEFAULT 'requested',
            refund_amount REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS activity_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action TEXT NOT NULL,
            entity_type TEXT,
            entity_id INTEGER,
            ip_address TEXT DEFAULT '127.0.0.1',
            user_agent TEXT,
            details TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        INSERT OR IGNORE INTO activity_logs (id, user_id, action, entity_type, entity_id, details) VALUES
        (1, 1, 'SYSTEM_INIT', 'DATABASE', 1, 'Database initialized with 30-table normalized multi-model architecture.'),
        (2, 4, 'USER_LOGIN', 'AUTH', 4, 'Customer authenticated successfully.'),
        (3, 4, 'CHECKOUT_COMPLETED', 'ORDER', 1, 'Simulated payment verified for Order #MM-ORD-2026-88101.');
        ";

        $pdo->exec($schema);
    }
}

/**
 * Procedural Database Utility Functions
 */
function get_db(): PDO {
    return Database::getConnection();
}

function db_query(string $sql, array $params = []): PDOStatement {
    $db = get_db();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_fetch_all(string $sql, array $params = []): array {
    $stmt = db_query($sql, $params);
    return $stmt->fetchAll();
}

function db_fetch_one(string $sql, array $params = []): ?array {
    $stmt = db_query($sql, $params);
    $res = $stmt->fetch();
    return $res === false ? null : $res;
}

function db_insert(string $table, array $data): int {
    $db = get_db();
    $keys = array_keys($data);
    $fields = implode(', ', array_map(fn($k) => "`$k`", $keys));
    if (Database::getDriver() === 'sqlite') {
        $fields = implode(', ', $keys);
    }
    $placeholders = implode(', ', array_fill(0, count($keys), '?'));
    $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($data));
    return (int)$db->lastInsertId();
}

function db_update(string $table, array $data, string $where, array $where_params = []): int {
    $db = get_db();
    $sets = [];
    $isSqlite = Database::getDriver() === 'sqlite';
    foreach (array_keys($data) as $k) {
        $field = $isSqlite ? $k : "`$k`";
        $sets[] = "{$field} = ?";
    }
    $setClause = implode(', ', $sets);
    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $params = array_merge(array_values($data), $where_params);
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

function db_delete(string $table, string $where, array $where_params = []): int {
    $db = get_db();
    $sql = "DELETE FROM {$table} WHERE {$where}";
    $stmt = $db->prepare($sql);
    $stmt->execute($where_params);
    return $stmt->rowCount();
}

function log_activity(string $action, ?string $entity_type = null, ?int $entity_id = null, ?string $details = null, ?int $user_id = null): void {
    try {
        $uid = $user_id ?: ($_SESSION['user_id'] ?? null);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Browser';
        db_insert('activity_logs', [
            'user_id' => $uid,
            'action' => $action,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'ip_address' => substr($ip, 0, 45),
            'user_agent' => substr($ua, 0, 250),
            'details' => $details
        ]);
    } catch (Exception $e) {
        // Suppress audit log failures silently so main flow is never interrupted
    }
}

function send_notification(int $user_id, string $title, string $message, ?string $link = null, string $type = 'system'): int {
    try {
        return db_insert('notifications', [
            'user_id' => $user_id,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'type' => $type,
            'is_read' => 0
        ]);
    } catch (Exception $e) {
        return 0;
    }
}
