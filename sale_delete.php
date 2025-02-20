<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: sales.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Get sale items to restore stock
    $stmt = $pdo->prepare("SELECT medicine_id, quantity FROM sale_items WHERE sale_id = ?");
    $stmt->execute([$_GET['id']]);
    $items = $stmt->fetchAll();

    // Restore stock quantities
    foreach ($items as $item) {
        $stmt = $pdo->prepare("UPDATE medicines SET stock_quantity = stock_quantity + ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['medicine_id']]);
    }

    // Delete sale items
    $stmt = $pdo->prepare("DELETE FROM sale_items WHERE sale_id = ?");
    $stmt->execute([$_GET['id']]);

    // Delete sale
    $stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    $pdo->commit();
    
    $_SESSION['success'] = "Sale deleted successfully";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting sale: " . $e->getMessage();
}

header('Location: sales.php');
exit;
?>