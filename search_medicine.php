<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$search = $_GET['search'] ?? '';

if (empty($search)) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, code, name, selling_price, stock_quantity 
        FROM medicines 
        WHERE (name LIKE ? OR code LIKE ?) 
        AND stock_quantity > 0 
        ORDER BY name 
        LIMIT 10
    ");
    
    $search = "%$search%";
    $stmt->execute([$search, $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($results);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([]);
}
?>