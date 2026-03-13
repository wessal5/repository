<?php
require_once 'config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['watchlist_id'])) {
    $user_id = $_SESSION['user_id'];
    $watchlist_id = intval($_POST['watchlist_id']);

    $stmt = $conn->prepare("DELETE FROM watchlist WHERE watchlist_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $watchlist_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: watchlist.php");
exit();
?>
