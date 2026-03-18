# FreshMart — Online Supermarket System

## Student Information

| Field               | Details                                       |
|---------------------|-----------------------------------------------|
| **Student Name**    | Shamran Nsubuga                               |
| **Registration No** | 25DSIT0139                   |
| **Project Title**   | FreshMart — Online Supermarket System         |

---

## Project Description

FreshMart is a full-stack web-based supermarket management system built with PHP and MySQL. It allows customers to browse products, add items to a cart, and place orders online. It also includes a complete admin panel for managing products, orders, inventory, staff, promotions, and generating sales reports.

---

## Technologies Used

| Technology   | Purpose                          |
|--------------|----------------------------------|
| PHP          | Server-side scripting            |
| MySQL        | Database management              |
| HTML5        | Page structure and markup        |
| CSS3         | Styling and responsive layout    |
| JavaScript   | AJAX cart, UI interactivity      |
| XAMPP        | Local development server         |
| phpMyAdmin   | Database administration          |

---

## System Features

### Customer Side
- Homepage with product grid, categories, and deals
- Product search and category filtering
- Add to cart with AJAX (no page reload)
- Live countdown timer for limited-time deals
- Customer registration and login with validation
- Customer dashboard with order history
- Shopping cart with quantity controls
- Checkout and order placement

### Admin Panel
- Dashboard with live statistics from the database
- Order management (view and update status)
- Product management (add, edit, delete)
- Inventory management with stock level updates
- Customer management (view and suspend accounts)
- Staff management (add, edit, remove)
- Promotions and discount codes
- Reports with revenue charts
- Site settings and password change

---

## Project Folder Structure

```
freshmart/
├── index.php
├── freshmart.sql
├── README.md
├── includes/
│   ├── config.php
│   ├── header.php
│   └── footer.php
├── customer/
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   ├── orders.php
│   ├── cart.php
│   ├── cart_add.php
│   ├── checkout.php
│   ├── order_success.php
│   └── logout.php
├── admin/
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── products.php
│   ├── orders.php
│   ├── inventory.php
│   ├── customers.php
│   ├── staff.php
│   ├── promotions.php
│   ├── reports.php
│   ├── settings.php
│   └── includes/
│       ├── sidebar.php
│       └── footer.php
└── assets/
    ├── css/
    │   └── style.css
    └── js/
        └── main.js
```

---

## Steps to Run the Project

### Requirements
- XAMPP (Apache + MySQL)
- A web browser

### Step 1 — Copy Project Files
Copy the entire `freshmart` folder into your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\freshmart\
```

### Step 2 — Start XAMPP
1. Open the **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**

### Step 3 — Import the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** on the left sidebar
3. Create a database named: `freshmart`
4. Click on the `freshmart` database
5. Click the **Import** tab at the top
6. Click **Choose File** and select `freshmart.sql` from the project folder
7. Click **Go** to import

### Step 4 — Open the Application

| Page           | URL                                             |
|----------------|-------------------------------------------------|
| Store Homepage | `http://localhost/freshmart/`                   |
| Admin Login    | `http://localhost/freshmart/admin/login.php`    |
| Customer Login | `http://localhost/freshmart/customer/login.php` |

---

## Database Import Instructions

The database file is located at:
```
freshmart/freshmart.sql
```

Import it using phpMyAdmin as described in Step 3 above, or via the MySQL command line:
```bash
mysql -u root -p freshmart < freshmart.sql
```

---

## Login Credentials

### Admin Panel
| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

### Demo Customer Account
| Field    | Value             |
|----------|-------------------|
| Email    | `sarah@email.com` |
| Password | `password`        |

---

## Troubleshooting

| Problem                      | Solution                                                        |
|------------------------------|-----------------------------------------------------------------|
| "Database Connection Failed" | Ensure MySQL is running in XAMPP and freshmart.sql is imported  |
| Blank page or 404 error      | Ensure Apache is running and folder is inside htdocs            |
| Images/CSS not loading       | Check that folder path is htdocs/freshmart/                     |
| "Table not found" errors     | Re-import freshmart.sql in phpMyAdmin                           |
_Submitted for Web Development Assignment — 2025_