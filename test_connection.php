<?php
// File untuk testing koneksi database
require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; }
    .error { color: red; background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; }
    .info { color: blue; background: #cce5ff; padding: 10px; border: 1px solid #b3d9ff; border-radius: 5px; }
    .test-item { margin: 10px 0; }
</style>";

// Test 1: Database Connection
echo "<div class='test-item'>";
echo "<h3>1. Testing Database Connection...</h3>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if($db) {
        echo "<div class='success'>✅ Database connection successful!</div>";
        
        // Test 2: Check if table exists
        echo "<h3>2. Checking Table Structure...</h3>";
        
        $query = "DESCRIBE contact_messages";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if($columns) {
            echo "<div class='success'>✅ Table 'contact_messages' exists!</div>";
            echo "<div class='info'><strong>Table Structure:</strong><br>";
            foreach($columns as $column) {
                echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}<br>";
            }
            echo "</div>";
        } else {
            echo "<div class='error'>❌ Table 'contact_messages' not found!</div>";
        }
        
        // Test 3: Count existing records
        echo "<h3>3. Checking Existing Data...</h3>";
        
        $query = "SELECT COUNT(*) as total FROM contact_messages";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<div class='info'>📊 Total messages in database: {$result['total']}</div>";
        
        // Test 4: Insert sample data (optional)
        if(isset($_GET['insert_sample']) && $_GET['insert_sample'] == '1') {
            echo "<h3>4. Inserting Sample Data...</h3>";
            
            $sampleData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '1234567890',
                'message' => 'This is a test message from connection test.'
            ];
            
            $query = "INSERT INTO contact_messages (name, email, phone, message) 
                     VALUES (:name, :email, :phone, :message)";
            $stmt = $db->prepare($query);
            
            if($stmt->execute($sampleData)) {
                echo "<div class='success'>✅ Sample data inserted successfully!</div>";
            } else {
                echo "<div class='error'>❌ Failed to insert sample data</div>";
            }
        } else {
            echo "<div class='info'>
                <a href='?insert_sample=1' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>
                    Insert Sample Data
                </a>
            </div>";
        }
        
        // Test 5: Recent messages
        echo "<h3>5. Recent Messages (Last 5)...</h3>";
        
        $query = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if($recentMessages) {
            echo "<div class='info'>";
            foreach($recentMessages as $msg) {
                echo "<div style='border-bottom: 1px solid #ddd; padding: 5px 0;'>";
                echo "<strong>#{$msg['id']}</strong> - {$msg['name']} ({$msg['email']}) - {$msg['created_at']}";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='info'>No messages found in database.</div>";
        }
        
    } else {
        echo "<div class='error'>❌ Database connection failed!</div>";
    }
    
} catch(Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div>";

// Configuration Info
echo "<h3>Configuration Information:</h3>";
echo "<div class='info'>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>PDO Available:</strong> " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
echo "<strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";
echo "<strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "<br>";
echo "</div>";

// Quick Actions
echo "<h3>Quick Actions:</h3>";
echo "<div style='margin: 10px 0;'>";
echo "<a href='admin/view_messages.php' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>View Admin Panel</a>";
echo "<a href='index.html' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;'>Go to Portfolio</a>";
echo "<a href='?refresh=1' style='background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>Refresh Test</a>";
echo "</div>";
?>