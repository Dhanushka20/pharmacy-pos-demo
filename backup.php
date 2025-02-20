<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

function createBackup($pdo) {
    try {
        $backup_dir = 'backups/';
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }

        // Create backup filename with timestamp
        $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Get all tables
        $tables = [];
        $result = $pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $output = "-- Pharmacy POS Database Backup\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

        // Process each table
        foreach ($tables as $table) {
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            
            // Get create table statement
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $output .= $row[1] . ";\n\n";
            
            // Get table data
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_NUM);
            
            if ($rows) {
                $output .= "INSERT INTO `$table` VALUES\n";
                $values = [];
                foreach ($rows as $row) {
                    $value = "(";
                    foreach ($row as $data) {
                        $value .= is_null($data) ? "NULL," : "'" . addslashes($data) . "',";
                    }
                    $value = rtrim($value, ",") . ")";
                    $values[] = $value;
                }
                $output .= implode(",\n", $values) . ";\n\n";
            }
        }

        // Save backup file
        file_put_contents($backup_file, $output);
        return basename($backup_file);
    } catch (Exception $e) {
        throw new Exception("Backup failed: " . $e->getMessage());
    }
}

// Handle backup creation
if (isset($_POST['create_backup'])) {
    try {
        $filename = createBackup($pdo);
        $_SESSION['success'] = "Backup created successfully: " . $filename;
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: backup.php');
    exit;
}

// Handle backup restore
if (isset($_POST['restore_backup']) && isset($_FILES['backup_file'])) {
    try {
        $file = $_FILES['backup_file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $sql = file_get_contents($file['tmp_name']);
            $pdo->exec($sql);
            $_SESSION['success'] = "Database restored successfully";
        } else {
            throw new Exception("Error uploading file");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Restore failed: " . $e->getMessage();
    }
    header('Location: backup.php');
    exit;
}

// Get list of existing backups
$backups = [];
$backup_dir = 'backups/';
if (file_exists($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => date('Y-m-d H:i:s', filemtime($backup_dir . $file))
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup - Pharmacy POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">Database Backup & Restore</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6">
                    <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6">
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Create Backup -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Create Backup</h2>
                    <form method="POST">
                        <button type="submit" name="create_backup" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg">
                            <i class="fas fa-download mr-2"></i> Create New Backup
                        </button>
                    </form>
                </div>

                <!-- Restore Backup -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Restore Backup</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <input type="file" name="backup_file" accept=".sql" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <button type="submit" name="restore_backup" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg"
                                onclick="return confirm('Are you sure you want to restore this backup? Current data will be replaced.')">
                            <i class="fas fa-upload mr-2"></i> Restore Backup
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Backups -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-xl font-semibold mb-4">Existing Backups</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filename</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                            <tr class="border-b">
                                <td class="px-6 py-4"><?php echo $backup['name']; ?></td>
                                <td class="px-6 py-4"><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                                <td class="px-6 py-4"><?php echo $backup['date']; ?></td>
                                <td class="px-6 py-4">
                                    <a href="backups/<?php echo $backup['name']; ?>" download 
                                       class="text-blue-500 hover:text-blue-700 mr-3">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="backup_delete.php?file=<?php echo $backup['name']; ?>" 
                                       class="text-red-500 hover:text-red-700"
                                       onclick="return confirm('Are you sure you want to delete this backup?')">
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