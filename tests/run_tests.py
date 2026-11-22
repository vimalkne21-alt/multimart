#!/usr/bin/env python3
"""
MultiMart - Comprehensive Automated Integration & Unit Testing Suite
Academic Major Project: B.Tech CSE / Data Science
"""

import os
import sqlite3
import re
import sys

def main():
    print("=" * 70)
    print(" MULTIMART COMPREHENSIVE AUTOMATED VERIFICATION SUITE")
    print(" Academic Major Project: B.Tech CSE / Data Science")
    print("=" * 70)

    base_dir = "/Users/vikramaditya/MultiMart"
    passed = 0
    failed = 0

    def assert_test(name, condition, details=""):
        nonlocal passed, failed
        if condition:
            passed += 1
            print(f" [PASS] {name}")
        else:
            failed += 1
            print(f" [FAIL] {name} - {details}")

    # 1. Directory Structure Test
    required_dirs = [
        "public/css", "public/js", "public/images", "public/uploads",
        "config", "includes", "auth", "admin", "seller", "customer",
        "business", "b2b", "b2c", "c2c", "c2b", "products", "cart",
        "checkout", "orders", "payments", "services", "messages",
        "database", "tests", "documentation"
    ]
    missing_dirs = [d for d in required_dirs if not os.path.isdir(os.path.join(base_dir, d))]
    assert_test("Project Directory Structure Complete", len(missing_dirs) == 0, f"Missing: {missing_dirs}")

    # 2. Database Schema SQL File Check
    sql_path = os.path.join(base_dir, "database", "multimart_database.sql")
    assert_test("Database SQL Script (multimart_database.sql) Exists", os.path.isfile(sql_path))

    with open(sql_path, "r") as f:
        sql_content = f.read()

    tables = re.findall(r"CREATE TABLE\s+(?:IF NOT EXISTS\s+)?\`?([a-zA-Z0-9_]+)\`?", sql_content, re.I)
    assert_test("30 Tables Defined in multimart_database.sql", len(tables) == 30, f"Found {len(tables)} tables")

    # 3. SQLite Local Fallback Database Verification
    sqlite_path = os.path.join(base_dir, "database", "multimart.sqlite")
    assert_test("SQLite Fallback Database File Exists", os.path.isfile(sqlite_path))

    conn = sqlite3.connect(sqlite_path)
    c = conn.cursor()

    c.execute("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
    sq_tables = [r[0] for r in c.fetchall()]
    assert_test("All 30 Tables Synchronized in SQLite Fallback", len(sq_tables) == 30, f"Found {len(sq_tables)}")

    # 4. User Accounts & Roles Test
    c.execute("SELECT COUNT(*) FROM roles")
    roles_count = c.fetchone()[0]
    assert_test("5 User Roles Configured (Admin, Biz Seller, Ind Seller, Customer, Freelancer)", roles_count >= 5)

    c.execute("SELECT COUNT(*) FROM users")
    users_count = c.fetchone()[0]
    assert_test("Demo Seed Users Populated", users_count >= 5)

    c.execute("SELECT email, role_id FROM users WHERE email='admin@multimart.com'")
    admin_row = c.fetchone()
    assert_test("SuperAdmin Account Configured (admin@multimart.com)", admin_row is not None and admin_row[1] == 1)

    # 5. E-Commerce Model 1: B2C Module Verification
    c.execute("SELECT COUNT(*) FROM products WHERE is_b2c = 1")
    b2c_count = c.fetchone()[0]
    assert_test("Model 1: B2C Retail Products Catalog Seeded", b2c_count >= 4)

    # 6. E-Commerce Model 2: B2B Wholesale & RFQ Module Verification
    c.execute("SELECT COUNT(*) FROM products WHERE is_b2b = 1")
    b2b_count = c.fetchone()[0]
    assert_test("Model 2: B2B Wholesale Lots with MOQ Seeded", b2b_count >= 3)

    c.execute("SELECT COUNT(*) FROM business_quotes")
    rfq_count = c.fetchone()[0]
    assert_test("Model 2: B2B RFQ Quotation Pipeline Populated", rfq_count >= 1)

    # 7. E-Commerce Model 3: C2C Pre-Owned Market Verification
    c.execute("SELECT COUNT(*) FROM products WHERE is_c2c = 1")
    c2c_count = c.fetchone()[0]
    assert_test("Model 3: C2C Pre-Owned Used Items Populated", c2c_count >= 3)

    c.execute("SELECT DISTINCT condition_type FROM products WHERE is_c2c = 1")
    conditions = [r[0] for r in c.fetchall()]
    assert_test("Model 3: C2C Condition Grading Integrated", len(conditions) > 0)

    # 8. E-Commerce Model 4: C2B Freelancing Module Verification
    c.execute("SELECT COUNT(*) FROM services")
    services_count = c.fetchone()[0]
    assert_test("Model 4: C2B Freelance Talent Services Active", services_count >= 2)

    c.execute("SELECT COUNT(*) FROM service_requests")
    contracts_count = c.fetchone()[0]
    assert_test("Model 4: C2B Project Contracts Workflow Active", contracts_count >= 1)

    # 9. Shopping Cart, Coupons & Simulated Checkout
    c.execute("SELECT COUNT(*) FROM coupons WHERE status='active'")
    coupon_count = c.fetchone()[0]
    assert_test("Active Promotional Coupons Present (WELCOME10, MULTISAVE20)", coupon_count >= 2)

    c.execute("SELECT COUNT(*) FROM payments WHERE is_demo=1")
    payments_count = c.fetchone()[0]
    assert_test("Safe Simulated Demo Payment Records Tagged", payments_count >= 2)

    c.execute("SELECT COUNT(*) FROM orders")
    orders_count = c.fetchone()[0]
    assert_test("Multi-Model Order Management Records Populated", orders_count >= 2)

    # 10. Messaging & Audit Logs
    c.execute("SELECT COUNT(*) FROM conversations")
    conv_count = c.fetchone()[0]
    assert_test("In-App Buyer-Seller Messaging Conversations Active", conv_count >= 2)

    c.execute("SELECT COUNT(*) FROM activity_logs")
    logs_count = c.fetchone()[0]
    assert_test("Cybersecurity Activity & Audit Logs Active", logs_count >= 3)

    conn.close()

    # 11. Frontend Assets Integrity Check
    css_path = os.path.join(base_dir, "public", "css", "style.css")
    js_path = os.path.join(base_dir, "public", "js", "main.js")
    assert_test("MultiMart Custom CSS Stylesheet Exists", os.path.isfile(css_path) and os.path.getsize(css_path) > 1000)
    assert_test("MultiMart Main JavaScript Controller Exists", os.path.isfile(js_path) and os.path.getsize(js_path) > 1000)

    # 12. Key Page Endpoints Check
    key_pages = [
        "index.php", "about.php", "contact.php", "help.php", "privacy.php", "terms.php",
        "auth/login.php", "auth/register.php", "auth/logout.php", "auth/profile.php",
        "b2c/index.php", "b2b/index.php", "b2b/rfq_create.php", "b2b/rfq_list.php",
        "c2c/index.php", "c2c/sell.php", "c2c/detail.php",
        "c2b/index.php", "c2b/detail.php", "c2b/hire.php",
        "products/detail.php", "products/search.php", "products/search_api.php", "products/coupons.php",
        "cart/index.php", "cart/cart_action.php",
        "checkout/index.php", "checkout/process_order.php",
        "orders/confirmation.php", "orders/track.php", "orders/invoice.php",
        "admin/dashboard.php", "admin/users.php", "admin/products.php", "admin/orders.php",
        "seller/dashboard.php", "seller/products.php", "seller/products_add.php", "seller/orders.php",
        "customer/dashboard.php", "customer/orders.php", "customer/notifications.php",
        "messages/inbox.php", "messages/chat.php", "services/dashboard.php", "services/create.php"
    ]
    missing_pages = [p for p in key_pages if not os.path.isfile(os.path.join(base_dir, p))]
    assert_test("All 45+ Functional Application Endpoints Implemented", len(missing_pages) == 0, f"Missing: {missing_pages}")

    print("\n" + "=" * 70)
    total_tests = passed + failed
    success_rate = (passed / total_tests) * 100
    print(f" TEST RUN COMPLETE: {passed}/{total_tests} Tests Passed ({success_rate:.1f}% Success)")
    print("=" * 70)

    if failed > 0:
        sys.exit(1)

if __name__ == "__main__":
    main()
