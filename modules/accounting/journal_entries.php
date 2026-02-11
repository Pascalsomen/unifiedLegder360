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
            'entry_date' => $_POST['entry_date'],
            'reference' => $_POST['reference'] ?? null,
            'description' => $_POST['description'] ?? null,
            'reference_type' => $_POST['reference_type'] ?? null,
            'reference_id' => $_POST['reference_id'] ?? null,
            'reconciled' => false,
            'created_by' => $_SESSION['user_id']
        ];

        $lines = [];
        foreach ($_POST['lines'] as $index => $line) {
            if (empty($line['account_id']) || (empty($line['debit']) && empty($line['credit']))) {
                continue;
            }

            $lines[] = [
                'account_id' => $line['account_id'],
                'line_description' => $line['line_description'] ?? null,
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0
            ];
        }

        // Create and post the journal entry
        $journalEntryId = $accountingSystem->createJournalEntry($header, $lines);
        $accountingSystem->postJournalEntry($journalEntryId, $_SESSION['user_id']);

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
$entries = $pdo->query("SELECT je.*, u.username as created_by_name
                       FROM journal_entries je
                       JOIN users u ON je.created_by = u.id
                       ORDER BY je.entry_date DESC, je.id DESC
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
                                <input type="date" class="form-control" name="entry_date"
                                       value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reference</label>
                                <input type="text" class="form-control" name="reference"
                                       placeholder="JE-<?= date('Ymdhis') ?>"  value="JE-<?= date('Ymdhis') ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reference Type</label>
                                <select class="form-control" name="reference_type">
                                    <option value="">None</option>
                                    <option value="invoice">Invoice</option>
                                    <option value="purchase_order">Purchase Order</option>
                                    <option value="payment">Payment</option>
                                    <option value="receipt">Receipt</option>
                                    <option value="adjustment">Adjustment</option>
                                </select>
                            </div>
                            <div class="col-md-4 mt-2">
                                <label class="form-label">Reference ID</label>
                                <input type="text" class="form-control" name="reference_id"
                                       placeholder="Optional reference ID">
                            </div>
                            <div class="col-md-8 mt-2">
                                <label class="form-label">Description*</label>
                                <input type="text" class="form-control" name="description" required
                                       placeholder="Enter journal entry description">
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table" id="journalLines">
                                <thead class="table-light">
                                    <tr>
                                        <th width="35%">Account*</th>
                                        <th width="20%">Description</th>
                                        <th width="20%">Debit</th>
                                        <th width="20%">Credit</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Lines will be added dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <th colspan="2" class="text-end">Totals</th>
                                        <th id="debitTotal">0.00</th>
                                        <th id="creditTotal">0.00</th>
                                        <th id="balanceStatus"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                            <?php if(hasPermission(1)): ?>
                                <button type="button" class="btn btn-secondary" id="addLineBtn">
                                    <i class="fas fa-plus"></i> Add Line
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                    <i class="fas fa-save"></i> Post Journal Entry
                                </button>
                            <?php endif; ?>
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
                                <strong><?= htmlspecialchars($entry['reference']) ?></strong>
                                <span class="badge <?= $entry['reconciled'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $entry['reconciled'] ? 'Reconciled' : 'Pending' ?>
                                </span>
                            </div>
                            <div class="text-muted small mb-1">
                                <?= htmlspecialchars($entry['description']) ?>
                            </div>
                            <small class="text-muted">
                                <?= date('M j, Y', strtotime($entry['entry_date'])) ?> •
                                <?= htmlspecialchars($entry['created_by_name']) ?>
                            </small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Quick Help</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-info-circle text-primary me-2"></i>Debits must equal credits</li>
                        <li><i class="fas fa-info-circle text-primary me-2"></i>Each line must have an account</li>
                        <li><i class="fas fa-info-circle text-primary me-2"></i>Reference type is optional</li>
                        <li><i class="fas fa-info-circle text-primary me-2"></i>Minimum 2 lines required</li>
                    </ul>
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

    // Initial lines
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
                                <?= htmlspecialchars($account['account_code']) ?> - <?= htmlspecialchars($account['account_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control line-desc-input"
                           name="lines[${lineCounter}][line_description]"
                           placeholder="Line description (optional)">
                </td>
                <td>
                    <input type="number" class="form-control debit-input"
                           name="lines[${lineCounter}][debit]" step="0.01" min="0" placeholder="0.00">
                </td>
                <td>
                    <input type="number" class="form-control credit-input"
                           name="lines[${lineCounter}][credit]" step="0.01" min="0" placeholder="0.00">
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-line" <?= !hasPermission(1) ? 'disabled' : '' ?>>
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
        if ($('#journalLines tbody tr').length > 2) {
            $(this).closest('tr').remove();
            calculateTotals();
        } else {
            alert('Minimum 2 lines required for a journal entry.');
        }
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
        let validLines = 0;

        $('.debit-input').each(function() {
            const val = parseFloat($(this).val()) || 0;
            debitTotal += val;
            if (val > 0) validLines++;
        });

        $('.credit-input').each(function() {
            const val = parseFloat($(this).val()) || 0;
            creditTotal += val;
            if (val > 0) validLines++;
        });

        $('#debitTotal').text(debitTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        $('#creditTotal').text(creditTotal.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        const difference = Math.abs(debitTotal - creditTotal);
        if (difference < 0.01 && validLines >= 2) {
            $('#balanceStatus').html('<span class="badge bg-success">Balanced</span>');
            $('#submitBtn').prop('disabled', false);
        } else if (difference < 0.01) {
            $('#balanceStatus').html('<span class="badge bg-warning">Add amounts</span>');
            $('#submitBtn').prop('disabled', true);
        } else {
            $('#balanceStatus').html(`<span class="badge bg-danger">Off by ${difference.toFixed(2)}</span>`);
            $('#submitBtn').prop('disabled', true);
        }
    }

    // Enable/disable submit button based on form validity
    function updateSubmitButton() {
        const hasValidLines = $('#journalLines tbody tr').length >= 2;
        let hasAllAccounts = true;

        $('.account-select').each(function() {
            if ($(this).val() === '') {
                hasAllAccounts = false;
            }
        });

        $('#submitBtn').prop('disabled', !(hasValidLines && hasAllAccounts));
        calculateTotals();
    }

    // Check account selections
    $(document).on('change', '.account-select', updateSubmitButton);

    // Form submission validation
    $('#journalForm').submit(function(e) {
        const debitTotal = parseFloat($('#debitTotal').text().replace(/,/g, '')) || 0;
        const creditTotal = parseFloat($('#creditTotal').text().replace(/,/g, '')) || 0;
        const difference = Math.abs(debitTotal - creditTotal);

        if (difference >= 0.01) {
            e.preventDefault();
            alert('Journal entry is not balanced. Debits must equal credits.');
            return false;
        }

        if ($('#journalLines tbody tr').length < 2) {
            e.preventDefault();
            alert('Minimum 2 lines required for a journal entry.');
            return false;
        }

        return true;
    });

    // Initial calculation
    calculateTotals();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>