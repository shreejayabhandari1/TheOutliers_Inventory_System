<?php

echo "<h2>StoreHub Debug Test</h2>";
echo "<pre>";

echo "✅ PHP is working. Version: " . phpversion() . "\n\n";

if (extension_loaded('mysqli')) {
    echo "✅ MySQLi extension is loaded\n\n";
} else {
    echo "❌ MySQLi extension is NOT loaded - enable it in php.ini\n\n";
}

echo "Trying to connect to MySQL...\n";

$conn = @mysqli_connect('localhost', 'root', '', 'storehub');

if ($conn) {
    echo "✅ Connected to database 'storehub' successfully!\n\n";

    $result = mysqli_query($conn, "SHOW TABLES");
    echo "Tables found:\n";
    while ($row = mysqli_fetch_array($result)) {
        echo "  - " . $row[0] . "\n";
    }

    $users = mysqli_query($conn, "SELECT id, name, email, role FROM users");
    echo "\nUsers in database:\n";
    while ($u = mysqli_fetch_assoc($users)) {
        echo "  - [{$u['role']}] {$u['name']} ({$u['email']})\n";
    }
    
    mysqli_close($conn);
    
} else {
    echo "❌ Connection FAILED!\n";
    echo "Error: " . mysqli_connect_error() . "\n";
    echo "Error code: " . mysqli_connect_errno() . "\n\n";

    echo "Trying without database name...\n";
    $conn2 = @mysqli_connect('localhost', 'root', '');
    if ($conn2) {
        echo "✅ MySQL server is reachable (root/empty password works)\n";
        echo "But 'storehub' database may not exist or wrong DB_NAME\n\n";

        $dbs = mysqli_query($conn2, "SHOW DATABASES");
        echo "Available databases:\n";
        while ($db = mysqli_fetch_array($dbs)) {
            echo "  - " . $db[0] . "\n";
        }
    } else {
        echo "❌ Cannot connect to MySQL at all\n";
        echo "Error: " . mysqli_connect_error() . "\n\n";
        echo "Possible causes:\n";
        echo "  1. MySQL is not running in XAMPP\n";
        echo "  2. Port 3306 is blocked\n";
        echo "  3. Wrong username/password\n";
    }
}

echo "</pre>";
echo "<hr><p style='color:red'><strong>DELETE test.php after fixing!</strong></p>";
?>
