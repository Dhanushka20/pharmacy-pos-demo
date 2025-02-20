<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: sales.php');
    exit;
}

// Get sale details
$stmt = $pdo->prepare("
    SELECT s.*, u.username as cashier_name 
    FROM sales s 
    LEFT JOIN users u ON s.cashier_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$_GET['id']]);
$sale = $stmt->fetch();

if (!$sale) {
    header('Location: sales.php');
    exit;
}

// Get sale items
$stmt = $pdo->prepare("
    SELECT si.*, m.name as medicine_name, m.dose, m.dose_unit 
    FROM sale_items si 
    LEFT JOIN medicines m ON si.medicine_id = m.id 
    WHERE si.sale_id = ?
");
$stmt->execute([$_GET['id']]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sale - <?php echo $sale['invoice_number']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header with Actions -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Sale Details</h1>
                <div class="space-x-2">
                    <a href="sale_print.php?id=<?php echo $sale['id']; ?>" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-print mr-2"></i>Print
                    </a>
                    <a href="sale_delete.php?id=<?php echo $sale['id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this sale?')"
                       class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-trash mr-2"></i>Delete
                    </a>
                </div>
            </div>

            <!-- Sale Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="mb-2"><strong>Invoice Number:</strong> <?php echo $sale['invoice_number']; ?></p>
                        <p class="mb-2"><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($sale['date'])); ?></p>
                        <p class="mb-2"><strong>Customer:</strong> <?php echo $sale['customer_name'] ?: 'Walk-in Customer'; ?></p>
                    </div>
                    <div>
                        <p class="mb-2"><strong>Cashier:</strong> <?php echo $sale['cashier_name']; ?></p>
                        <p class="mb-2"><strong>Payment Method:</strong> <?php echo ucfirst($sale['payment_method']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">Sale Items</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium"><?php echo $item['medicine_name']; ?></div>
                                    <?php if ($item['dose'] && $item['dose_unit']): ?>
                                        <div class="text-sm text-gray-500"><?php echo $item['dose'] . ' ' . $item['dose_unit']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                                <td class="px-6 py-4"><?php echo $item['quantity']; ?></td>
                                <td class="px-6 py-4">Rs. <?php echo number_format($item['total_price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td colspan="3" class="px-6 py-3 text-right font-semibold">Total:</td>
                                <td class="px-6 py-3 font-semibold">Rs. <?php echo number_format($sale['total_amount'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Back Button -->
            <div class="flex justify-end">
                <a href="sales.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Sales
                </a>
            </div>
        </div>
    </div>
</body>
</html>