<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: purchases.php');
    exit;
}

// Get purchase details
$stmt = $pdo->prepare("
    SELECT p.*, s.name as supplier_name, s.contact_person, s.phone, s.address
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

// Get purchase items
$stmt = $pdo->prepare("
    SELECT pi.*, m.name as medicine_name, m.code 
    FROM purchase_items pi
    LEFT JOIN medicines m ON pi.medicine_id = m.id
    WHERE pi.purchase_id = ?
");
$stmt->execute([$_GET['id']]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - <?php echo $purchase['reference_no']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .print-area {
                padding: 20px;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto my-8 bg-white p-8 print-area">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold">PURCHASE ORDER</h1>
            <h2 class="text-xl font-bold">Pharmacy POS</h2>
            <p>123 Main Street, City</p>
            <p>Tel: 011-1234567</p>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <h3 class="font-bold mb-2">Supplier Details:</h3>
                <p><strong>Name:</strong> <?php echo $purchase['supplier_name']; ?></p>
                <p><strong>Contact Person:</strong> <?php echo $purchase['contact_person']; ?></p>
                <p><strong>Phone:</strong> <?php echo $purchase['phone']; ?></p>
                <p><strong>Address:</strong> <?php echo $purchase['address']; ?></p>
            </div>
            <div>
                <h3 class="font-bold mb-2">Purchase Details:</h3>
                <p><strong>PO Number:</strong> <?php echo $purchase['reference_no']; ?></p>
                <p><strong>Date:</strong> <?php echo date('Y-m-d', strtotime($purchase['date'])); ?></p>
                <p><strong>Payment Status:</strong> 
                    <span class="<?php echo $purchase['payment_status'] == 'paid' ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo ucfirst($purchase['payment_status']); ?>
                    </span>
                </p>
            </div>
        </div>

        <table class="w-full mb-8">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="text-left py-2">Code</th>
                    <th class="text-left py-2">Item Description</th>
                    <th class="text-right py-2">Unit Price</th>
                    <th class="text-right py-2">Quantity</th>
                    <th class="text-right py-2">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr class="border-b border-gray-200">
                    <td class="py-2"><?php echo $item['code']; ?></td>
                    <td class="py-2"><?php echo $item['medicine_name']; ?></td>
                    <td class="text-right py-2">Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                    <td class="text-right py-2"><?php echo $item['quantity']; ?></td>
                    <td class="text-right py-2">Rs. <?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="flex justify-end mb-8">
            <div class="w-1/2">
                <div class="flex justify-between py-2 text-xl font-bold border-t-2 border-gray-300">
                    <span>Total Amount:</span>
                    <span>Rs. <?php echo number_format($purchase['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <?php if ($purchase['notes']): ?>
        <div class="mb-8">
            <h3 class="font-bold mb-2">Notes:</h3>
            <p><?php echo nl2br($purchase['notes']); ?></p>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-8 mt-16">
            <div class="text-center">
                <div class="border-t border-gray-300 pt-2">
                    Authorized Signature
                </div>
            </div>
            <div class="text-center">
                <div class="border-t border-gray-300 pt-2">
                    Supplier Signature
                </div>
            </div>
        </div>

        <div class="text-center mt-8">
            <button onclick="window.print()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded-lg no-print">
                <i class="fas fa-print"></i> Print Purchase Order
            </button>
        </div>
    </div>
</body>
</html>