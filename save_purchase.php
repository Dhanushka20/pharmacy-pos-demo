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

    // Insert purchase
    $stmt = $pdo->prepare("
        INSERT INTO purchases (reference_no, supplier_id, date, total_amount, 
                             payment_status, notes)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['reference_no'],
        $data['supplier_id'],
        $data['date'],
        $data['total_amount'],
        $data['payment_status'],
        $data['notes']
    ]);

    $purchase_id = $pdo->lastInsertId();

    // Insert purchase items and update stock
    foreach ($data['items'] as $item) {
        // Insert purchase item
        $stmt = $pdo->prepare("
            INSERT INTO purchase_items (purchase_id, medicine_id, quantity, 
                                      unit_price, total_price)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $purchase_id,
            $item['id'],
            $item['quantity'],
            $item['unit_price'],
            $item['unit_price'] * $item['quantity']
        ]);

        // Update medicine stock and unit price
        $stmt = $pdo->prepare("
            UPDATE medicines 
            SET stock_quantity = stock_quantity + ?,
                unit_price = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $item['quantity'],
            $item['unit_price'],
            $item['id']
        ]);

        // Update medicine expiry date if provided
        if (isset($item['expiry_date']) && !empty($item['expiry_date'])) {
            $stmt = $pdo->prepare("
                UPDATE medicines 
                SET expiry_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$item['expiry_date'], $item['id']]);
        }
    }

    // If payment status is 'paid', create a payment record
    if ($data['payment_status'] === 'paid') {
        $stmt = $pdo->prepare("
            INSERT INTO supplier_payments (purchase_id, amount, payment_date)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $purchase_id,
            $data['total_amount'],
            $data['date']
        ]);
    }

    // Commit transaction
    $pdo->commit();

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'purchase_id' => $purchase_id,
        'message' => 'Purchase saved successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving purchase: ' . $e->getMessage()
    ]);
}
?>