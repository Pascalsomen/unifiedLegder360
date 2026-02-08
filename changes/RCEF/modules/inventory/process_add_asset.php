<?php
require_once __DIR__ . '/../../includes/header.php'; // adjust path to your DB connection
require_once __DIR__ . '/../../includes/functions.php'; // optional: for flash messages, redirects, etc.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assetName = trim($_POST['asset_name']);
    $category = trim($_POST['category']);
    $purchaseDate = $_POST['purchase_date'];
    $cost = floatval($_POST['cost']);
    $usefulLife = intval($_POST['useful_life']);
    $salvageValue = floatval($_POST['salvage_value']);
    $depreciationMethod = $_POST['depreciation_method'];

    // Basic validation (extend if needed)
    if ($assetName && $category && $purchaseDate && $cost && $usefulLife && $depreciationMethod) {
        $query = "
            INSERT INTO fixed_assets
                (asset_name, category, purchase_date, cost, useful_life, salvage_value, depreciation_method)
            VALUES
                (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $assetName,
            $category,
            $purchaseDate,
            $cost,
            $usefulLife,
            $salvageValue,
            $depreciationMethod
        ]);

        // Redirect to the asset list page or a confirmation message
        $_SESSION['toast']="Asset Added Successfull";
        echo "<script>window.location='assets.php'</script>";
        exit;
    } else {
        // Redirect with error

        echo "<script>window.location='add_asset.php'</script>";
        exit;
    }
} else {
    // Invalid request
    echo "<script>window.location='add_asset.php'</script>";

}
