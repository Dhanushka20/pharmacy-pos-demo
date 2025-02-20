// config/demo_database.php - In-memory data storage
<?php
class DemoDatabase {
    private static $data = [
        'users' => [
            ['id' => 1, 'username' => 'admin', 'password' => '$2a$12$1cCRFByxK2m.QsNS9GCU.Oa0ZcwC/YRZcB441kwvg4ZMWjtmlqJee', 'role' => 'admin'],
        ],
        'medicines' => [
            ['id' => 1, 'code' => 'MED001', 'name' => 'Paracetamol', 'dose' => '500', 'dose_unit' => 'mg', 'stock_quantity' => 100, 'unit_price' => 5.00, 'selling_price' => 7.50],
            ['id' => 2, 'code' => 'MED002', 'name' => 'Amoxicillin', 'dose' => '250', 'dose_unit' => 'mg', 'stock_quantity' => 50, 'unit_price' => 10.00, 'selling_price' => 15.00],
        ],
        'sales' => [],
        'suppliers' => [
            ['id' => 1, 'name' => 'ABC Pharmaceuticals', 'contact_person' => 'John Doe', 'phone' => '1234567890'],
        ]
    ];

    public static function get($table) {
        return self::$data[$table] ?? [];
    }

    public static function find($table, $id) {
        foreach (self::$data[$table] as $item) {
            if ($item['id'] == $id) return $item;
        }
        return null;
    }
}
?>