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
    <title>Receipt - <?php echo $sale['invoice_number']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: 80mm 297mm;
                margin: 0;
            }
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        .receipt {
            width: 80mm;
            padding: 10mm;
            margin: auto;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <!-- Action Buttons -->
        <div class="flex justify-center gap-4 mb-4 no-print">
            <a href="sales.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
            <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-print mr-2"></i>Print Receipt
            </button>
        </div>

        <!-- Receipt -->
        <div class="bg-white shadow-lg receipt">
            <!-- Header -->
            <div class="text-center mb-4">
                <h1 class="text-xl font-bold">Pharmacy POS</h1>
                <p class="text-sm">123 Main Street, City</p>
                <p class="text-sm">Tel: 011-1234567</p>
                <div class="border-b border-gray-300 my-2"></div>
            </div>

            <!-- Invoice Details -->
            <div class="text-sm mb-4">
                <p><strong>Invoice:</strong> <?php echo $sale['invoice_number']; ?></p>
                <p><strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($sale['date'])); ?></p>
                <p><strong>Cashier:</strong> <?php echo $sale['cashier_name']; ?></p>
                <?php if ($sale['customer_name']): ?>
                    <p><strong>Customer:</strong> <?php echo $sale['customer_name']; ?></p>
                <?php endif; ?>
            </div>

            <!-- Items -->
            <div class="border-b border-gray-300 my-2"></div>
            <table class="w-full text-sm mb-4">
                <thead>
                    <tr class="border-b border-gray-300">
                        <th class="text-left">Item</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="py-1">
                            <?php echo $item['medicine_name']; ?>
                            <?php if ($item['dose'] && $item['dose_unit']): ?>
                                <br><span class="text-xs"><?php echo $item['dose'] . ' ' . $item['dose_unit']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                        <td class="text-right"><?php echo $item['quantity']; ?></td>
                        <td class="text-right">Rs. <?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Total -->
            <div class="border-t border-gray-300 pt-2">
                <div class="flex justify-between font-bold">
                    <span>Total:</span>
                    <span>Rs. <?php echo number_format($sale['total_amount'], 2); ?></span>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm mt-4">
                <p>Thank you for your business!</p>
                <p class="text-xs mt-2">Software by: Dhanushka SmartTech</p>
            </div>
        </div>
    </div>
</body>
</html>