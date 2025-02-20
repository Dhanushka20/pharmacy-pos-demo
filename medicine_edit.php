<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get medicine details
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $medicine = $stmt->fetch();
    
    if (!$medicine) {
        header('Location: medicines.php');
        exit;
    }
} else {
    header('Location: medicines.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE medicines 
            SET code = ?, name = ?, generic_name = ?, category = ?, 
                unit = ?, unit_price = ?, selling_price = ?, stock_quantity = ?, 
                reorder_level = ?, expiry_date = ?, manufacturer = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['code'],
            $_POST['name'],
            $_POST['generic_name'],
            $_POST['category'],
            $_POST['unit'],
            $_POST['unit_price'],
            $_POST['selling_price'],
            $_POST['stock_quantity'],
            $_POST['reorder_level'],
            $_POST['expiry_date'],
            $_POST['manufacturer'],
            $_GET['id']
        ]);
        
        header('Location: medicines.php');
        exit;
    } catch (PDOException $e) {
        $error = "Error updating medicine: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Medicine - Pharmacy POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Edit Medicine</h2>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medicine Code</label>
                            <input type="text" name="code" required value="<?php echo $medicine['code']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medicine Name</label>
                            <input type="text" name="name" required value="<?php echo $medicine['name']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Generic Name</label>
                            <input type="text" name="generic_name" value="<?php echo $medicine['generic_name']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <input type="text" name="category" value="<?php echo $medicine['category']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                            <input type="text" name="unit" required value="<?php echo $medicine['unit']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
                            <input type="number" step="0.01" name="unit_price" required value="<?php echo $medicine['unit_price']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Selling Price</label>
                            <input type="number" step="0.01" name="selling_price" required value="<?php echo $medicine['selling_price']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Stock Quantity</label>
                            <input type="number" name="stock_quantity" required value="<?php echo $medicine['stock_quantity']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Level</label>
                            <input type="number" name="reorder_level" required value="<?php echo $medicine['reorder_level']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                            <input type="date" name="expiry_date" required value="<?php echo $medicine['expiry_date']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Manufacturer</label>
                            <input type="text" name="manufacturer" value="<?php echo $medicine['manufacturer']; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <a href="medicines.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg mr-4">Cancel</a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Update Medicine
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>