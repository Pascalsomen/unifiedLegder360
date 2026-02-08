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
                        WHERE is_active = TRUE
                        ORDER BY account_code")->fetchAll();

// Fetch recent journal entries
$entries = $pdo->query("SELECT t.*, u.username as created_by_name
                       FROM transactions t JOIN users u ON t.created_by = u.id
                       ORDER BY t.transaction_date DESC, t.id DESC
                       ")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">


        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>All Journal Entries</h4>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">

                    <table class="table table-bordered" id="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Trx Date</th>
                <th>Refference</th>
                  <th>Transaction Type</th>
                    <th>Descripton</th>
                <th>By</th>

            </tr>
        </thead>
        <tbody>
                        <?php $no=0; foreach ($entries as $entry): ?>

                        <tr>
                            <td><?php echo $no= $no + 1;?></td>
                            <td> <?= date('M j, Y', strtotime($entry['transaction_date'])) ?></td>
                            <td><a href="view_entry.php?id=<?= $entry['id'] ?>"> <?= $entry['reference'] ?>  </a></td>
                               <td> <?= $entry['trx_type'] ?></td>
                             <td> <?= $entry['description'] ?></td>
                            <td><?= $entry['created_by_name'] ?></td>

                        </tr>


                        <?php endforeach; ?>

                        </tbody>
                        <table>
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

        $('#debitTotal').text(debitTotal.toFixed(2));
        $('#creditTotal').text(creditTotal.toFixed(2));

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