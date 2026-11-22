# COMPREHENSIVE LOCAL INSTALLATION GUIDE
**Project Title:** MultiMart Multi-Model E-Commerce Marketplace  
**Target Environment:** XAMPP (Apache + MySQL / MariaDB) & Standalone PHP CLI  

---

## Method A: Standard Installation via XAMPP (Windows / macOS / Linux)

### Step 1: Download & Install XAMPP
- If not already installed, download XAMPP with PHP 8.0+ from the official Apache Friends website:
  [https://www.apachefriends.org/](https://www.apachefriends.org/)
- Complete the standard installation including Apache and MySQL components.

### Step 2: Deploy MultiMart into Web Root (`htdocs`)
Copy the `MultiMart` project directory into your XAMPP `htdocs` directory:
- **Windows:** `C:\xampp\htdocs\MultiMart`
- **macOS:** `/Applications/XAMPP/xamppfiles/htdocs/MultiMart`
- **Linux:** `/opt/lampp/htdocs/MultiMart`

*(Alternatively on macOS/Linux, create a symbolic link):*
```bash
ln -s /Users/vikramaditya/MultiMart /Applications/XAMPP/xamppfiles/htdocs/MultiMart
```

### Step 3: Launch Apache & MySQL
1. Open the **XAMPP Control Panel**.
2. Start the **Apache** web server module.
3. Start the **MySQL** database server module.
4. Ensure both modules display green operational status.

### Step 4: Import Database Schema via phpMyAdmin
1. Open your web browser and navigate to:  
   `http://localhost/phpmyadmin/`
2. Click **New** in the left sidebar to create a new database.
3. Name the database **`multimart_db`** with collation `utf8mb4_unicode_ci` and click **Create**.
4. With `multimart_db` selected, click the **Import** tab on the top menu.
5. Click **Choose File** and select:  
   `MultiMart/database/multimart_database.sql`
6. Scroll to the bottom and click **Import** (or **Go**). All 30 tables and seed records will be created.

### Step 5: Verify Permissions for Uploads
Ensure write permissions are available for the media upload directories:
```bash
chmod -R 775 /Users/vikramaditya/MultiMart/public/uploads
```

### Step 6: Launch MultiMart
Navigate to:  
👉 **`http://localhost/MultiMart/`**

---

## Method B: Instant Standalone Test (Zero-Configuration PHP Runner)
If XAMPP is not installed or MySQL is temporarily stopped, MultiMart includes an automatic embedded SQLite3 database engine (`database/multimart.sqlite`) pre-loaded with all 30 tables and demo seed records.

Run the following command from the project directory:
```bash
cd /Users/vikramaditya/MultiMart
php -S localhost:8000
```
Then open your browser to:  
👉 **`http://localhost:8000/`**
