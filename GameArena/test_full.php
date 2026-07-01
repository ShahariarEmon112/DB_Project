<?php
require 'C:/xampp/htdocs/GameArena/config/database.php';
require 'C:/xampp/htdocs/GameArena/includes/auth.php';

echo "Database class: " . (class_exists('Database') ? 'OK' : 'FAIL') . PHP_EOL;
echo "Auth class: " . (class_exists('Auth') ? 'OK' : 'FAIL') . PHP_EOL;

try {
    $db = getDB();
    echo "DB connection: " . ($db->isConnected() ? 'OK' : 'FAIL') . PHP_EOL;
    
    // Test login
    $result = Auth::login('admin', 'password123');
    echo "Login: " . ($result['success'] ? 'OK' : 'FAIL: ' . $result['errors'][0]) . PHP_EOL;
    
    if ($result['success']) {
        echo "User: " . Auth::getUser()['full_name'] . PHP_EOL;
        echo "Role: " . Auth::getUser()['role_name'] . PHP_EOL;
        echo "Is Admin: " . (Auth::isAdmin() ? 'YES' : 'NO') . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
