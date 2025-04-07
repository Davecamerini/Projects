<?php
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail {
    private static $config;
    private $mailer;

    public function __construct() {
        self::$config = require __DIR__ . '/../config/mail.php';
        
        $this->mailer = new PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = self::$config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = self::$config['username'];
        $this->mailer->Password = self::$config['password'];
        $this->mailer->SMTPSecure = self::$config['encryption'];
        $this->mailer->Port = self::$config['port'];
        
        // Default sender
        $this->mailer->setFrom(
            self::$config['from_email'],
            self::$config['from_name']
        );
    }

    /**
     * Send an email
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (can be HTML)
     * @param string $toName Recipient name (optional)
     * @return bool Whether the email was sent successfully
     * @throws Exception If there's an error sending the email
     */
    public function send($to, $subject, $body, $toName = '') {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $toName);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Mail Error: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Send a password reset email
     * 
     * @param string $to Recipient email
     * @param string $username Username
     * @param string $token Reset token
     * @return bool Whether the email was sent successfully
     */
    public function sendPasswordReset($to, $username, $token) {
        $resetLink = "http://{$_SERVER['HTTP_HOST']}/backend/admin/reset-password.php?token=" . $token;
        
        $subject = "Reset Your Password - AIVIP Blog";
        
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #007bff;'>Password Reset Request</h2>
                <p>Hello {$username},</p>
                <p>You have requested to reset your password. Click the button below to reset it:</p>
                <p style='text-align: center;'>
                    <a href='{$resetLink}' 
                       style='display: inline-block; padding: 10px 20px; 
                              background-color: #007bff; color: white; 
                              text-decoration: none; border-radius: 5px;'>
                        Reset Password
                    </a>
                </p>
                <p>Or copy and paste this link in your browser:</p>
                <p style='background: #f8f9fa; padding: 10px; border-radius: 5px;'>
                    {$resetLink}
                </p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='color: #666; font-size: 12px;'>
                    This is an automated email from AIVIP Blog. Please do not reply.
                </p>
            </div>
        </body>
        </html>";
        
        return $this->send($to, $subject, $body, $username);
    }
} 