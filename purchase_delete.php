<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: purchases.php');
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Check if purchase exists and is pending
    $stmt = $pdo->prepare("
        SELECT * FROM purchases 
        WHERE id = ? AND payment_status = 'pending'
    ");
    $stmt->execute([$_GET['id']]);
    $purchase = $stmt->fetch();

    if (!$purchase) {
        throw new Exception("Purchase not found or cannot be deleted");
    }

    // Get purchase items to revert stock
    $stmt = $pdo->prepare("
        SELECT medicine_id, quantity 
        FROM purchase_items 
        WHERE purchase_id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $items = $stmt->fetchAll();

    // Revert stock quantities
    foreach ($items as $item) {
        $stmt = $pdo->prepare("
            UPDATE medicines 
            SET stock_quantity = stock_quantity - ? 
            WHERE id = ?
        ");
        $stmt->execute([$item['quantity'], $item['medicine_id']]);
    }

    // Delete purchase items
    $stmt = $pdo->prepare("DELETE FROM purchase_items WHERE purchase_id = ?");
    $stmt->execute([$_GET['id']]);

    // Delete payments if any
    $stmt = $pdo->prepare("DELETE FROM supplier_payments WHERE purchase_id = ?");
    $stmt->execute([$_GET['id']]);

    // Delete purchase
    $stmt = $pdo->prepare("DELETE FROM purchases WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    // Commit transaction
    $pdo->commit();
    
    $_SESSION['success'] = "Purchase deleted successfully";

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting purchase: " . $e->getMessage();
}

header('Location: purchases.php');
exit;
?>