<?php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
// voucher_template.php

// Set page title dynamically if passed
$pageTitle = $pageTitle ?? 'Voucher Document';
$content = $content ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
            }
        }
        .voucher-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .voucher-logo {
            max-height: 100px;
        }
        .voucher-contact {
            font-size: 14px;
            color: #555;
        }
        .voucher-content {
            margin-top: 30px;
        }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body class="p-5">

    <!-- Print Button -->
    <div class="no-print print-button">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
    </div>

    <!-- Header Section -->
    <div class="voucher-header">
        <img src="assets/logo.png" alt="Logo" class="voucher-logo mb-2">
        <h2><?php echo $settings['system_name_full']?></h2>
        <div class="voucher-contact">
            <p><?php echo $settings['address']?></p>
            <p>Email: <?php echo $settings['email']?>| Phone: <?php echo $settings['phone_number']?></p>
        </div>
    </div>

    <!-- Content Section -->
    <div class="voucher-content">
        <?= $content ?>
    </div>

</body>
</html>
