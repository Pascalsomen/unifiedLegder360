<?php
require_once '../../includes/header.php';


if (!hasRole('accountant')) {
    redirect($base);
}

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = "Invalid account ID.";
    redirect($base_url.'/accounting/chart_of_accounts.php');
}
$parentAccounts = $pdo->query("SELECT * FROM chart_of_accounts WHERE status = TRUE ORDER BY account_code")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id']; // Hidden field in your form
    $accountCode = $_POST['account_code'];
    $accountName = $_POST['account_name'];
    $accountType = $_POST['account_type'];
    $parentAccount = $_POST['parent_account'] ?? null;

    $stmt = $pdo->prepare("UPDATE chart_of_accounts
                          SET account_code = ?, account_name = ?, account_type = ?, parent_id = ?
                          WHERE id = ?");
    $stmt->execute([$accountCode, $accountName, $accountType, $parentAccount, $id]);

    $_SESSION['toast'] = "Account updated successfully!";
    redirect($base_url.'/accounting/chart_of_accounts.php');
}


// Fetch account data
$stmt = $pdo->prepare("SELECT * FROM chart_of_accounts WHERE id = ?");
$stmt->execute([$id]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    $_SESSION['error'] = "Account not found.";
    redirect($base_url.'/accounting/chart_of_accounts.php');
}
?>

<div class="container mt-4">
    <h4>Edit Chart of Account</h4>
    <form action="" method="POST">
        <input type="hidden" name="id" value="<?= $account['id'] ?>">

        <div class="mb-3">
            <label for="account_code" class="form-label">Account Code</label>
            <input type="text" class="form-control" name="account_code" hidden   value="<?= htmlspecialchars($account['account_code']) ?>" required>
            <input type="text" class="form-control" disabled  value="<?= htmlspecialchars($account['account_code']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="account_name" class="form-label">Account Name</label>
            <input type="text" class="form-control" name="account_name" value="<?= htmlspecialchars($account['account_name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="account_type" class="form-label">Account Type</label>
            <select name="account_type" class="form-control" required>
                <option value="asset" <?= $account['account_type'] == 'asset' ? 'selected' : '' ?>>Asset</option>
                <option value="liability" <?= $account['account_type'] == 'liability' ? 'selected' : '' ?>>Liability</option>
                <option value="equity" <?= $account['account_type'] == 'equity' ? 'selected' : '' ?>>Equity</option>
                <option value="revenue" <?= $account['account_type'] == 'revenue' ? 'selected' : '' ?>>Revenue</option>
                <option value="expense" <?= $account['account_type'] == 'expense' ? 'selected' : '' ?>>Expense</option>
                <option value="stock" <?= $account['account_type'] == 'stock' ? 'selected' : '' ?>>Stock</option>
                <option value="finances" <?= $account['account_type'] == 'finances' ? 'selected' : '' ?>>Finances</option>
                <option value="payables" <?= $account['account_type'] == 'payables' ? 'selected' : '' ?>>Payables</option>

                <option value="receivables" <?= $account['account_type'] == 'receivables' ? 'selected' : '' ?>>Receivables</option>
            </select>
        </div>

        <div class="mb-3">
        <label for="parent_account" class="form-label">Parent Account (optional)</label>
    <select name="parent_account" class="form-control">
        <option value="0">-- None --</option>
        <?php foreach ($parentAccounts as $parent): ?>
            <option value="<?= $parent['id'] ?>" <?= $account['parent_id'] == $parent['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($parent['account_code'] . ' - ' . $parent['account_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Account</button>
        <a href="<?php echo $base_url?>/accounting/chart_of_accounts.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
