<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['movie_id'])) {
    $user_id = $_SESSION['user_id'];
    $movie_id = intval($_POST['movie_id']);

    // Check if already in watchlist
    $check_stmt = $conn->prepare("SELECT watchlist_id FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $check_stmt->bind_param("ii", $user_id, $movie_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO watchlist (user_id, movie_id, status) VALUES (?, ?, 'planned')");
        $stmt->bind_param("ii", $user_id, $movie_id);
        $stmt->execute();
        $stmt->close();
    }

    $check_stmt->close();
}

header("Location: watchlist.php");
exit();
?>
