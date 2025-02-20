<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: medicines.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM medicines WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    
    header('Location: medicines.php');
    exit;
} catch (PDOException $e) {
    echo "Error deleting medicine: " . $e->getMessage();
}
?>