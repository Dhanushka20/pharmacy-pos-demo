<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (isset($_GET['file'])) {
    $backup_dir = 'backups/';
    $file = $backup_dir . basename($_GET['file']);
    
    if (file_exists($file) && unlink($file)) {
        $_SESSION['success'] = "Backup deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting backup";
    }
}

header('Location: backup.php');
exit;
?>