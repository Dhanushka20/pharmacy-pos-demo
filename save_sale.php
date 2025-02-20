<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Start transaction
    $pdo->beginTransaction();

    // Insert sale
    $stmt = $pdo->prepare("
        INSERT INTO sales (invoice_number, customer_name, cashier_id, subtotal, 
                          discount, total_amount, payment_method, date)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $data['invoice_number'],
        $data['customer_name'],
        $_SESSION['user_id'],
        $data['subtotal'],
        $data['discount'],
        $data['total_amount'],
        $data['payment_method']
    ]);

    $sale_id = $pdo->lastInsertId();

    // Insert sale items and update stock
    foreach ($data['items'] as $item) {
        // Insert sale item
        $stmt = $pdo->prepare("
            INSERT INTO sale_items (sale_id, medicine_id, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $sale_id,
            $item['id'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity']
        ]);

        // Update stock
        $stmt = $pdo->prepare("
            UPDATE medicines 
            SET stock_quantity = stock_quantity - ? 
            WHERE id = ?
        ");

        $stmt->execute([$item['quantity'], $item['id']]);
    }

    // Commit transaction
    $pdo->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'sale_id' => $sale_id]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>