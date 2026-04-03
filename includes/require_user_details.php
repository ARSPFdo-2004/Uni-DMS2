<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_prefs_set'])) {
    header("Location: index.php?require_prefs=1");
    exit;
}
?>