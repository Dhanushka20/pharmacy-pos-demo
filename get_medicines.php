<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT id, code, name, selling_price, stock_quantity 
        FROM medicines 
        WHERE stock_quantity > 0
        ORDER BY name ASC
    ");
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($medicines);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>