# 📚 Emerald Library

A cozy community library system where every book finds a reader and every reader finds a book. Built with plain PHP, MySQL, and Tailwind CSS — browse books, borrow with one click, and read entire books online, all from the comfort of home.

![Emblem](https://img.shields.io/badge/stack-PHP%208+-MySQL-blue) ![Emblem](https://img.shields.io/badge/style-Tailwind%20CSS-emerald)

## ✨ Features

- **User-facing**
  - Browse and search books by title, author, or category (full-text search)
  - View book details and read full books online in a reader view
  - Request a book to borrow — track approvals, issued books, and returns
  - Stats dashboard, featured books, categories, and member testimonials on the homepage
- **Admin panel**
  - Dashboard with library overview stats
  - Manage books (add / edit / delete, upload covers, write content)
  - Approve or reject borrow requests
  - Track issued books, returns, and late fines
  - Manage members and categories
- **Auth & security**
  - Register / login / logout with sessions and bcrypt password hashing
  - Role-based access (admin vs user) enforced on every page
  - Escaped output (`e()`) against XSS and prepared statements via mysqli

## 🛠 Tech Stack

| Layer    | Technology                              |
|----------|-----------------------------------------|
| Backend  | PHP 8+ (no frameworks, plain mysqli)    |
| Database | MySQL (utf8mb4)                         |
| Frontend | Tailwind CSS (CDN) + custom CSS         |
| Fonts    | Fraunces (display) + Nunito (body)      |

## 📂 Project Structure

```
Emerald Library/
├── index.php              # Homepage (hero, stats, categories, featured, testimonials)
├── books.php              # Browse / search books
├── login.php              # Sign in
├── register.php           # Create account
├── logout.php             # Sign out
├── install.php            # One-time installer (delete after setup!)
├── auth.php               # Session / auth helpers (current_user, is_admin, flash…)
├── config/
│   └── db.php             # DB connection + BASE_URL constants
├── database/
│   └── library.sql        # Schema + seed data
├── inc/
│   ├── header.php         # Shared header + navigation
│   ├── footer.php         # Shared footer
│   └── book_card.php      # Reusable book card component
├── admin/                 # Admin-only pages (dashboard, books, requests, issues…)
├── user/                  # User-only pages (dashboard, my-books, read)
├── assets/
│   ├── css/style.css      # Custom styles
│   └── js/app.js          # Frontend behavior
└── uploads/               # Uploaded book covers (created by installer)
```

## 🚀 Installation

### 1. Requirements

- [XAMPP](https://www.apachefriends.org/) / [WAMP](https://www.wampserver.com/) (PHP 8+, MySQL, Apache)
- A modern web browser

### 2. Setup

1. Copy the project folder into your web root:
   - XAMPP: `C:\xampp\htdocs\Emerald Library\`
   - WAMP: `C:\wamp64\www\Emerald Library\`
2. Start Apache and MySQL from the control panel.
3. Open the installer in your browser:

   ```
   http://localhost/Emerald%20Library/install.php
   ```

   This creates the `library_system` database, all tables, and seeds sample data.
4. **Security:** delete `install.php` once installation is complete.
5. Visit the homepage:

   ```
   http://localhost/Emerald%20Library/
   ```

> **Note:** Database credentials default to `localhost` / `root` / empty password (WAMP/XAMPP defaults). Change them in `config/db.php` if needed.

## 🔑 Default Accounts

| Role  | Email             | Password  |
|-------|-------------------|-----------|
| Admin | `admin@library.com` | `admin123` |
| User  | `user@library.com`  | `user123`  |

## 🗄 Database Schema

| Table            | Purpose                                  |
|------------------|------------------------------------------|
| `users`          | Members & admins (bcrypt password hashes)|
| `categories`     | Book categories                          |
| `books`          | Books, covers, descriptions, full text   |
| `borrow_requests`| Request flow (pending → approved / rejected) |
| `issues`         | Issued / returned records + fines        |

## 🖌 Design Notes

- **Palette:** emerald, gold, cream & stone — deliberately no blue / black.
- **Brand colors** are registered as Tailwind tokens (`brand-*`, `gold-*`, `cream`) in `inc/header.php`.
- **Hero image:** the homepage hero uses a library photo hotlinked from Unsplash with a gradient + overlay fallback (`index.php`).

## 🧰 Useful Commands

```bash
# Recreate the database from scratch (alternative to install.php)
mysql -u root < database/library.sql
```

## ⚠️ Troubleshooting

- **"Database connection failed"** → Make sure MySQL is running and the `library_system` database exists (run `install.php`).
- **Blank page after upload** → Check the `uploads/` folder exists and is writable.
- **Hero image missing** → It is hotlinked from Unsplash; if offline, the emerald–gold gradient fallback shows instead.

---

Crafted with ♥ for book lovers.
