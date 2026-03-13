<?php
/**
 * Database Configuration
 * This file establishes the connection to the MySQL database.
 */

$host = 'localhost';
$dbname = 'movie_watchlist';
$username = 'root';
$password = ''; // Default XAMPP password is empty

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for better compatibility
$conn->set_charset("utf8mb4");
?>
