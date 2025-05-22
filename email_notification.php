<?php
/**
 * Email Notification System
 * Mengirim notifikasi email ketika ada pesan baru dari contact form
 */

class EmailNotification {
    private $adminEmail;
    private $siteUrl;
    private $siteName;
    
    public function __construct() {
        // Konfigurasi email - sesuaikan dengan kebutuhan
        $this->adminEmail = 'your-email@example.com'; // Ganti dengan email admin
        $this->siteUrl = 'https://yourwebsite.com';    // Ganti dengan URL website
        $this->siteName = 'Portfolio Website';          // Nama website
    }
    
    /**
     * Mengirim notifikasi ke admin ketika ada pesan baru
     */
    public function sendNewMessageNotification($messageData) {
        $to = $this->adminEmail;
        $subject = '[' . $this->siteName . '] New Contact Message from ' . $messageData['name'];
        
        $message = $this->createNewMessageTemplate($messageData);
        $headers = $this->getEmailHeaders();
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Mengirim konfirmasi ke pengirim pesan
     */
    public function sendConfirmationToSender($messageData) {
        $to = $messageData['email'];
        $subject = 'Thank you for contacting us - ' . $this->siteName;
        
        $message = $this->createConfirmationTemplate($messageData);
        $headers = $this->getEmailHeaders();
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Template email untuk notifikasi admin
     */
    private function createNewMessageTemplate($data) {
        $template = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 20px; }
        .message-details { background: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .message-content { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #666; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>New Contact Message</h1>
            <p>You have received a new message from your portfolio website</p>
        </div>
        
        <div class='content'>
            <div class='message-details'>
                <h3>Contact Information:</h3>
                <p><strong>Name:</strong> {$data['name']}</p>
                <p><strong>Email:</strong> {$data['email']}</p>
                <p><strong>Phone:</strong> {$data['phone']}</p>
                <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
            </div>
            
            <div class='message-content'>
                <h3>Message:</h3>
                <p>" . nl2br(htmlspecialchars($data['message'])) . "</p>
            </div>
            
            <div style='text-align: center; margin: 20px 0;'>
                <a href='{$this->siteUrl}/admin/view_messages.php' class='btn'>View in Admin Panel</a>
            </div>
        </div>
        
        <div class='footer'>
            <p>This email was sent automatically from {$this->siteName}</p>
            <p><a href='{$this->siteUrl}'>{$this->siteUrl}</a></p>
        </div>
    </div>
</body>
</html>";
        
        return $template;
    }
    
    /**
     * Template email konfirmasi untuk pengirim
     */
    private function createConfirmationTemplate($data) {
        $template = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 20px; }
        .message-summary { background: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Thank You!</h1>
            <p>Your message has been received successfully</p>
        </div>
        
        <div class='content'>
            <p>Hi {$data['name']},</p>
            
            <p>Thank you for contacting us through our portfolio website. We have successfully received your message and will get back to you as soon as possible.</p>
            
            <div class='message-summary'>
                <h3>Your Message Summary:</h3>
                <p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>
                <p><strong>Subject:</strong> Contact Form Inquiry</p>
                <p><strong>Your Email:</strong> {$data['email']}</p>
                <p><strong>Message Preview:</strong> " . substr(htmlspecialchars($data['message']), 0, 100) . "...</p>
            </div>
            
            <p>We typically respond within 24-48 hours during business days. If your inquiry is urgent, please feel free to call us directly.</p>
            
            <p>Best regards,<br>
            The {$this->siteName} Team</p>
        </div>
        
        <div class='footer'>
            <p>This is an automated confirmation email from {$this->siteName}</p>
            <p><a href='{$this->siteUrl}'>{$this->siteUrl}</a></p>
        </div>
    </div>
</body>
</html>";
        
        return $template;
    }
    
    /**
     * Headers untuk email HTML
     */
    private function getEmailHeaders() {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->siteName . " <noreply@" . parse_url($this->siteUrl, PHP_URL_HOST) . ">" . "\r\n";
        $headers .= "Reply-To: " . $this->adminEmail . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return $headers;
    }
    
    /**
     * Mengirim email dengan template sederhana (fallback)
     */
    public function sendSimpleNotification($messageData) {
        $to = $this->adminEmail;
        $subject = 'New Contact Message from ' . $messageData['name'];
        
        $message = "You have received a new contact message:\n\n";
        $message .= "Name: " . $messageData['name'] . "\n";
        $message .= "Email: " . $messageData['email'] . "\n";
        $message .= "Phone: " . $messageData['phone'] . "\n";
        $message .= "Date: " . date('Y-m-d H:i:s') . "\n\n";
        $message .= "Message:\n" . $messageData['message'] . "\n\n";
        $message .= "---\n";
        $message .= "Sent from: " . $this->siteUrl;
        
        $headers = "From: " . $this->siteName . " <noreply@" . parse_url($this->siteUrl, PHP_URL_HOST) . ">";
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Test email configuration
     */
    public function testEmailConfiguration() {
        $testData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'message' => 'This is a test message to verify email configuration.'
        ];
        
        return $this->sendSimpleNotification($testData);
    }
}

// Usage example and testing
if(isset($_GET['test_email']) && $_GET['test_email'] == '1') {
    $emailNotifier = new EmailNotification();
    $result = $emailNotifier->testEmailConfiguration();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'Test email sent successfully!' : 'Failed to send test email'
    ]);
    exit;
}

// Integration example - add this to your process_contact.php
/*
// After successfully saving to database:
if($result['success']) {
    $emailNotifier = new EmailNotification();
    
    // Send notification to admin
    $emailNotifier->sendNewMessageNotification($input);
    
    // Send confirmation to sender (optional)
    $emailNotifier->sendConfirmationToSender($input);
}
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration - Portfolio</title>
    <style>
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
        .config-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn-test { background: #28a745; }
        .code-block {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Notification Configuration</h1>
        
        <div class="config-section">
            <h3>📧 Current Configuration</h3>
            <p><strong>Admin Email:</strong> your-email@example.com (⚠️ Please update this)</p>
            <p><strong>Site URL:</strong> https://yourwebsite.com (⚠️ Please update this)</p>
            <p><strong>Site Name:</strong> Portfolio Website</p>
        </div>
        
        <div class="config-section">
            <h3>🔧 Setup Instructions</h3>
            <ol>
                <li>Edit the EmailNotification class constructor</li>
                <li>Update $adminEmail with your actual email</li>
                <li>Update $siteUrl with your website URL</li>
                <li>Update $siteName with your preferred site name</li>
                <li>Test email functionality using the button below</li>
            </ol>
        </div>
        
        <div class="config-section">
            <h3>🧪 Test Email System</h3>
            <button onclick="testEmail()" class="btn btn-test">Send Test Email</button>
            <div id="testResult" style="margin-top: 10px;"></div>
        </div>
        
        <div class="config-section">
            <h3>🔗 Integration Code</h3>
            <p>Add this code to your process_contact.php after successful database save:</p>
            <div class="code-block">
// Include email notification<br>
require_once 'email_notification.php';<br><br>

// After successful save to database<br>
if($result['success']) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;$emailNotifier = new EmailNotification();<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Send notification to admin<br>
&nbsp;&nbsp;&nbsp;&nbsp;$emailNotifier->sendNewMessageNotification($input);<br>
&nbsp;&nbsp;&nbsp;&nbsp;<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Send confirmation to sender (optional)<br>
&nbsp;&nbsp;&nbsp;&nbsp;$emailNotifier->sendConfirmationToSender($input);<br>
}
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="admin/view_messages.php" class="btn">📊 View Messages</a>
            <a href="test_connection.php" class="btn">🔧 Test Connection</a>
            <a href="index.html" class="btn">🏠 Portfolio</a>
        </div>
    </div>
    
    <script>
        function testEmail() {
            const button = event.target;
            const resultDiv = document.getElementById('testResult');
            
            button.textContent = 'Sending...';
            button.disabled = true;
            
            fetch('?test_email=1')
                .then(response => response.json())
                .then(data => {
                    resultDiv.innerHTML = '<div style="padding: 10px; border-radius: 5px; background: ' + 
                        (data.success ? '#d4edda; color: #155724' : '#f8d7da; color: #721c24') + 
                        ';">' + data.message + '</div>';
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div style="padding: 10px; border-radius: 5px; background: #f8d7da; color: #721c24;">Error: ' + error + '</div>';
                })
                .finally(() => {
                    button.textContent = 'Send Test Email';
                    button.disabled = false;
                });
        }
    </script>
</body>
</html>