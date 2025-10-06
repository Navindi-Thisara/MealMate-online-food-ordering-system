# MealMate - Online Food Ordering System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

MealMate is a web-based online food ordering system designed to help restaurants manage menus, orders, and users efficiently. It allows customers to browse menus, add items to a cart, place orders, and supports admin functionalities like managing users, menu items, and orders.

---

## Table of Contents
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Usage](#usage)
- [Screenshots](#screenshots)
- [Contributing](#contributing)
- [License](#license)

---

## Features

**Customer Features**
- Browse and search food menu.
- Add, edit, and remove items in cart.
- Place and view orders.
- Responsive UI for mobile and desktop.

**Admin Features**
- Manage users (add, edit, delete).
- Manage food menu items (add, edit, delete).
- View and manage orders.
- Role-based access control for security.

---

## Technologies Used
- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **Backend:** PHP
- **Database:** MySQL
- **Other:** Composer for dependency management, AJAX for dynamic updates

---

## Project Structure

MealMate-online-food-ordering-system/

├── assets/

├── cart/

├── food_management/

├── includes/

├── orders/

├── users/

├── vendor/

├── index.php

├── contact.php

├── index.css

├── index.js

├── theme-toggle.js

├── db.sql

└── README.md

---

## Installation

1. **Clone the repository**

git clone https://github.com/Navindi-Thisara/MealMate-online-food-ordering-system.git

2. **Setup Database**

Import db.sql into your MySQL database.

Update database credentials in includes/db_connect.php.

3. **Run the project**

Use XAMPP or any local server.

Place the project in the htdocs folder.

Open http://localhost/MealMate-online-food-ordering-system/index.php in your browser.

## Usage

- Admin login credentials can be set in the users table.

- Customers can register via register.php.

- Admins can manage menus, orders, and users via the dashboard.

- Dark mode toggle available via theme-toggle.js.
