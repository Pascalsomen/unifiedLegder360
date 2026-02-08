<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/AccountingSystem.php';

if (!hasRole('accountant')) {
    redirect($base);
}

$accountingSystem = new AccountingSystem($pdo);

// Get entry ID
$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = "Missing journal entry ID.";
    redirect($base_url . '/accounting/journal_entries.php');
}

// Fetch entry and lines
$entry = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
$entry->execute([$id]);
$entry = $entry->fetch();

if (!$entry) {
    $_SESSION['error'] = "Journal entry not found.";
    redirect($base_url . '/accounting/journal_entries.php');
}

$lines = $pdo->prepare("SELECT * FROM transaction_lines WHERE transaction_id = ?");
$lines->execute([$id]);
$lines = $lines->fetchAll();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $header = [
            'transaction_date' => $_POST['transaction_date'],
            'reference' => $_POST['reference'],
            'description' => $_POST['description'],
            'trx_type' => $_POST['trx_type'],
            'updated_by' => $_SESSION['user_id']
        ];

        $newLines = [];
        foreach ($_POST['lines'] as $line) {
            if (empty($line['account_id']) || (empty($line['debit']) && empty($line['credit']))) {
                continue;
            }
            $newLines[] = [
                'account_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0
            ];
        }

        $accountingSystem->updateJournalEntry($id, $header, $newLines);

        $_SESSION['toast'] = "Journal entry updated successfully.";
        redirect($base_url . '/accounting/journal_entries.php');
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Fetch accounts
$accounts = $pdo->query("SELECT id, account_code, account_name FROM chart_of_accounts WHERE is_active = 1 ORDER BY account_code")->fetchAll();
?>

<div class="container">
    <h3>Edit Journal Entry</h3>
    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Date*</label>
                <input type="date" name="transaction_date" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= $entry['transaction_date'] ?>" required>
            </div>
            <div class="col-md-4">
                <label>Reference*</label>
                <input type="text" name="reference" class="form-control" value="<?= $entry['reference'] ?>" required readonly>
            </div>
            <div class="col-md-4">
                <label>Type*</label>
                <select name="trx_type" class="form-control" required>
                    <option value="others">Select Transaction Type</option>
                    <option value="goods" <?= $entry['trx_type'] == 'goods' ? 'selected' : '' ?>>Goods</option>
                    <option value="service" <?= $entry['trx_type'] == 'service' ? 'selected' : '' ?>>Services</option>
                    <option value="salaries" <?= $entry['trx_type'] == 'salaries' ? 'selected' : '' ?>>Salaries</option>
                  
                </select>
            </div>
            <div class="col-md-12 mt-2">
                <label>Description*</label>
                <input type="text" name="description" class="form-control" value="<?= $entry['description'] ?>" required>
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table" id="journalLines">
                <thead class="table-light">
                    <tr>
                        <th>Account*</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lines as $i => $line): ?>
                        <tr>
                            <td>
                                <select name="lines[<?= $i ?>][account_id]" class="form-select" required>
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?= $acc['id'] ?>" <?= $acc['id'] == $line['account_id'] ? 'selected' : '' ?>>
                                            <?= $acc['account_code'] ?> - <?= $acc['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="lines[<?= $i ?>][debit]" step="0.01" class="form-control" value="<?= $line['debit'] ?>"></td>
                            <td><input type="number" name="lines[<?= $i ?>][credit]" step="0.01" class="form-control" value="<?= $line['credit'] ?>"></td>
                            <td><button type="button" class="btn btn-danger remove-line"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="button" id="addLineBtn" class="btn btn-secondary mb-3">Add Line</button>
        <button type="submit" class="btn btn-primary">Update Entry</button>
    </form>
</div>

<script>
let lineIndex = <?= count($lines) ?>;

$('#addLineBtn').on('click', function () {
    const row = `
        <tr>
            <td>
                <select name="lines[${lineIndex}][account_id]" class="form-select" required>
                    <option value="">Select Account</option>
                    <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>"><?= $acc['account_code'] ?> - <?= $acc['account_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="number" name="lines[${lineIndex}][debit]" step="0.01" class="form-control"></td>
            <td><input type="number" name="lines[${lineIndex}][credit]" step="0.01" class="form-control"></td>
            <td><button type="button" class="btn btn-danger remove-line"><i class="fas fa-trash-alt"></i></button></td>
        </tr>`;
    $('#journalLines tbody').append(row);
    lineIndex++;
});

$(document).on('click', '.remove-line', function () {
    $(this).closest('tr').remove();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
