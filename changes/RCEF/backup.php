<?php
$host = 'localhost';
$username = 'root'; // XAMPP default
$password = '';     // XAMPP default
$database = 'rcef';

// Output path (change to your desired folder)
$backupPath = __DIR__ . "/backups";
if (!is_dir($backupPath)) mkdir($backupPath);

$backupFile = $backupPath . '/backup_' . date('Y-m-d_H-i-s') . '.sql';

// XAMPP's mysqldump path
$mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe';

$command = "\"$mysqldumpPath\" --user=$username --host=$host $database > \"$backupFile\"";

if ($password !== '') {
    $command = str_replace("--host=$host", "--password=$password --host=$host", $command);
}

exec($command, $output, $result);

if ($result === 0) {
    echo "Backup successful: $backupFile";
} else {
    echo "Backup failed.";
}
?>
