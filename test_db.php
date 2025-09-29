<?php
// test_db.php - Quick database connection test
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
    $result = $stmt->fetch();
    echo "<p>✅ Database connected successfully!</p>";
    echo "<p>Total users in database: " . $result['total_users'] . "</p>";
    
    // Test each table
    $tables = ['users', 'doctors', 'patients', 'appointments', 'bills'];
    echo "<h3>Table Status:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetchColumn();
        echo "<li>$table: $count records</li>";
    }
    echo "</ul>";
    
    if ($result['total_users'] == 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>⚠️ No sample data found!</h4>";
        echo "<p>You need to load the sample data. Please:</p>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin</li>";
        echo "<li>Select the 'healsync' database</li>";
        echo "<li>Go to Import tab</li>";
        echo "<li>Upload and execute sql/seed_data.sql</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database connection failed: " . $e->getMessage() . "</p>";
}
?>
