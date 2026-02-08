<?php require_once __DIR__ . '/includes/header.php';

// Handle logo upload
$logo = '';
if (!empty($_FILES['logo']['name'])) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = time() . '_' . basename($_FILES["logo"]["name"]);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["logo"]["tmp_name"], $targetFile)) {
        $logo = $targetFile;
    }
}

// Get form values
$fields = [
    'system_name_short' => $_POST['system_name_short'],
    'system_name_full' => $_POST['system_name_full'],
    'email'             => $_POST['email'],
    'phone_number'      => $_POST['phone_number'],
    'address'           => $_POST['address'],
    'backup_email'      => $_POST['backup_email'],
    'smtp_host'         => $_POST['smtp_host'],
    'smtp_username'     => $_POST['smtp_username'],
    'smtp_password'     => $_POST['smtp_password'],
    'backup_time'       => $_POST['backup_time'],
    'backup_frequency'  => $_POST['backup_frequency'],
    'timezone'          => $_POST['timezone'],
    'date_format'       => $_POST['date_format']
];

if ($logo) {
    $fields['logo'] = $logo;
}

// Check if settings already exist
$stmt = $pdo->query("SELECT COUNT(*) FROM system_settings");
$exists = $stmt->fetchColumn();

if ($exists) {
    // Update
    $updateFields = [];
    foreach ($fields as $key => $value) {
        $updateFields[] = "`$key` = :$key";
    }

    $sql = "UPDATE system_settings SET " . implode(', ', $updateFields) . " WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($fields);
} else {
    // Insert
    $columns = implode(', ', array_keys($fields));
    $placeholders = ':' . implode(', :', array_keys($fields));
    $sql = "INSERT INTO system_settings ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($fields);
}
$_SESSION['toast'] = "Settings Updated";
echo "<script>window.location ='system.php'</script>";
exit;
