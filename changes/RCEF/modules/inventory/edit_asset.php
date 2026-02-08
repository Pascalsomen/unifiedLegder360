<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isset($_GET['id'])) {
    echo "Asset ID not specified.";
    exit;
}

$assetId = $_GET['id'];

// Fetch asset details
$stmt = $pdo->prepare("SELECT * FROM fixed_assets WHERE id = ?");
$stmt->execute([$assetId]);
$asset = $stmt->fetch();

if (!$asset) {
    echo "Asset not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assetName = trim($_POST['asset_name']);
    $category = trim($_POST['category']);
    $purchaseDate = $_POST['purchase_date'];
    $cost = floatval($_POST['cost']);
    $usefulLife = intval($_POST['useful_life']);
    $salvageValue = floatval($_POST['salvage_value']);
    $depreciationMethod = $_POST['depreciation_method'];

    if ($assetName && $category && $purchaseDate && $cost && $usefulLife && $depreciationMethod) {
        $updateQuery = "
            UPDATE fixed_assets
            SET asset_name = ?, category = ?, purchase_date = ?, cost = ?, useful_life = ?, salvage_value = ?, depreciation_method = ?
            WHERE id = ?
        ";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([
            $assetName,
            $category,
            $purchaseDate,
            $cost,
            $usefulLife,
            $salvageValue,
            $depreciationMethod,
            $assetId
        ]);

        $_SESSION['toast']="Asset Edit Successfull";
        echo "<script>window.location='assets.php'</script>";
        exit;
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
    }
}
?>

<div class="container mt-4">
    <h4>Edit Asset</h4>
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <label>Asset Name</label>
                <input type="text" name="asset_name" class="form-control" value="<?= htmlspecialchars($asset['asset_name']) ?>" required>

                <label>Category</label>
                <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($asset['category']) ?>" required>

                <label>Purchase Date</label>
                <input type="date" name="purchase_date" class="form-control" value="<?= $asset['purchase_date'] ?>" required>

                <label>Cost</label>
                <input type="number" step="0.01" name="cost" class="form-control" value="<?= $asset['cost'] ?>" required>

                <label>Useful Life (years)</label>
                <input type="number" name="useful_life" class="form-control" value="<?= $asset['useful_life'] ?>" required>

                <label>Salvage Value</label>
                <input type="number" step="0.01" name="salvage_value" class="form-control" value="<?= $asset['salvage_value'] ?>">

                <label>Depreciation Method</label>
                <select name="depreciation_method" class="form-control" required>
                    <option value="">-- Select Method --</option>
                    <option value="straight_line" <?= $asset['depreciation_method'] === 'straight_line' ? 'selected' : '' ?>>Straight Line</option>
                    <option value="declining_balance" <?= $asset['depreciation_method'] === 'declining_balance' ? 'selected' : '' ?>>Declining Balance</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Update Asset</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
