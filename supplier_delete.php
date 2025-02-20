<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: suppliers.php');
    exit;
}

try {
    // Check if there are any related purchases
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE supplier_id = ?");
    $stmt->execute([$_GET['id']]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['error'] = "Cannot delete supplier because they have related purchases";
        header('Location: suppliers.php');
        exit;
    }

    // Delete supplier
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    
    $_SESSION['success'] = "Supplier deleted successfully";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting supplier: " . $e->getMessage();
}

header('Location: suppliers.php');
exit;
?>