<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasRole('accountant')) {
    redirect($base);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountCode = $_POST['account_code'];
    $accountName = $_POST['account_name'];
    $accountType = $_POST['account_type'];
    $parentAccount = $_POST['parent_account'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO chart_of_accounts
                          (account_code, account_name, account_type, parent_account)
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$accountCode, $accountName, $accountType, $parentAccount]);

    $_SESSION['toast'] = "Account added successfully!";
    redirect($base_url.'/accounting/chart_of_accounts.php');
}

// Fetch accounts
$accounts = $pdo->query("SELECT * FROM chart_of_accounts WHERE is_active = TRUE ORDER BY account_code")->fetchAll();
?>

<div class="container mt-5">
    <h2>Chart of Accounts</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            Add New Account
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="account_code">Account Code</label>
                            <input type="text" class="form-control" id="account_code" name="account_code"   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="account_name">Account Name</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="account_type">Account Type</label>
                            <select class="form-control" id="account_type" name="account_type" required>
                                <option value="asset">Asset</option>
                                <option value="liability">Liability</option>
                                <option value="equity">Equity</option>
                                <option value="revenue">Revenue</option>
                                <option value="expense">Expense</option>
                                <option value="stock">stock</option>
                                <option value="finances">Finances</option>
                                 <option value="payables">Payables</option>
                                <option value="receivables">Receivables</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="parent_account">Parent Account</label>
                            <select class="form-control" id="parent_account" name="parent_account">
                                <option value="">None</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"><?= $account['account_code'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Add Account</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Account List   <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Chart of Accounts')">Export to Excel</button>

        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Parent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td><?= $account['account_code'] ?></td>
                            <td><?= $account['account_name'] ?></td>
                            <td><?= ucfirst($account['account_type']) ?></td>
                            <td>
                                <?php
                                if ($account['parent_account']) {
                                    $parent = $pdo->query("SELECT account_code FROM chart_of_accounts WHERE id = {$account['parent_account']}")->fetch();
                                    echo $parent['account_code'];
                                }
                                ?>
                            </td>
                            <td>
                                <a href="edit_account.php?id=<?= $account['id'] ?>" class="btn btn-sm btn-warning">Edit</a>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>