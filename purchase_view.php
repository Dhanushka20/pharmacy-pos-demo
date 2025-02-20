<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: purchases.php');
    exit;
}

// Get purchase details with supplier info
$stmt = $pdo->prepare("
    SELECT p.*, s.name as supplier_name, s.contact_person, s.phone
    FROM purchases p 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    WHERE p.id = ?
");
$stmt->execute([$_GET['id']]);
$purchase = $stmt->fetch();

if (!$purchase) {
    header('Location: purchases.php');
    exit;
}

// Get purchase items with medicine info
$stmt = $pdo->prepare("
    SELECT pi.*, m.name as medicine_name, m.code 
    FROM purchase_items pi
    LEFT JOIN medicines m ON pi.medicine_id = m.id
    WHERE pi.purchase_id = ?
");
$stmt->execute([$_GET['id']]);
$items = $stmt->fetchAll();

// Get payment history
$stmt = $pdo->prepare("
    SELECT * FROM supplier_payments 
    WHERE purchase_id = ? 
    ORDER BY payment_date DESC
");
$stmt->execute([$_GET['id']]);
$payments = $stmt->fetchAll();

// Calculate total paid amount
$total_paid = array_sum(array_column($payments, 'amount'));
$balance = $purchase['total_amount'] - $total_paid;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Purchase - Pharmacy POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold">Purchase Details</h1>
            <div>
                <button onclick="window.print()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg mr-2">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="purchases.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Purchase Information</h2>
                    <p><strong>Reference No:</strong> <?php echo $purchase['reference_no']; ?></p>
                    <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($purchase['date'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span class="<?php echo $purchase['payment_status'] == 'paid' ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo ucfirst($purchase['payment_status']); ?>
                        </span>
                    </p>
                    <p><strong>Notes:</strong> <?php echo $purchase['notes']; ?></p>
                </div>
                <div>
                    <h2 class="text-xl font-semibold mb-4">Supplier Information</h2>
                    <p><strong>Name:</strong> <?php echo $purchase['supplier_name']; ?></p>
                    <p><strong>Contact Person:</strong> <?php echo $purchase['contact_person']; ?></p>
                    <p><strong>Phone:</strong> <?php echo $purchase['phone']; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Purchase Items</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr class="border-b">
                            <td class="px-6 py-4"><?php echo $item['code']; ?></td>
                            <td class="px-6 py-4"><?php echo $item['medicine_name']; ?></td>
                            <td class="px-6 py-4"><?php echo $item['quantity']; ?></td>
                            <td class="px-6 py-4">Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                            <td class="px-6 py-4">Rs. <?php echo number_format($item['total_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="font-bold">
                            <td colspan="4" class="px-6 py-4 text-right">Total Amount:</td>
                            <td class="px-6 py-4">Rs. <?php echo number_format($purchase['total_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($payments)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Payment History</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr class="border-b">
                            <td class="px-6 py-4"><?php echo date('Y-m-d', strtotime($payment['payment_date'])); ?></td>
                            <td class="px-6 py-4">Rs. <?php echo number_format($payment['amount'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="font-bold">
                            <td class="px-6 py-4 text-right">Total Paid:</td>
                            <td class="px-6 py-4">Rs. <?php echo number_format($total_paid, 2); ?></td>
                        </tr>
                        <tr class="font-bold">
                            <td class="px-6 py-4 text-right">Balance:</td>
                            <td class="px-6 py-4">Rs. <?php echo number_format($balance, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>