<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/AccountingSystem.php';

if (!hasRole('accountant')) {
    redirect($base);
}

$accountingSystem = new AccountingSystem($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $header = [
            'transaction_date' => $_POST['transaction_date'],
            'reference' => $_POST['reference'],
            'description' => $_POST['description'],
            'trx_type'=> $_POST['trx_type'],
            'created_by' => $_SESSION['user_id']
        ];

        $lines = [];
        foreach ($_POST['lines'] as $line) {
            if (empty($line['account_id']) || (empty($line['debit']) && empty($line['credit']))) {
                continue;
            }

            $lines[] = [
                'account_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0
            ];
        }

        // Create and post the journal entry
        $transactionId = $accountingSystem->createJournalEntry($header, $lines);
        $accountingSystem->postJournalEntry($transactionId, $_SESSION['user_id']);

        $_SESSION['toast'] = "Journal entry posted successfully!";
        redirect($base_url.'/accounting/journal_entries.php');
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}


// Fetch accounts for dropdown
$accounts = $pdo->query("SELECT id, account_code, account_name
                        FROM chart_of_accounts
                        WHERE status = TRUE
                        ORDER BY account_code")->fetchAll();

// Fetch recent journal entries
$entries = $pdo->query("SELECT t.*, u.username as created_by_name
                       FROM transactions t JOIN users u ON t.created_by = u.id
                       ORDER BY t.transaction_date DESC, t.id DESC
                       LIMIT 5")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Create Journal Entry</h4>
                </div>
                <div class="card-body">
                    <form id="journalForm" method="POST">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Date*</label>
                                <input type="date" class="form-control" name="transaction_date"
                                       value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reference</label>
                                <input type="text" class="form-control" name="reference"
                                       placeholder="RCEF-<?= date('Ymdhis') ?>"  value="RCEF-<?= date('Ymdhis') ?>" readonly>
                            </div>
                              <div class="col-md-4">
                                <label class="form-label">Type</label>
<select class="form-control" name="trx_type" required>
    <option value="others">Select Transaction type</option>
    <option value="goods">Goods</option>
    <option value="service">Services</option>
    <option value="salaries">salaries</option>
</select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description*</label>
                                <input type="text" class="form-control" name="description" required>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table" id="journalLines">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%">Account*</th>
                                        <th width="25%">Debit</th>
                                        <th width="25%">Credit</th>
                                        <th width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Lines will be added dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <th class="text-end">Totals</th>
                                        <th id="debitTotal">0.00</th>
                                        <th id="creditTotal">0.00</th>
                                        <th id="balanceStatus"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                         <?php if(hasPermission(1)){
                            ?> <button type="button" class="btn btn-secondary" id="addLineBtn">
                            <i class="fas fa-plus"></i> Add Line <?php echo hasPermission(1)?>
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save"></i> Post Journal Entry
                        </button>
                        <?php
                         }  ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Recent Journal Entries</h4>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($entries as $entry): ?>
                        <a href="view_entry.php?id=<?= $entry['id'] ?>"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <strong><?= $entry['reference'] ?></strong>
                                <span><?= number_format($entry['amount'], 2) ?></span>
                            </div>
                            <small class="text-muted">
                                <?= date('M j, Y', strtotime($entry['transaction_date'])) ?> •
                                <?= $entry['created_by_name'] ?>
                            </small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
$(document).ready(function() {
    let lineCounter = 0;

    // Add new journal line
    $('#addLineBtn').click(function() {
        addJournalLine();
    });

    // Initial line
    addJournalLine();
    addJournalLine();

    function addJournalLine() {
        const newLine = `
            <tr id="line-${lineCounter}">
                <td>
                    <select class="form-select account-select" name="lines[${lineCounter}][account_id]" required>
                        <option value="">Select Account</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id'] ?>">
                                <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control debit-input"
                           name="lines[${lineCounter}][debit]" step="0.01" min="0">
                </td>
                <td>
                    <input type="number" class="form-control credit-input"
                           name="lines[${lineCounter}][credit]" step="0.01" min="0">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-line">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#journalLines tbody').append(newLine);
        lineCounter++;
        updateSubmitButton();
    }

    // Remove journal line
    $(document).on('click', '.remove-line', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    // Auto-toggle between debit/credit
    $(document).on('input', '.debit-input, .credit-input', function() {
        const row = $(this).closest('tr');
        const debitInput = row.find('.debit-input');
        const creditInput = row.find('.credit-input');

        if ($(this).hasClass('debit-input') && parseFloat($(this).val()) > 0) {
            creditInput.val('').prop('disabled', true);
        } else if ($(this).hasClass('credit-input') && parseFloat($(this).val()) > 0) {
            debitInput.val('').prop('disabled', true);
        } else {
            debitInput.prop('disabled', false);
            creditInput.prop('disabled', false);
        }

        calculateTotals();
    });

    // Calculate totals
    function calculateTotals() {
        let debitTotal = 0;
        let creditTotal = 0;

        $('.debit-input').each(function() {
            debitTotal += parseFloat($(this).val()) || 0;
        });

        $('.credit-input').each(function() {
            creditTotal += parseFloat($(this).val()) || 0;
        });

$('#debitTotal').text(debitTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
$('#creditTotal').text(creditTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));


        const difference = Math.abs(debitTotal - creditTotal);
        if (difference < 0.01) {
            $('#balanceStatus').html('<span class="badge bg-success">Balanced</span>');
            $('#submitBtn').prop('disabled', false);
        } else {
            $('#balanceStatus').html(`<span class="badge bg-danger">Off by ${difference.toFixed(2)}</span>`);
            $('#submitBtn').prop('disabled', true);
        }
    }

    // Enable/disable submit button
    function updateSubmitButton() {
        const hasValidLines = $('#journalLines tbody tr').length > 1 ||
                             ($('#journalLines tbody tr').length === 1 &&
                              $('#journalLines tbody tr .account-select').val() !== '');
        $('#submitBtn').prop('disabled', !hasValidLines);
    }

    // Initial calculation
    calculateTotals();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>