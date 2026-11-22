# SOFTWARE REQUIREMENTS SPECIFICATION
**Project Title:** MultiMart Multi-Model E-Commerce Platform  

---

## 1. Backend Server-Side Runtime
- **PHP Interpreter:** PHP version 8.0, 8.1, 8.2, or 8.3.
- **Required Core Extensions:**
  - `pdo` (PHP Data Objects runtime)
  - `pdo_mysql` (MySQL PDO database driver)
  - `pdo_sqlite` (SQLite3 PDO fallback driver)
  - `openssl` (Cryptographic random bytes generation)
  - `session` (Secure HTTP session handler)
  - `json` (RESTful API serialization)

---

## 2. Database Management System
- **Primary Database Server:** MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.4+.
- **Database Administration Interface:** phpMyAdmin 5.0+.
- **Secondary Embedded Engine:** SQLite 3.30+ (pre-configured for zero-dependency execution).

---

## 3. Client-Side Libraries & Presentation
- **Markup:** HTML5 (W3C compliant semantic markup).
- **Styling:** CSS3, Bootstrap 5.3.2 CSS bundle via CDN with local fallbacks.
- **Iconography:** Bootstrap Icons 1.11.3 & Font Awesome 6.5.1 CDN.
- **Visual Analytics:** Chart.js 4.4.1 for administrative and merchant metrics.
- **Client Scripting:** Modern ECMAScript 6+ Vanilla JavaScript with Asynchronous Fetch API.

---

## 4. Software Development Tools
- **Code Editor:** Visual Studio Code (VS Code) with PHP Intelephense extensions.
- **Local Server Manager:** XAMPP Control Panel 3.3.0.
- **Database Query Testing:** phpMyAdmin, SQLite3 CLI, Python test runner.
