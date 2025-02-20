<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Generate invoice number
$stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(invoice_number, 4) AS UNSIGNED)) as last_number FROM sales WHERE invoice_number LIKE 'INV%'");
$result = $stmt->fetch();
$next_number = ($result['last_number'] ?? 0) + 1;
$invoice_number = 'INV' . str_pad($next_number, 6, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Pharmacy POS</title>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Medicine</label>
                        <div class="relative">
                            <div class="flex">
                                <input type="text" id="medicine-search" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="Type medicine name or code...">
                                <button id="show-all-medicines" 
                                        class="px-4 py-2 bg-blue-500 text-white rounded-r-md hover:bg-blue-600 focus:outline-none">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div id="search-results" 
                                 class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-300 search-results hidden">
                            </div>
                        </div>
                    </div>

                    <table class="w-full" id="pos-table">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
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
                    <h2 class="text-xl font-semibold mb-4">Payment Details</h2>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number</label>
                        <input type="text" id="invoice-number" value="<?php echo $invoice_number; ?>" readonly
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md focus:outline-none">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name</label>
                        <input type="text" id="customer-name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                        <input type="text" id="subtotal" readonly value="0.00"
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Discount</label>
                        <input type="number" id="discount" value="0" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Amount</label>
                        <input type="text" id="total-amount" readonly value="0.00"
                               class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-xl font-bold">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select id="payment-method" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                        </select>
                    </div>

                    <button id="complete-sale" 
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition duration-200">
                        Complete Sale
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

            // Show all medicines when clicking dropdown button
            $('#show-all-medicines').click(function() {
                if (searchResults.is(':visible')) {
                    searchResults.hide();
                    return;
                }

                // Display all medicines
                const resultsHtml = medicines.map(medicine => `
                    <div class="medicine-item p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-200" 
                         data-medicine='${JSON.stringify(medicine)}'>
                        <div class="font-medium">${medicine.name} - ${medicine.code}</div>
                        <div class="text-sm text-gray-600">Stock: ${medicine.stock_quantity}</div>
                    </div>
                `).join('');
                
                searchResults.html(resultsHtml).show();
            });

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
                            <div class="text-sm text-gray-600">Stock: ${medicine.stock_quantity}</div>
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
                if (!$(e.target).closest('#medicine-search, #search-results, #show-all-medicines').length) {
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
                        <td class="px-6 py-4">Rs. ${medicine.selling_price}</td>
                        <td class="px-6 py-4">
                            <input type="number" min="1" max="${medicine.stock_quantity}" value="1" 
                                class="w-20 px-2 py-1 border border-gray-300 rounded-md quantity-input">
                        </td>
                        <td class="px-6 py-4">Rs. ${medicine.selling_price}</td>
                        <td class="px-6 py-4">
                            <button class="text-red-500 hover:text-red-700 remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#pos-table tbody').append(row);
                items.push({
                    id: medicine.id,
                    name: medicine.name,
                    price: parseFloat(medicine.selling_price),
                    quantity: 1
                });

                updateTotals();
            }

            // Remove item
            $(document).on('click', '.remove-item', function() {
                const row = $(this).closest('tr');
                const id = parseInt(row.data('id'));
                items = items.filter(item => item.id !== id);
                row.remove();
                updateTotals();
            });

            // Update quantity
            $(document).on('change', '.quantity-input', function() {
                const row = $(this).closest('tr');
                const id = parseInt(row.data('id'));
                const quantity = parseInt($(this).val());
                const item = items.find(item => item.id === id);
                
                if (item) {
                    item.quantity = quantity;
                    row.find('td:eq(3)').text(`Rs. ${(item.price * quantity).toFixed(2)}`);
                    updateTotals();
                }
            });

            // Update totals when discount changes
            $('#discount').on('input', updateTotals);

            function updateTotals() {
                let subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                let discount = parseFloat($('#discount').val()) || 0;
                let total = subtotal - discount;

                $('#subtotal').val(subtotal.toFixed(2));
                $('#total-amount').val(total.toFixed(2));
            }

            // Complete sale
            $('#complete-sale').click(function() {
                if (items.length === 0) {
                    alert('Please add items to the cart');
                    return;
                }

                const saleData = {
                    invoice_number: $('#invoice-number').val(),
                    customer_name: $('#customer-name').val(),
                    subtotal: parseFloat($('#subtotal').val()),
                    discount: parseFloat($('#discount').val()),
                    total_amount: parseFloat($('#total-amount').val()),
                    payment_method: $('#payment-method').val(),
                    items: items
                };

                // Send data to server
               $.ajax({
                   url: 'save_sale.php',
                   method: 'POST',
                   data: JSON.stringify(saleData),
                   contentType: 'application/json',
                   success: function(response) {
                       if (response.success) {
                           alert('Sale completed successfully!');
                           window.location.href = 'sale_print.php?id=' + response.sale_id;
                       } else {
                           alert('Error: ' + response.message);
                       }
                   },
                   error: function() {
                       alert('Error saving sale');
                   }
               });
           });
       });
   </script>
</body>
</html>