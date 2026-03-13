# Movie Watchlist Web Application

A simple and complete web application to manage your personal movie watchlist.

## Features
- User Registration and Login
- Browse available movies
- Add movies to your watchlist
- Mark movies as watched or planned
- Remove movies from watchlist

## Technology Stack
- **Backend:** PHP (Procedural)
- **Database:** MySQL (using mysqli)
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5

## Setup Instructions (XAMPP)

1. **Place Project in XAMPP:**
   - Copy the `movie-watchlist` folder into your XAMPP's `htdocs` directory.
   - Usually: `C:\xampp\htdocs\movie-watchlist`

2. **Import Database:**
   - Open XAMPP Control Panel and start **Apache** and **MySQL**.
   - Go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
   - Create a new database named `movie_watchlist`.
   - Select the `movie_watchlist` database.
   - Click on the **Import** tab.
   - Click **Choose File** and select the `database.sql` file located in the project root.
   - Click **Go** to run the import.

3. **Run the Project:**
   - Open your browser and go to: [http://localhost/movie-watchlist](http://localhost/movie-watchlist)

## Database Configuration
If your MySQL root password is not empty, update the `config/database.php` file:
```php
$password = 'your_password';
```

## Folder Structure
- `config/`: Database connection settings
- `includes/`: Reusable header and footer files
- `assets/`: CSS, JS, and image files
- Root files: Main application pages (login, register, movies, watchlist, etc.)
