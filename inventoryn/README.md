# MyStore – Inventory Management System
## Setup Guide (XAMPP / any PHP+MySQL server)

---

## Step 1: Database Setup

1. Open **phpMyAdmin** → go to `http://localhost/phpmyadmin`
2. Click **"Import"** tab
3. Choose file: **`database.sql`** → Click **Go**
4. This creates:
   - Database: `inventry_management_db`
   - Tables: `users`, `admins`, `products`, `orders`
   - Default admin account (username: `admin`, password: `admin123`)

---

## Step 2: Configure Database Connection

Open **`config.php`** and update these lines:

```php
define('DB_HOST', 'localhost');   // usually 'localhost'
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password (empty by default in XAMPP)
define('DB_NAME', 'inventry_management_db'); // keep this as-is
```

---

## Step 3: Copy Files to Server

Copy all PHP files to your web server folder:
- **XAMPP**: `C:/xampp/htdocs/mystore/`
- **WAMP**: `C:/wamp64/www/mystore/`
- **Linux**: `/var/www/html/mystore/`

---

## Step 4: Open in Browser

```
http://localhost/mystore/home.php         ← Store homepage
http://localhost/mystore/admin_login.php  ← Admin panel
```

---

## File Structure

```
mystore/
├── config.php              ← Database connection (edit this!)
├── database.sql            ← Run this in phpMyAdmin once
├── home.php                ← Public store (products from DB)
├── login.php               ← User login
├── signup.php              ← User registration
├── logout.php              ← User logout
├── order.php               ← Place order (login required)
├── admin_login.php         ← Admin login page
├── admin.php               ← Admin dashboard (products + orders + users)
├── admin_logout.php        ← Admin logout
├── admin_update_order.php  ← AJAX order status update
├── about.php               ← About page (keep from old project)
└── contact.php             ← Contact page (keep from old project)
```

---

## How It Works

### User Flow
1. User visits `home.php` → sees products from database
2. Clicks "Order Now" → if not logged in, redirected to `login.php`
3. After login → fills order form → order saved to `orders` table
4. Product stock automatically reduced

### Admin Flow
1. Admin visits `admin_login.php` → logs in with admin credentials
2. Dashboard shows:
   - **Inventory tab**: Add/Edit/Delete products (with image upload)
   - **Orders tab**: View all orders, update status (pending/confirmed/shipped/delivered/cancelled)
   - **Users tab**: View all registered users

### Home Page
- Products are fetched from MySQL database
- Search and category filter work instantly (client-side)
- Stock badges update based on actual database quantities

---

## Default Admin Credentials
- Username: `admin`
- Password: `admin123`
- **⚠️ Change this password after first login!**

To change password, run this in phpMyAdmin:
```sql
UPDATE admins SET password = '<new_hash>' WHERE username = 'admin';
```
Or add a "Change Password" feature in admin panel.

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| "Database Connection Failed" | Check `config.php` credentials, make sure MySQL is running |
| Blank page | Check PHP version (needs PHP 7.4+), enable `display_errors` |
| Images not showing | Image stored as base64 in DB – normal, no separate folder needed |
| Can't login | Run `database.sql` again, check `admins` table has a row |
