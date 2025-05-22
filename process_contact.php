<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';

class ContactHandler {
    private $conn;
    private $table_name = "contact_messages";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function saveMessage($name, $email, $phone, $message) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     (name, email, phone, message) 
                     VALUES (:name, :email, :phone, :message)";
            
            $stmt = $this->conn->prepare($query);
            
            // Clean input data
            $name = htmlspecialchars(strip_tags($name));
            $email = htmlspecialchars(strip_tags($email));
            $phone = htmlspecialchars(strip_tags($phone));
            $message = htmlspecialchars(strip_tags($message));
            
            // Bind values
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":phone", $phone);
            $stmt->bindParam(":message", $message);
            
            if($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim!',
                    'id' => $this->conn->lastInsertId()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Gagal menyimpan pesan'
            ];
            
        } catch(PDOException $exception) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $exception->getMessage()
            ];
        }
    }

    public function validateInput($data) {
        $errors = [];
        
        // Validate name
        if(empty($data['name']) || strlen($data['name']) > 50) {
            $errors[] = "Nama harus diisi dan maksimal 50 karakter";
        }
        
        // Validate email
        if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 50) {
            $errors[] = "Email tidak valid dan maksimal 50 karakter";
        }
        
        // Validate phone
        if(!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
            $errors[] = "Nomor HP harus angka dan panjang 10-15 digit";
        }
        
        // Validate message
        if(empty($data['message']) || strlen($data['message']) > 200) {
            $errors[] = "Pesan wajib diisi dan maksimal 200 karakter";
        }
        
        return $errors;
    }
}

// Handle POST request
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If not JSON, try regular POST
    if(!$input) {
        $input = $_POST;
    }
    
    if(!empty($input)) {
        $database = new Database();
        $db = $database->getConnection();
        
        if($db) {
            $contactHandler = new ContactHandler($db);
            
            // Validate input
            $validation_errors = $contactHandler->validateInput($input);
            
            if(empty($validation_errors)) {
                $result = $contactHandler->saveMessage(
                    $input['name'],
                    $input['email'], 
                    $input['phone'],
                    $input['message']
                );
                echo json_encode($result);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => implode(', ', $validation_errors)
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Koneksi database gagal'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak lengkap'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method tidak diizinkan'
    ]);
}
?>