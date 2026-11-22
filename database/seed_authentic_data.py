#!/usr/bin/env python3
"""
MultiMart - Authentic Original Commercial Data Seeder
Populates 100% genuine, realistic, market-accurate products, wholesale lots,
verified pre-owned items, and professional freelance services.
Academic Major Project: B.Tech CSE / Data Science
"""

import sqlite3
import os

DB_PATH = "/Users/vikramaditya/MultiMart/database/multimart.sqlite"

def seed():
    conn = sqlite3.connect(DB_PATH)
    c = conn.cursor()

    # Clear existing demo/test products and services
    c.execute("DELETE FROM product_images")
    c.execute("DELETE FROM reviews")
    c.execute("DELETE FROM products")
    c.execute("DELETE FROM services")

    # 1. B2C RETAIL PRODUCTS (100% Authentic Commercial Hardware & Goods)
    b2c_products = [
        {
            "seller_id": 2, "business_id": 1, "category_id": 1,
            "title": "Apple MacBook Pro 14\" M3 Pro (18GB Unified Memory, 512GB SSD, Space Black)",
            "slug": "apple-macbook-pro-14-m3-pro-space-black",
            "sku": "APL-MBP14-M3P-BLK",
            "short_description": "14.2-inch Liquid Retina XDR display, Apple M3 Pro 11-core CPU, 14-core GPU.",
            "description": "Engineered for pro workflows, machine learning models, and heavy 4K video rendering. Features an 11-core CPU, 14-core GPU, 18GB unified memory, and up to 18 hours of battery life. Liquid Retina XDR display with 1600 nits peak HDR brightness and ProMotion 120Hz.",
            "price": 199900.0, "discount_price": 189900.0, "stock_quantity": 24,
            "is_b2c": 1, "is_b2b": 0, "is_c2c": 0, "condition_type": "New", "location": "Bengaluru, KA",
            "images": [
                "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&auto=format&fit=crop&q=80",
                "https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 2, "business_id": 1, "category_id": 1,
            "title": "Sony WH-1000XM5 Wireless Industry-Leading Noise Cancelling Headphones",
            "slug": "sony-wh1000xm5-noise-cancelling-silver",
            "sku": "SNY-WH1000XM5-SLV",
            "short_description": "Auto NC Optimizer, 8 microphones, 30-hour battery life with quick charge.",
            "description": "Two processors and eight microphones deliver magnanimous active noise cancellation. Ultra-comfortable lightweight design in soft fit leather. Crystal-clear hands-free calling with 4 beamforming microphones and AI noise reduction.",
            "price": 29990.0, "discount_price": 26990.0, "stock_quantity": 48,
            "is_b2c": 1, "is_b2b": 0, "is_c2c": 0, "condition_type": "New", "location": "Mumbai, MH",
            "images": [
                "https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&auto=format&fit=crop&q=80",
                "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 2, "business_id": 1, "category_id": 1,
            "title": "Samsung Galaxy S24 Ultra 5G (Titanium Gray, 12GB RAM, 256GB Storage)",
            "slug": "samsung-galaxy-s24-ultra-5g-titanium-gray",
            "sku": "SAM-S24U-256-GRY",
            "short_description": "Snapdragon 8 Gen 3 for Galaxy, 200MP Quad Telephoto Camera, Built-in S-Pen.",
            "description": "Welcome to the era of mobile AI with Galaxy AI. 6.8\" Dynamic AMOLED 2X flat display with Corning Gorilla Armor. Titanium frame exterior with IP68 water and dust resistance, integrated S-Pen stylus, and ray-tracing graphics.",
            "price": 129999.0, "discount_price": 121999.0, "stock_quantity": 30,
            "is_b2c": 1, "is_b2b": 0, "is_c2c": 0, "condition_type": "New", "location": "Delhi NCR",
            "images": [
                "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&auto=format&fit=crop&q=80",
                "https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 2, "business_id": 1, "category_id": 3,
            "title": "Dyson V12 Detect Slim Total Clean Cordless Vacuum Cleaner",
            "slug": "dyson-v12-detect-slim-cordless-vacuum",
            "sku": "DYS-V12-SLIM-VCM",
            "short_description": "Laser reveals microscopic dust, Piezo sensor counts particles, 150 AW suction.",
            "description": "Dyson's lightest intelligent cordless vacuum with laser illumination. Scientifically proves a deep clean with a real-time LCD screen displaying particle counts. Up to 60 minutes of run time with click-in swappable battery.",
            "price": 55900.0, "discount_price": 49900.0, "stock_quantity": 18,
            "is_b2c": 1, "is_b2b": 0, "is_c2c": 0, "condition_type": "New", "location": "Bengaluru, KA",
            "images": [
                "https://images.unsplash.com/photo-1558317374-067fb5f30001?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 2, "business_id": 1, "category_id": 3,
            "title": "De'Longhi Dedica Deluxe Pump Espresso Coffee Machine EC685.M",
            "slug": "delonghi-dedica-deluxe-espresso-machine",
            "sku": "DLG-EC685-SLV",
            "short_description": "15-bar professional pressure, adjustable manual cappuccino system, stainless steel.",
            "description": "Traditional barista-quality espresso and cappuccino in a compact 6-inch slim footprint. Features Thermoblock heating technology, 15-bar pressure extraction, 3-in-1 filter holder for single, double, and ESE pods.",
            "price": 22490.0, "discount_price": 19990.0, "stock_quantity": 25,
            "is_b2c": 1, "is_b2b": 0, "is_c2c": 0, "condition_type": "New", "location": "Pune, MH",
            "images": [
                "https://images.unsplash.com/photo-1517668808822-9ebb02f2a0e6?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 2, "business_id": 1, "category_id": 2,
            "title": "Nike Air Zoom Pegasus 40 Men's Road Running Shoes",
            "slug": "nike-air-zoom-pegasus-40-running-shoes",
            "sku": "NKE-PEG40-BLK-UK9",
            "short_description": "Dual Zoom Air units, React foam midsole, engineered single-layer mesh.",
            "description": "The workhorse with wings returns. Tuned comfort and spring in your step for daily miles, road running, and marathon training. Redesigned midfoot strap provides an accommodating fit for all arch profiles.",
            "price": 10495.0, "discount_price": 8995.0, "stock_quantity": 40,
            "is_b2c": 1, "is_b2b": 0, "is_c2c": 0, "condition_type": "New", "location": "Hyderabad, TS",
            "images": [
                "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80"
            ]
        }
    ]

    # 2. B2B WHOLESALE PRODUCTS (Industrial, Raw Materials, Bulk Lots)
    b2b_products = [
        {
            "seller_id": 2, "business_id": 1, "category_id": 5,
            "title": "Adani Solar 550W Mono PERC Bifacial Photovoltaic Panels (Pallet of 31 Units)",
            "slug": "adani-solar-550w-mono-perc-bifacial-pallet",
            "sku": "B2B-SLR-550W-PL31",
            "short_description": "Grade A+ Tier 1 144-cell dual glass bifacial solar modules with 25-year performance warranty.",
            "description": "Industrial grade high-efficiency solar modules designed for commercial rooftop and ground-mount PV plants. Up to 21.3% module efficiency, IP68 junction boxes with MC4 connectors, and PID-free certified cells. GST Input Tax Credit compliant invoice included.",
            "price": 341000.0, "bulk_price": 11000.0, "min_order_qty": 31, "stock_quantity": 310,
            "is_b2c": 0, "is_b2b": 1, "is_c2c": 0, "condition_type": "New", "location": "Mundra SEZ / Ahmedabad, GJ",
            "images": [
                "https://images.unsplash.com/photo-1509391365360-2e959784a276?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 2, "business_id": 1, "category_id": 5,
            "title": "SS-304 Grade Hex Bolts & Nuts Industrial Fastener Assortment (Carton of 500 pcs)",
            "slug": "ss304-hex-bolts-nuts-carton-500",
            "sku": "B2B-FST-SS304-CT500",
            "short_description": "Corrosion resistant Austenitic Stainless Steel M8/M10/M12 fasteners with mill test certificates.",
            "description": "Standard DIN 933 / ISO 4017 fully threaded precision bolts with matching nylon lock nuts. Designed for structural engineering, automotive manufacturing, and heavy equipment fabrication.",
            "price": 14500.0, "bulk_price": 2900.0, "min_order_qty": 5, "stock_quantity": 120,
            "is_b2c": 0, "is_b2b": 1, "is_c2c": 0, "condition_type": "New", "location": "Chennai Industrial Estate, TN",
            "images": [
                "https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 2, "business_id": 1, "category_id": 7,
            "title": "3-Ply Heavy-Duty Corrugated Shipping Boxes (Bundle of 100 pcs, 14x10x8\")",
            "slug": "3-ply-corrugated-shipping-boxes-bundle-100",
            "sku": "B2B-PKG-BOX-BD100",
            "short_description": "180 GSM Kraft paper, bursting strength 16 kg/sq.cm, precision die-cut for e-commerce dispatch.",
            "description": "Cost-effective, highly durable packaging cartons for warehousing, retail packaging, and courier dispatches. 100% recyclable, moisture resistant outer layer with pre-creased folding flaps.",
            "price": 4800.0, "bulk_price": 450.0, "min_order_qty": 10, "stock_quantity": 400,
            "is_b2c": 0, "is_b2b": 1, "is_c2c": 0, "condition_type": "New", "location": "Faridabad, HR",
            "images": [
                "https://images.unsplash.com/photo-1589710751893-f9a6770ad71b?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 2, "business_id": 1, "category_id": 5,
            "title": "ESP32-WROOM-32D Dual-Core Wi-Fi + Bluetooth IoT ICs (Tray of 50 pcs)",
            "slug": "esp32-wroom-32d-iot-microcontroller-tray-50",
            "sku": "B2B-ELC-ESP32-TR50",
            "short_description": "Original Espressif Systems 4MB SPI Flash SoC modules for IoT manufacturing and automation.",
            "description": "Ultra-low power dual-core Tensilica Xtensa 32-bit LX6 microprocessors running at 240MHz. Integrated Wi-Fi 802.11 b/g/n and Bluetooth 4.2 BR/EDR & BLE. RoHS certified for commercial embedded production.",
            "price": 14950.0, "bulk_price": 285.0, "min_order_qty": 2, "stock_quantity": 85,
            "is_b2c": 0, "is_b2b": 1, "is_c2c": 0, "condition_type": "New", "location": "Electronic City, Bengaluru, KA",
            "images": [
                "https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&auto=format&fit=crop&q=80"
            ]
        }
    ]

    # 3. C2C PRE-OWNED MARKETPLACE (Authentic Verified Individual Items)
    c2c_products = [
        {
            "seller_id": 3, "business_id": None, "category_id": 9,
            "title": "Apple iPad Air 5th Gen (M1 Chip, 64GB, Wi-Fi, Space Grey) + Apple Pencil 2",
            "slug": "apple-ipad-air-m1-64gb-pencil2-used",
            "sku": "C2C-IPD-AIR5-M1",
            "short_description": "Purchased Nov 2023, 98% battery health, Spigen tempered glass applied from day one.",
            "description": "Selling my personal iPad Air M1 used strictly for college notes and PDF reading. Includes original box, Apple 20W USB-C charger, braided cable, and original Apple Pencil 2nd Generation with magnetic charging. Flawless body, zero scratches.",
            "price": 42000.0, "discount_price": 42000.0, "stock_quantity": 1,
            "is_b2c": 0, "is_b2b": 0, "is_c2c": 1, "condition_type": "Like New", "location": "Indiranagar, Bengaluru, KA",
            "images": [
                "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 3, "business_id": None, "category_id": 9,
            "title": "Sony PlayStation 5 Disc Edition + 2 DualSense Controllers + God of War Ragnarok",
            "slug": "sony-ps5-disc-edition-2-controllers-used",
            "sku": "C2C-SNY-PS5-DISC",
            "short_description": "Indian retail unit (CFI-1208A), runs whisper quiet, includes HDMI 2.1 cable and retail box.",
            "description": "Mint condition PS5 disc console. Never modified or opened, smoke-free home. Comes with two original DualSense wireless controllers (Midnight Black and Cosmic Red) and physical disc copy of God of War Ragnarok. Face-to-face test and pickup available.",
            "price": 41500.0, "discount_price": 41500.0, "stock_quantity": 1,
            "is_b2c": 0, "is_b2b": 0, "is_c2c": 1, "condition_type": "Mint", "location": "Koramangala, Bengaluru, KA",
            "images": [
                "https://images.unsplash.com/photo-1606813907291-d86efa9b94db?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 3, "business_id": None, "category_id": 11,
            "title": "Herman Miller Sayl Ergonomic Office Chair (Fog White Frame & Slate Mesh)",
            "slug": "herman-miller-sayl-ergonomic-chair-used",
            "sku": "C2C-HML-SAYL-WHT",
            "short_description": "Original Herman Miller 3D Intelligent suspension back, fully adjustable arms and seat depth.",
            "description": "Purchased for home office during remote work. Unmatched lumbar support and spine alignment. All hydraulic pistons, tilt tensioners, and armrest height adjusters function like new. Cleaned and sanitized.",
            "price": 36000.0, "discount_price": 36000.0, "stock_quantity": 1,
            "is_b2c": 0, "is_b2b": 0, "is_c2c": 1, "condition_type": "Excellent", "location": "HSR Layout, Bengaluru, KA",
            "images": [
                "https://images.unsplash.com/photo-1580481077195-c3a8a30f3177?w=800&auto=format&fit=crop&q=80"
            ]
        },
        {
            "seller_id": 3, "business_id": None, "category_id": 9,
            "title": "Canon EOS R50 Mirrorless Camera + RF-S 18-45mm IS STM Lens Kit",
            "slug": "canon-eos-r50-mirrorless-camera-kit",
            "sku": "C2C-CAN-R50-KIT",
            "short_description": "Shutter count under 2,200. 24.2 MP APS-C sensor, 4K 30p uncropped video.",
            "description": "Upgrading to full-frame cinema setup. Perfect for vlogging, product photography, and YouTube creators. Dual Pixel CMOS AF II with vehicle, animal, and eye detection. Includes 64GB SanDisk Extreme Pro SDXC card and neck strap.",
            "price": 48000.0, "discount_price": 48000.0, "stock_quantity": 1,
            "is_b2c": 0, "is_b2b": 0, "is_c2c": 1, "condition_type": "Like New", "location": "Whitefield, Bengaluru, KA",
            "images": [
                "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800&auto=format&fit=crop&q=80"
            ]
        }
    ]

    all_products = b2c_products + b2b_products + c2c_products

    for p in all_products:
        c.execute("""
            INSERT INTO products (
                seller_id, business_id, category_id, title, slug, sku,
                short_description, description, price, discount_price, stock_quantity,
                min_order_qty, bulk_price, is_b2b, is_b2c, is_c2c, condition_type,
                location, status, views_count, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 120, datetime('now'), datetime('now'))
        """, (
            p["seller_id"], p.get("business_id"), p["category_id"], p["title"], p["slug"], p["sku"],
            p["short_description"], p["description"], p["price"], p.get("discount_price"), p["stock_quantity"],
            p.get("min_order_qty", 1), p.get("bulk_price"), p["is_b2b"], p["is_b2c"], p["is_c2c"],
            p.get("condition_type", "New"), p.get("location", "India")
        ))
        prod_id = c.lastrowid

        for idx, img in enumerate(p.get("images", [])):
            c.execute("""
                INSERT INTO product_images (product_id, image_url, is_primary, display_order, created_at)
                VALUES (?, ?, ?, ?, datetime('now'))
            """, (prod_id, img, 1 if idx == 0 else 0, idx))

        # Add 1-2 realistic buyer reviews
        c.execute("""
            INSERT INTO reviews (product_id, user_id, rating, review_title, review_text, review_type, status, created_at)
            VALUES (?, 4, 5, 'Exceptional Quality & Express Delivery', '100% genuine product. Packaging was immaculate and arrived within 24 hours in Bengaluru.', 'product', 'approved', datetime('now'))
        """, (prod_id,))

    # 4. C2B PROFESSIONAL FREELANCE SERVICES
    c2b_services = [
        {
            "freelancer_id": 5, "category_id": 13,
            "title": "Enterprise Full-Stack Web Application (Next.js, FastAPI, PostgreSQL & Docker)",
            "slug": "enterprise-fullstack-web-application-development",
            "description": "End-to-end scalable SaaS & portal development with clean modular microservices, REST/GraphQL APIs, role-based JWT auth, and automated CI/CD pipelines.",
            "skills_required": "React, Next.js, Python, FastAPI, Docker, PostgreSQL, Redis, TailwindCSS",
            "basic_price": 45000.0, "standard_price": 85000.0, "premium_price": 140000.0,
            "delivery_days": 14, "portfolio_url": "https://github.com/rahul-verma-enterprise"
        },
        {
            "freelancer_id": 5, "category_id": 14,
            "title": "Custom AI & Machine Learning Pipeline Engineering (RAG, LLM Fine-Tuning & PyTorch)",
            "slug": "custom-ai-machine-learning-pipeline-engineering",
            "description": "Production-grade generative AI, retrieval-augmented generation (RAG) with vector databases (Pinecone/Milvus), forecasting models, and Vertex AI / SageMaker deployment.",
            "skills_required": "PyTorch, Transformers, LangChain, LlamaIndex, Python, Scikit-Learn, MLOps",
            "basic_price": 65000.0, "standard_price": 120000.0, "premium_price": 195000.0,
            "delivery_days": 21, "portfolio_url": "https://huggingface.co/rahul-ai-research"
        },
        {
            "freelancer_id": 5, "category_id": 15,
            "title": "Enterprise UI/UX Design System, Design Tokens & Interactive Prototypes in Figma",
            "slug": "enterprise-uiux-design-system-figma",
            "description": "Complete multi-platform design systems with atomic component libraries, responsive grid specs, accessible WCAG 2.1 color contrast, and developer handoff specs.",
            "skills_required": "Figma, Design Systems, Mobile UI/UX, Design Tokens, Micro-Interactions, Wireframing",
            "basic_price": 35000.0, "standard_price": 65000.0, "premium_price": 110000.0,
            "delivery_days": 10, "portfolio_url": "https://dribbble.com/rahul-ux-pro"
        },
        {
            "freelancer_id": 5, "category_id": 13,
            "title": "Cloud Infrastructure, Kubernetes Orchestration & DevOps Automation (AWS / GCP)",
            "slug": "cloud-infrastructure-kubernetes-devops-automation",
            "description": "Zero-downtime multi-region cloud deployments using Terraform, Helm charts, Kubernetes (EKS/GKE), GitHub Actions CI/CD, Prometheus and Grafana monitoring.",
            "skills_required": "Kubernetes, Terraform, AWS, Google Cloud, Docker, GitHub Actions, Helm, Linux",
            "basic_price": 40000.0, "standard_price": 75000.0, "premium_price": 135000.0,
            "delivery_days": 12, "portfolio_url": "https://github.com/rahul-devops-infra"
        }
    ]

    for s in c2b_services:
        c.execute("""
            INSERT INTO services (
                freelancer_id, category_id, title, slug, description, skills_required,
                basic_price, standard_price, premium_price, delivery_days, portfolio_url,
                status, views_count, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 85, datetime('now'), datetime('now'))
        """, (
            s["freelancer_id"], s["category_id"], s["title"], s["slug"], s["description"],
            s["skills_required"], s["basic_price"], s["standard_price"], s["premium_price"],
            s["delivery_days"], s["portfolio_url"]
        ))

    conn.commit()
    conn.close()
    print("✅ AUTHENTIC ORIGINAL PRODUCTS & SERVICES SUCCESSFULLY SEEDED INTO MULTIMART DATABASE!")

if __name__ == "__main__":
    seed()
