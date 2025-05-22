<?php
require_once '../config/database.php';

class MessageViewer {
    private $conn;
    private $table_name = "contact_messages";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllMessages($limit = 50, $offset = 0) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                     ORDER BY created_at DESC 
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            return false;
        }
    }

    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET status = :status 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":id", $id);
            
            return $stmt->execute();
        } catch(PDOException $exception) {
            return false;
        }
    }

    public function deleteMessage($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            
            return $stmt->execute();
        } catch(PDOException $exception) {
            return false;
        }
    }

    public function getMessageCount() {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'];
        } catch(PDOException $exception) {
            return 0;
        }
    }
}

// Handle actions
if(isset($_POST['action'])) {
    $database = new Database();
    $db = $database->getConnection();
    $messageViewer = new MessageViewer($db);
    
    if($_POST['action'] == 'update_status' && isset($_POST['id']) && isset($_POST['status'])) {
        $success = $messageViewer->updateStatus($_POST['id'], $_POST['status']);
        if($success) {
            $message = "Status berhasil diupdate";
        } else {
            $error = "Gagal mengupdate status";
        }
    }
    
    if($_POST['action'] == 'delete' && isset($_POST['id'])) {
        $success = $messageViewer->deleteMessage($_POST['id']);
        if($success) {
            $message = "Pesan berhasil dihapus";
        } else {
            $error = "Gagal menghapus pesan";
        }
    }
}

// Get messages
$database = new Database();
$db = $database->getConnection();
$messageViewer = new MessageViewer($db);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$messages = $messageViewer->getAllMessages($limit, $offset);
$totalMessages = $messageViewer->getMessageCount();
$totalPages = ceil($totalMessages / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Contact Messages</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .message-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .message-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .message-info span {
            font-weight: bold;
            color: #333;
        }
        
        .message-content {
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }
        
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        
        .status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-unread { background: #ffc107; color: black; }
        .status-read { background: #28a745; color: white; }
        .status-replied { background: #007bff; color: white; }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination a {
            padding: 8px 12px;
            text-decoration: none;
            background: #007bff;
            color: white;
            border-radius: 3px;
        }
        
        .pagination a.active {
            background: #0056b3;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
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
        
        @media (max-width: 768px) {
            .message-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .message-info {
                flex-direction: column;
                gap: 5px;
            }
            
            .message-actions {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel - Contact Messages</h1>
        
        <?php if(isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalMessages; ?></div>
                <div>Total Messages</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $page; ?></div>
                <div>Current Page</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $totalPages; ?></div>
                <div>Total Pages</div>
            </div>
        </div>
        
        <?php if($messages && count($messages) > 0): ?>
            <?php foreach($messages as $msg): ?>
                <div class="message-card">
                    <div class="message-header">
                        <div class="message-info">
                            <span>ID: <?php echo $msg['id']; ?></span>
                            <span>Name: <?php echo htmlspecialchars($msg['name']); ?></span>
                            <span>Email: <?php echo htmlspecialchars($msg['email']); ?></span>
                            <span>Phone: <?php echo htmlspecialchars($msg['phone']); ?></span>
                        </div>
                        <span class="status status-<?php echo $msg['status']; ?>">
                            <?php echo ucfirst($msg['status']); ?>
                        </span>
                    </div>
                    
                    <div class="message-content">
                        <strong>Message:</strong><br>
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    
                    <div>
                        <small><strong>Received:</strong> <?php echo date('d M Y H:i', strtotime($msg['created_at'])); ?></small>
                    </div>
                    
                    <div class="message-actions">
                        <?php if($msg['status'] == 'unread'): ?>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                <input type="hidden" name="status" value="read">
                                <button type="submit" class="btn btn-success">Mark as Read</button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if($msg['status'] != 'replied'): ?>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                <input type="hidden" name="status" value="replied">
                                <button type="submit" class="btn btn-info">Mark as Replied</button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="mailto:<?php echo $msg['email']; ?>?subject=Re: Your Message&body=Hi <?php echo $msg['name']; ?>,%0A%0AThank you for your message." class="btn btn-warning">Reply via Email</a>
                        
                        <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if($totalPages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="alert alert-info" style="background: #cce5ff; color: #004085; border: 1px solid #b3d9ff;">
                No messages found.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>