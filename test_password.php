<?php
// test_password.php - Test what password matches the hash
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

// Common passwords to test
$passwords_to_test = [
    'password',
    'secret',
    'admin',
    '123456',
    'password123',
    'admin123',
    'healsync',
    'test',
    'demo'
];

echo "<h2>Password Hash Test</h2>";
echo "<p>Testing hash: <code>$hash</code></p>";

foreach ($passwords_to_test as $password) {
    if (password_verify($password, $hash)) {
        echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ Password Found!</h3>";
        echo "<p><strong>Password:</strong> <code>$password</code></p>";
        echo "</div>";
        break;
    }
}

echo "<h3>Sample User Accounts:</h3>";
echo "<ul>";
echo "<li><strong>Admin:</strong> admin@healsync.com</li>";
echo "<li><strong>Doctor:</strong> mchen@healsync.com</li>";
echo "<li><strong>Patient:</strong> john.smith@email.com</li>";
echo "<li><strong>Receptionist:</strong> mgarcia@healsync.com</li>";
echo "</ul>";
?>
