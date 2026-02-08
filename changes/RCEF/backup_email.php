<?php session_start();
require_once __DIR__ . '/config/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';

$stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
// Paths
$backupPath = __DIR__ . "/backups";
if (!is_dir($backupPath)) mkdir($backupPath);
$backupFile = $backupPath . '/backup_' . date('Y-m-d_H-i-s') . '.sql';

// Run backup
$mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe';
$command = "\"$mysqldumpPath\" --user=$username --host=$host $dbname > \"$backupFile\"";
if ($password !== '') {
    $command = str_replace("--host=$host", "--password=$password --host=$host", $command);
}
exec($command, $output, $result);

if ($result !== 0 || !file_exists($backupFile)) {
    die("❌ Backup failed.");
}

// Email it
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = $settings['smtp_host'];   // SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = $settings['smtp_username'];    // your Gmail
    $mail->Password =  $settings['smtp_password'];      // Gmail App Password
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    // Recipients
    $mail->setFrom($settings['smtp_username'], 'Backup System');
    $mail->addAddress($settings['backup_email'], 'Admin');

    // Attachments
    $mail->addAttachment($backupFile);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'RCEF MySQL Backup - ' . date('Y-m-d H:i:s');
    $mail->Body    = 'Attached is the latest database backup.';

    $mail->send();

    $_SESSION['toast'] = "✅ Backup generated  and sent to backoup email.";
    $link =$_SERVER['HTTP_REFERER'];
    echo "<script>window.location ='http://192.168.1.81/rcef/'</script>";
    exit;
} catch (Exception $e) {
    echo "❌ Mail error: {$mail->ErrorInfo}";
}
?>


<div class="container mt-5">

<div class="row">
<div class="col-md-12">
  <center>  <label>Generating backup, Please wait.........</label><center>
  </div>  </div>  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
