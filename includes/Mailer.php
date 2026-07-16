<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/libs/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/models/Setting.php';

class Mailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        $db = new Database();
        $conn = $db->getConnection();
        $settingModel = new Setting($conn);
        $settings = $settingModel->getAll();

        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = !empty($settings['smtp_host']) ? $settings['smtp_host'] : ($_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = !empty($settings['smtp_user']) ? $settings['smtp_user'] : ($_ENV['SMTP_USER'] ?? '');
            $this->mail->Password   = !empty($settings['smtp_pass']) ? $settings['smtp_pass'] : ($_ENV['SMTP_PASS'] ?? '');
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port       = !empty($settings['smtp_port']) ? $settings['smtp_port'] : ($_ENV['SMTP_PORT'] ?? 587);

            // Sender
            $fromEmail = !empty($settings['smtp_from_email']) ? $settings['smtp_from_email'] : ($_ENV['SMTP_FROM_EMAIL'] ?? 'no-reply@docmarly.com');
            $fromName  = !empty($settings['smtp_from_name']) ? $settings['smtp_from_name'] : ($_ENV['SMTP_FROM_NAME'] ?? 'Doc Marly SQMS');
            $this->mail->setFrom($fromEmail, $fromName);
            
            $this->mail->isHTML(true);
        } catch (Exception $e) {
            // Initialization error
        }
    }

    public function sendWelcomeEmail($toEmail, $name, $username, $setupLink) {
        if (empty($toEmail) || empty($this->mail->Username)) {
            return false; // Cannot send without valid email or configured SMTP
        }

        try {
            $this->mail->addAddress($toEmail);
            $this->mail->Subject = 'Welcome to Doc Marly SQMS - Setup Your Account';
            $this->mail->Body    = "
                <h3>Welcome to Doc Marly SQMS, " . htmlspecialchars($name ?? '') . "!</h3>
                <p>An account has been created for you. Your assigned username is: <strong>" . htmlspecialchars($username ?? '') . "</strong></p>
                <p>To securely set up your password and access your account, please click the link below:</p>
                <p><a href=\"" . htmlspecialchars($setupLink ?? '') . "\" style=\"padding: 10px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 4px;\">Set Up Password</a></p>
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <p>" . htmlspecialchars($setupLink ?? '') . "</p>
                <p>This link will expire in 24 hours.</p>
                <p>Best Regards,<br>The Doc Marly Team</p>
            ";
            $this->mail->AltBody = "Welcome to Doc Marly SQMS, $name!\n\nAn account has been created for you. Your assigned username is: $username\n\nTo securely set up your password and access your account, please visit the following link:\n\n$setupLink\n\nThis link will expire in 24 hours.\n\nBest Regards,\nThe Doc Marly Team";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    public function sendOTPEmail($toEmail, $name, $otpCode) {
        if (empty($toEmail) || empty($this->mail->Username)) {
            return false;
        }

        try {
            $this->mail->clearAddresses(); // Clear previous addresses if object is reused
            $this->mail->addAddress($toEmail);
            $this->mail->Subject = 'Doc Marly SQMS - Login Verification Code';
            
            $this->mail->Body = "
                <h3>Hello " . htmlspecialchars($name ?? '') . ",</h3>
                <p>Your one-time verification code is:</p>
                <h2 style=\"font-size: 24px; letter-spacing: 5px; color: #007bff; background: #f0f8ff; padding: 15px; display: inline-block; border-radius: 5px;\">" . htmlspecialchars($otpCode ?? '') . "</h2>
                <p>This code will expire in 5 minutes.</p>
                <p>If you did not attempt to log in, please secure your account immediately.</p>
                <p>Best Regards,<br>The Doc Marly Team</p>
            ";
            
            $this->mail->AltBody = "Hello $name,\n\nYour one-time verification code is: $otpCode\n\nThis code will expire in 5 minutes.\n\nBest Regards,\nThe Doc Marly Team";

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("OTP could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
?>
