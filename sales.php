<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all sales with cashier details
$stmt = $pdo->query("
    SELECT s.*, u.username as cashier_name 
    FROM sales s 
    LEFT JOIN users u ON s.cashier_id = u.id 
    ORDER BY s.date DESC
");
$sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - Pharmacy POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Sales</h1>
            <a href="pos.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus"></i> New Sale
            </a>
        </div>

        <!-- Sales Table -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cashier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tax</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr class="border-b">
                                <td class="px-6 py-4"><?php echo $sale['invoice_number'] ?? ''; ?></td>
                                <td class="px-6 py-4"><?php echo date('Y-m-d H:i', strtotime($sale['date'])); ?></td>
                                <td class="px-6 py-4"><?php echo $sale['customer_name'] ?? ''; ?></td>
                                <td class="px-6 py-4"><?php echo $sale['cashier_name'] ?? ''; ?></td>
                                <td class="px-6 py-4">Rs. <?php echo number_format((float)$sale['discount'], 2); ?></td>
                                <td class="px-6 py-4">Rs. <?php echo number_format((float)$sale['tax'], 2); ?></td>
                                <td class="px-6 py-4 font-semibold">Rs. <?php echo number_format((float)$sale['total_amount'], 2); ?></td>
                                <td class="px-6 py-4">
                                    <a href="sale_view.php?id=<?php echo $sale['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="sale_print.php?id=<?php echo $sale['id']; ?>" class="text-gray-500 hover:text-gray-700 mr-2">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="sale_delete.php?id=<?php echo $sale['id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this sale?')" 
                                       class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>