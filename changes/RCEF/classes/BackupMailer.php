<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // or adjust if you manually include PHPMailer

class BackupMailer
{
    public static function sendBackup($filePath, $toEmail): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.yourhost.com'; // e.g., smtp.gmail.com
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@example.com'; // sender email
            $mail->Password = 'your_password'; // sender email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('pascal@panatechrwanda.com', 'System Backup');
            $mail->addAddress($toEmail);

            // Attachments
            $mail->addAttachment($filePath);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'System Backup - ' . date('Y-m-d H:i');
            $mail->Body    = 'Attached is your system backup generated on ' . date('Y-m-d H:i') . '.';

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Backup Email Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
