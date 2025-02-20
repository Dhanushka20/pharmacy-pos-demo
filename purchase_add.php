<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get all suppliers
$stmt = $pdo->query("SELECT id, name FROM suppliers ORDER BY name");
$suppliers = $stmt->fetchAll();

// Generate reference number
$stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(reference_no, 4) AS UNSIGNED)) as last_number FROM purchases WHERE reference_no LIKE 'PO-%'");
$result = $stmt->fetch();
$next_number = ($result['last_number'] ?? 0) + 1;
$reference_no = 'PO-' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Purchase - Pharmacy POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .medicine-item.selected {
            background-color: #EBF5FF;
        }
        .search-results {
            max-height: 250px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-wrap -mx-4">
            <!-- Left Side - Product Selection -->
            <div class="w-full lg:w-8/12 px-4 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Supplier</label>
                        <select id="supplier" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['id']; ?>">
                                    <?php echo $supplier['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Medicine</label>
                        <div class="relative">
                            <input type="text" id="medicine-search" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Type medicine name or code...">
                            <div id="search-results" 
                                 class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-300 search-results hidden">
                            </div>
                        </div>
                    </div>

                    <table class="w-full" id="purchase-table">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <!-- Items will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Side - Payment Information -->
            <div class="w-full lg:w-4/12 px-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Purchase Details</h2>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                        <input type="text" id="reference-no" value="<?php echo $reference_no; ?>" readonly
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md focus:outline-none">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" id="purchase-date" value="<?php echo date('Y-m-d'); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount</label>
                        <input type="text" id="total-amount" readonly value="0.00"
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-xl font-bold">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Status</label>
                        <select id="payment-status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea id="notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <button id="save-purchase" 
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition duration-200">
                        Save Purchase
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let items = [];
            let medicines = [];
            let selectedIndex = -1;

            // Load all medicines initially
            $.ajax({
                url: 'get_medicines.php',
                method: 'GET',
                success: function(response) {
                    medicines = response;
                }
            });

            const searchInput = $('#medicine-search');
            const searchResults = $('#search-results');

            // Handle search input
            searchInput.on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                selectedIndex = -1;
                
                if (searchTerm.length < 1) {
                    searchResults.hide();
                    return;
                }

                // Filter medicines based on search term
                const filteredMedicines = medicines.filter(medicine => 
                    medicine.name.toLowerCase().includes(searchTerm) || 
                    medicine.code.toLowerCase().includes(searchTerm)
                );

                // Display search results
                if (filteredMedicines.length > 0) {
                    const resultsHtml = filteredMedicines.map(medicine => `
                        <div class="medicine-item p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-200" 
                             data-medicine='${JSON.stringify(medicine)}'>
                            <div class="font-medium">${medicine.name} - ${medicine.code}</div>
                        </div>
                    `).join('');
                    
                    searchResults.html(resultsHtml).show();
                } else {
                    searchResults.html('<div class="p-3 text-gray-500">No results found</div>').show();
                }
            });

            // Handle keyboard navigation
            searchInput.on('keydown', function(e) {
                const items = $('.medicine-item');
                
                if (items.length === 0) return;

                items.removeClass('selected');

                switch(e.keyCode) {
                    case 38: // Up arrow
                        e.preventDefault();
                        selectedIndex = selectedIndex > 0 ? selectedIndex - 1 : items.length - 1;
                        break;
                        
                    case 40: // Down arrow
                        e.preventDefault();
                        selectedIndex = selectedIndex < items.length - 1 ? selectedIndex + 1 : 0;
                        break;
                        
                    case 13: // Enter
                        e.preventDefault();
                        if (selectedIndex >= 0) {
                            const medicine = JSON.parse(items.eq(selectedIndex).attr('data-medicine'));
                            addMedicineToTable(medicine);
                            searchInput.val('');
                            searchResults.hide();
                            selectedIndex = -1;
                        }
                        return;
                }

                if (selectedIndex >= 0) {
                    const selectedItem = items.eq(selectedIndex);
                    selectedItem.addClass('selected');
                    
                    // Scroll into view if necessary
                    const container = searchResults[0];
                    const itemElement = selectedItem[0];
                    
                    if (itemElement.offsetTop < container.scrollTop || 
                        itemElement.offsetTop + itemElement.offsetHeight > container.scrollTop + container.offsetHeight) {
                        itemElement.scrollIntoView({ block: 'nearest' });
                    }
                }
            });

            // Handle medicine selection by click
            $(document).on('click', '.medicine-item', function() {
                const medicine = JSON.parse($(this).attr('data-medicine'));
                addMedicineToTable(medicine);
                searchInput.val('');
                searchResults.hide();
                selectedIndex = -1;
            });

            // Hide search results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#medicine-search, #search-results').length) {
                    searchResults.hide();
                    selectedIndex = -1;
                }
            });

            function addMedicineToTable(medicine) {
                const existingItem = items.find(item => item.id === medicine.id);
                if (existingItem) {
                    alert('This item is already in the list');
                    return;
                }

                const row = `
                    <tr data-id="${medicine.id}">
                        <td class="px-6 py-4">${medicine.name}</td>
                        <td class="px-6 py-4">
                            <input type="number" min="1" value="1" 
                                class="w-20 px-2 py-1 border border-gray-300 rounded-md quantity-input">
                        </td>
                        <td class="px-6 py-4">
                            <input type="number" step="0.01" value="${medicine.unit_price}" 
                                class="w-24 px-2 py-1 border border-gray-300 rounded-md unit-price-input">
                        </td>
                        <td class="px-6 py-4">Rs. ${medicine.unit_price}</td>
                        <td class="px-6 py-4">
                            <button class="text-red-500 hover:text-red-700 remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#purchase-table tbody').append(row);
                items.push({
                    id: medicine.id,
                    name: medicine.name,
                    unit_price: parseFloat(medicine.unit_price),
                    quantity: 1
                });

                updateTotal();
            }

            // Remove item
            $(document).on('click', '.remove-item', function() {
                const row = $(this).closest('tr');
                const id = parseInt(row.data('id'));
                items = items.filter(item => item.id !== id);
                row.remove();
                updateTotal();
            });

            // Update unit price
            $(document).on('change', '.unit-price-input', function() {
                const row = $(this).closest('tr');
                const id = parseInt(row.data('id'));
                const unit_price = parseFloat($(this).val());
                const item = items.find(item => item.id === id);
                
                if (item) {
                    item.unit_price = unit_price;
                    row.find('td:eq(3)').text(`Rs. ${(unit_price * item.quantity).toFixed(2)}`);
                    updateTotal();
                }
            });

            // Update quantity
            $(document).on('change', '.quantity-input', function() {
                const row = $(this).closest('tr');
                const id = parseInt(row.data('id'));
                const quantity = parseInt($(this).val());
                const item = items.find(item => item.id === id);
                
                if (item) {
                    item.quantity = quantity;
                    row.find('td:eq(3)').text(`Rs. ${(item.unit_price * quantity).toFixed(2)}`);
                    updateTotal();
                }
            });

            function updateTotal() {
                let total = items.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
                $('#total-amount').val(total.toFixed(2));
            }

            // Save purchase
            $('#save-purchase').click(function() {
                if (!$('#supplier').val()) {
                    alert('Please select a supplier');
                    return;
                }

                if (items.length === 0) {
                    alert('Please add items to the purchase');
                    return;
                }

                const purchaseData = {
                    supplier_id: $('#supplier').val(),
                    reference_no: $('#reference-no').val(),
                    date: $('#purchase-date').val(),
                    total_amount: parseFloat($('#total-amount').val()),
                    payment_status: $('#payment-status').val(),
                    notes: $('#notes').val(),
                    items: items
                };

            
                // Send data to server
                $.ajax({
                    url: 'save_purchase.php',
                    method: 'POST',
                    data: JSON.stringify(purchaseData),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            alert('Purchase saved successfully!');
                            window.location.href = 'purchases.php';
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error saving purchase');
                    }
                });
            });
        });
    </script>
</body>
</html>