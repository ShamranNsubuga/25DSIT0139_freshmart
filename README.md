# FreshMart — Supermarket Website
## XAMPP Setup Guide

---

### STEP 1 — Copy files to XAMPP
Copy the entire `freshmart` folder to:
```
C:\xampp\htdocs\freshmart\
```

Your folder structure should look like:
```
C:\xampp\htdocs\freshmart\
    index.php
    freshmart.sql
    README.md
    includes/
        config.php
        header.php
        footer.php
    customer/
        login.php
        register.php
        dashboard.php
        orders.php
        cart.php
        cart_add.php
        logout.php
    admin/
        login.php
        logout.php
        dashboard.php
        products.php
        orders.php
        inventory.php
        customers.php
        staff.php
        promotions.php
        reports.php
        settings.php
        includes/
            sidebar.php
            footer.php
    assets/
        css/
            style.css
        js/
            main.js
```

---

### STEP 2 — Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache**
3. Start **MySQL**

---

### STEP 3 — Import the Database
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **"New"** on the left sidebar
3. Create a database named: `freshmart`
4. Click on the `freshmart` database
5. Click the **Import** tab at the top
6. Click **Choose File** → select `freshmart.sql` from your freshmart folder
7. Click **Go** to import

---

### STEP 4 — Open the Site
- **Store:** `http://localhost/freshmart/`
- **Admin:** `http://localhost/freshmart/admin/login.php`
- **Customer Login:** `http://localhost/freshmart/customer/login.php`

---

### LOGIN CREDENTIALS

**Admin Panel:**
- Username: `admin`
- Password: `admin123`

**Demo Customer:**
- Email: `sarah@email.com`
- Password: `password`

---

### TROUBLESHOOTING

**"Database Connection Failed"**
→ Make sure MySQL is running in XAMPP
→ Make sure you imported `freshmart.sql` in phpMyAdmin

**Blank page or 404**
→ Make sure Apache is running
→ Check the folder is at `C:\xampp\htdocs\freshmart\`

**Images/CSS not loading**
→ The site uses Google Fonts (requires internet connection for fonts)
→ All CSS and JS are local files — check paths

**"Table not found" errors**
→ Re-import `freshmart.sql` in phpMyAdmin

---

### FEATURES INCLUDED

**Storefront:**
- Homepage with product grid, categories, deals
- Search by name/category
- Filter by category, deals, new arrivals
- Add to cart (AJAX, no page reload)
- Live countdown timer for deals
- Customer register & login with validation
- Customer dashboard with order history
- Shopping cart with qty controls

**Admin Panel:**
- Dashboard with live stats from DB
- Order management (view + update status)
- Product management (add/edit/delete)
- Inventory with stock level updates
- Customer management (view + suspend)
- Staff management (add/edit/remove)
- Promotions & discount codes
- Reports with revenue chart
- Settings & password change

---

### NEXT STEPS (optional upgrades)
- Add checkout with order placement
- Integrate MTN/Airtel Mobile Money API
- Add product images (file upload)
- Add email notifications (PHPMailer)
- Add customer wishlist
- Add product reviews & ratings
