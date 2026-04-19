# 🎬 CineReview

A movie review web application built with PHP, MySQL, and Bootstrap 5.

**Live site:** http://cinereview.rf.gd  
**Student:** Thales Augusto Scaramelli Junqueira (15578)  
**Subject:** INT1059 Advanced Web — Assessment 3

---

## Features

- Browse a curated movie catalogue with poster, rating, and genre
- Search movies by title and filter by genre
- User registration and login system
- Write, edit, and delete reviews with a 1–5 star rating
- Add and remove movies from a personal favourites list
- Account page to manage reviews, favourites, and profile settings
- Admin panel to add, edit, and delete movies

---

## Requirements

- PHP 8.0+
- MySQL / MariaDB
- WAMP, XAMPP, or any Apache/PHP/MySQL stack

---

## Local Setup

**1. Clone the repository**
```bash
git clone https://github.com/ThalesSCaramelli/CineReview.git
```

**2. Copy to your server root**
```
C:\wamp64\www\cinereview\
```

**3. Import the database**

Open phpMyAdmin and import `CineReview.sql`. This creates the `cinereview` database with all tables and sample data.

**4. Check database credentials**

Open `includes/config.php` and confirm the settings match your environment:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cinereview');
define('DB_USER', 'root');
define('DB_PASS', '');  // WAMP default is empty
```

**5. Open in browser**
```
http://localhost/cinereview/
```

---

## Admin Panel

The admin panel allows you to manage the movie catalogue without touching the database directly.

**URL:** `http://localhost/cinereview/admin/movies.php`  
**Password:** `cinereview2024`

> Change the password in `admin/movies.php` before deploying to production.

---

## Test Account

| Email | Password |
|---|---|
| maria@email.com | password |

---

## Project Structure

```
cinereview/
├── index.php              # Home page — movie grid, search, filter
├── movie.php              # Movie detail, review form, user reviews
├── login.php              # User login
├── register.php           # User registration
├── account.php            # Account — reviews, favourites, settings
├── edit_review.php        # Edit a submitted review
├── logout.php             # Session destroy
├── CineReview.sql         # Database export
├── includes/
│   ├── config.php         # DB connection + session
│   ├── auth.php           # Login, register, session helpers
│   ├── header.php         # Shared navbar and HTML head
│   └── footer.php         # Shared footer and Bootstrap JS
├── admin/
│   └── movies.php         # Admin panel — manage movies
└── assets/
    └── css/
        └── style.css      # Dark cinema theme
```

---

## Database Tables

| Table | Description |
|---|---|
| `users` | Registered user accounts |
| `movies` | Movie catalogue |
| `reviews` | User reviews with star rating (1–5) |
| `favorites` | User favourites list |
