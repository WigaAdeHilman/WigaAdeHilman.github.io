<?php
/**
 * Database Backup Script
 * Membuat backup database contact_messages
 */

require_once 'config/database.php';

class DatabaseBackup {
    private $conn;
    private $dbName;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->dbName = 'portfolio_db'; // Sesuaikan dengan nama database
    }
    
    public function createBackup($includeData = true) {
        try {
            $backup = "-- Portfolio Database Backup\n";
            $backup .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Get table structure
            $backup .= $this->getTableStructure();
            
            if($includeData) {
                $backup .= $this->getTableData();
            }
            
            return $backup;
            
        } catch(Exception $e) {
            return false;
        }
    }
    
    private function getTableStructure() {
        $structure = "-- Table structure for table `contact_messages`\n";
        $structure .= "DROP TABLE IF EXISTS `contact_messages`;\n";
        
        $query = "SHOW CREATE TABLE contact_messages";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $structure .= $result['Create Table'] . ";\n\n";
        
        return $structure;
    }
    
    private function getTableData() {
        $data = "-- Dumping data for table `contact_messages`\n\n";
        
        $query = "SELECT * FROM contact_messages ORDER BY id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if($results) {
            $data .= "INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `message`, `created_at`, `status`) VALUES\n";
            
            $values = [];
            foreach($results as $row) {
                $values[] = "(" . 
                    $row['id'] . ", " .
                    "'" . addslashes($row['name']) . "', " .
                    "'" . addslashes($row['email']) . "', " .
                    "'" . addslashes($row['phone']) . "', " .
                    "'" . addslashes($row['message']) . "', " .
                    "'" . $row['created_at'] . "', " .
                    "'" . $row['status'] . "'" .
                    ")";
            }
            
            $data .= implode(",\n", $values) . ";\n\n";
        }
        
        return $data;
    }
    
    public function downloadBackup($filename = null) {
        if(!$filename) {
            $filename = 'portfolio_backup_' . date('Y-m-d_H-i-s') . '.sql';
        }
        
        $backup = $this->createBackup();
        
        if($backup) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($backup));
            echo $backup;
            exit;
        }
        
        return false;
    }
    
    public function saveBackupToFile($directory = 'backups/') {
        if(!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        $filename = 'portfolio_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $directory . $filename;
        
        $backup = $this->createBackup();
        
        if($backup && file_put_contents($filepath, $backup)) {
            return $filepath;
        }
        
        return false;
    }
    
    public function getBackupStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_messages,
                        COUNT(CASE WHEN status = 'unread' THEN 1 END) as unread_messages,
                        COUNT(CASE WHEN status = 'read' THEN 1 END) as read_messages,
                        COUNT(CASE WHEN status = 'replied' THEN 1 END) as replied_messages,
                        MIN(created_at) as oldest_message,
                        MAX(created_at) as newest_message
                      FROM contact_messages";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(Exception $e) {
            return false;
        }
    }
}

// Handle backup requests
if(isset($_GET['action'])) {
    $backup = new DatabaseBackup();
    
    switch($_GET['action']) {
        case 'download':
            $backup->downloadBackup();
            break;
            
        case 'save':
            $filepath = $backup->saveBackupToFile();
            if($filepath) {
                $message = "Backup saved successfully to: " . $filepath;
            } else {
                $error = "Failed to save backup file";
            }
            break;
            
        case 'structure_only':
            $backupContent = $backup->createBackup(false);
            if($backupContent) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="portfolio_structure_' . date('Y-m-d_H-i-s') . '.sql"');
                echo $backupContent;
                exit;
            }
            break;
    }
}

$backup = new DatabaseBackup();
$stats = $backup->getBackupStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup - Portfolio</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .backup-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .backup-card {
            background: #fff;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: border-color 0.3s ease;
        }
        
        .backup-card:hover {
            border-color: #007bff;
        }
        
        .backup-card h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .backup-card p {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        
        .btn-warning { background: #ffc107; color: black; }
        .btn-warning:hover { background: #e0a800; }
        
        .btn-info { background: #17a2b8; }
        .btn-info:hover { background: #117a8b; }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-section {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-section h3 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
        }
        
        .info-list li {
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-list li:last-child {
            border-bottom: none;
        }
        
        .navigation {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        
        .navigation a {
            margin: 0 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .stats-grid,
            .backup-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Backup Management</h1>
        
        <?php if(isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($stats): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_messages']; ?></div>
                    <div class="stat-label">Total Messages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['unread_messages']; ?></div>
                    <div class="stat-label">Unread Messages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['read_messages']; ?></div>
                    <div class="stat-label">Read Messages</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['replied_messages']; ?></div>
                    <div class="stat-label">Replied Messages</div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="backup-options">
            <div class="backup-card">
                <h3>📥 Full Backup</h3>
                <p>Download complete backup including all data and table structure</p>
                <a href="?action=download" class="btn btn-success">Download Full Backup</a>
            </div>
            
            <div class="backup-card">
                <h3>🏗️ Structure Only</h3>
                <p>Download only database structure without data</p>
                <a href="?action=structure_only" class="btn btn-info">Download Structure</a>
            </div>
            
            <div class="backup-card">
                <h3>💾 Save to Server</h3>
                <p>Save backup file to server's backup directory</p>
                <a href="?action=save" class="btn btn-warning">Save to Server</a>
            </div>
        </div>
        
        <div class="info-section">
            <h3>📋 Backup Information</h3>
            <ul class="info-list">
                <li><strong>Database:</strong> portfolio_db</li>
                <li><strong>Table:</strong> contact_messages</li>
                <li><strong>Backup Format:</strong> SQL</li>
                <li><strong>Compression:</strong> None (Plain text)</li>
                <?php if($stats): ?>
                    <li><strong>Date Range:</strong> 
                        <?php echo $stats['oldest_message'] ? date('Y-m-d', strtotime($stats['oldest_message'])) : 'N/A'; ?> 
                        to 
                        <?php echo $stats['newest_message'] ? date('Y-m-d', strtotime($stats['newest_message'])) : 'N/A'; ?>
                    </li>
                <?php endif; ?>
                <li><strong>Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
            </ul>
        </div>
        
        <div class="info-section">
            <h3>ℹ️ Usage Instructions</h3>
            <ul class="info-list">
                <li><strong>Full Backup:</strong> Use this for complete data migration or restoration</li>
                <li><strong>Structure Only:</strong> Use this to recreate table structure on new server</li>
                <li><strong>Save to Server:</strong> Automatically saves backup to 'backups/' directory</li>
                <li><strong>Restoration:</strong> Import the SQL file through phpMyAdmin or MySQL client</li>
                <li><strong>Schedule:</strong> Consider setting up automated daily/weekly backups</li>
            </ul>
        </div>
        
        <div class="navigation">
            <a href="admin/view_messages.php" class="btn">📊 View Messages</a>
            <a href="test_connection.php" class="btn btn-info">🔧 Test Connection</a>
            <a href="index.html" class="btn btn-success">🏠 Go to Portfolio</a>
        </div>
    </div>
</body>
</html>