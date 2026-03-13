<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: movies.php");
} else {
    header("Location: login.php");
}
exit();
?>
