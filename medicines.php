<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all medicines
$stmt = $pdo->query("SELECT * FROM medicines ORDER BY name ASC");
$medicines = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy POS - Medicines</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Medicines</h1>
            <a href="medicine_add.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus"></i> Add New Medicine
            </a>
        </div>

        <!-- Medicines Table -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Selling Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($medicines as $medicine): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $medicine['code']; ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium"><?php echo $medicine['name']; ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php 
                                        if (!empty($medicine['dose']) && !empty($medicine['dose_unit'])) {
                                            echo $medicine['dose'] . ' ' . $medicine['dose_unit'];
                                        }
                                        if (!empty($medicine['generic_name'])) {
                                            if (!empty($medicine['dose'])) echo ' • ';
                                            echo $medicine['generic_name'];
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $medicine['category']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($medicine['stock_quantity'] <= $medicine['reorder_level']): ?>
                                        <span class="text-red-500 font-semibold"><?php echo $medicine['stock_quantity']; ?></span>
                                    <?php else: ?>
                                        <span><?php echo $medicine['stock_quantity']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">Rs. <?php echo number_format($medicine['unit_price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">Rs. <?php echo number_format($medicine['selling_price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $expiry_date = new DateTime($medicine['expiry_date']);
                                    $today = new DateTime();
                                    $interval = $today->diff($expiry_date);
                                    $days_remaining = $interval->days;
                                    
                                    if ($expiry_date < $today) {
                                        echo '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Expired</span>';
                                    } elseif ($days_remaining <= 30) {
                                        echo '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Expiring soon</span>';
                                    } else {
                                        echo $medicine['expiry_date'];
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="medicine_edit.php?id=<?php echo $medicine['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="medicine_delete.php?id=<?php echo $medicine['id']; ?>" class="text-red-500 hover:text-red-700" 
                                       onclick="return confirm('Are you sure you want to delete this medicine?')">
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