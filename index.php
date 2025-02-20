<?php
session_start();
require_once 'config/database.php';

// Get dashboard statistics
$stats = [];

// Get total sales for today
$stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(date) = CURDATE()");
$sales = $stmt->fetch();
$stats['today_sales_count'] = $sales['count'];
$stats['today_sales_amount'] = $sales['total'];

// Get total medicines
$stmt = $pdo->query("SELECT COUNT(*) FROM medicines");
$stats['total_medicines'] = $stmt->fetchColumn();

// Get low stock medicines
$stmt = $pdo->query("SELECT COUNT(*) FROM medicines WHERE stock_quantity <= reorder_level");
$stats['low_stock'] = $stmt->fetchColumn();

// Get expired and expiring items
$today = date('Y-m-d');
$thirtyDaysFromNow = date('Y-m-d', strtotime('+30 days'));
$stmt = $pdo->query("
    SELECT id, name, dose, dose_unit, expiry_date, stock_quantity 
    FROM medicines 
    WHERE expiry_date <= '$thirtyDaysFromNow' 
    ORDER BY expiry_date ASC
");
$expiring_items = $stmt->fetchAll();

// Get recent sales
$stmt = $pdo->query("
    SELECT s.*, u.username 
    FROM sales s 
    LEFT JOIN users u ON s.cashier_id = u.id 
    ORDER BY s.date DESC LIMIT 5
");
$recent_sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pharmacy POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-hover {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .table-row-hover:hover {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Welcome Back, <?php echo $_SESSION['username']; ?>!</h1>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Today's Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 card-hover">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-50 rounded-lg p-3">
                        <i class="fas fa-shopping-cart text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-400">Today's Sales</h3>
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-gray-900">Rs. <?php echo number_format($stats['today_sales_amount'], 2); ?></span>
                            <span class="ml-2 text-sm text-gray-500"><?php echo $stats['today_sales_count']; ?> transactions</span>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-blue-50 rounded-full">
                    <div class="h-1 bg-blue-500 rounded-full" style="width: <?php echo min(100, ($stats['today_sales_count'] / 10) * 100); ?>%"></div>
                </div>
            </div>

            <!-- Total Medicines -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 card-hover">
                <div class="flex items-center mb-4">
                    <div class="bg-green-50 rounded-lg p-3">
                        <i class="fas fa-pills text-green-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-400">Total Medicines</h3>
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-gray-900"><?php echo $stats['total_medicines']; ?></span>
                            <span class="ml-2 text-sm text-gray-500">items</span>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-green-50 rounded-full">
                    <div class="h-1 bg-green-500 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 card-hover">
                <div class="flex items-center mb-4">
                    <div class="bg-red-50 rounded-lg p-3">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-400">Low Stock Items</h3>
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-gray-900"><?php echo $stats['low_stock']; ?></span>
                            <span class="ml-2 text-sm text-gray-500">items need attention</span>
                        </div>
                    </div>
                </div>
                <div class="h-1 bg-red-50 rounded-full">
                    <div class="h-1 bg-red-500 rounded-full" style="width: <?php echo min(100, ($stats['low_stock'] / $stats['total_medicines']) * 100); ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Expiring Items -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 card-hover">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Expiring Items</h2>
                    <a href="medicines.php" class="text-blue-500 hover:text-blue-600 text-sm">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200">
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Medicine</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Dose</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Expiry</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($expiring_items as $item): 
                                $expired = strtotime($item['expiry_date']) < strtotime($today);
                                $expiring_soon = strtotime($item['expiry_date']) <= strtotime($thirtyDaysFromNow);
                            ?>
                            <tr class="text-sm table-row-hover">
                                <td class="px-4 py-3 text-gray-800"><?php echo $item['name']; ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo $item['dose'] . ' ' . $item['dose_unit']; ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo $item['stock_quantity']; ?></td>
                                <td class="px-4 py-3 text-gray-600"><?php echo date('Y-m-d', strtotime($item['expiry_date'])); ?></td>
                                <td class="px-4 py-3">
                                    <?php if ($expired): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-50 text-red-600 font-medium">Expired</span>
                                    <?php elseif ($expiring_soon): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-50 text-yellow-600 font-medium">Expiring Soon</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 card-hover">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-gray-800">Recent Sales</h2>
                    <a href="sales.php" class="text-blue-500 hover:text-blue-600 text-sm">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left border-b border-gray-200">
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recent_sales as $sale): ?>
                            <tr class="text-sm table-row-hover">
                                <td class="px-4 py-3">
                                    <a href="sale_view.php?id=<?php echo $sale['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-700 font-medium">
                                        <?php echo $sale['invoice_number']; ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <?php echo $sale['customer_name'] ?: 'Walk-in Customer'; ?>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    Rs. <?php echo number_format($sale['total_amount'], 2); ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-50 text-green-600 font-medium">
                                        Completed
                                    </span>
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