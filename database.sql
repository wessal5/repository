-- Create Database
CREATE DATABASE IF NOT EXISTS movie_watchlist;
USE movie_watchlist;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create movies table
CREATE TABLE IF NOT EXISTS movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    genre VARCHAR(100),
    release_year INT,
    poster_url VARCHAR(255)
);

-- Create watchlist table
CREATE TABLE IF NOT EXISTS watchlist (
    watchlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    status ENUM('planned', 'watched') DEFAULT 'planned',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
);

-- Insert sample movie data
INSERT INTO movies (title, description, genre, release_year, poster_url) VALUES
('Inception', 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.', 'Sci-Fi', 2010, 'https://images.unsplash.com/photo-1626814026160-2237a95fc5a0?q=80&w=400'),
('The Shawshank Redemption', 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.', 'Drama', 1994, 'https://images.unsplash.com/photo-1534447677768-be436bb09401?q=80&w=400'),
('The Dark Knight', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.', 'Action', 2008, 'https://images.unsplash.com/photo-1509248961158-e54f6934749c?q=80&w=400'),
('Pulp Fiction', 'The lives of two mob hitmen, a boxer, a gangster and his wife, and a pair of diner bandits intertwine in four tales of violence and redemption.', 'Crime', 1994, 'https://images.unsplash.com/photo-1594909122845-11baa439b7bf?q=80&w=400'),
('Interstellar', 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity''s survival.', 'Sci-Fi', 2014, 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?q=80&w=400'),
('The Matrix', 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.', 'Sci-Fi', 1999, 'https://images.unsplash.com/photo-1626814026160-2237a95fc5a0?q=80&w=400');
