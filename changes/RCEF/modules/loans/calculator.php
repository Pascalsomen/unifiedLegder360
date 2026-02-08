<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasPermission('loans')) {
    redirect('/index.php');
}

// Handle loan calculation
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $interestRate = floatval($_POST['interest_rate']);
    $term = intval($_POST['term']);
    $method = $_POST['method'];

    $monthlyRate = $interestRate / 100 / 12;

    if ($method === 'reducing') {
        // Reducing balance calculation
        $monthlyPayment = $amount * $monthlyRate * pow(1 + $monthlyRate, $term) / (pow(1 + $monthlyRate, $term) - 1);
        $totalPayment = $monthlyPayment * $term;
    } else {
        // Flat rate calculation
        $totalInterest = $amount * ($interestRate / 100) * ($term / 12);
        $monthlyPayment = ($amount + $totalInterest) / $term;
        $totalPayment = $amount + $totalInterest;
    }

    $result = [
        'monthly_payment' => $monthlyPayment,
        'total_payment' => $totalPayment,
        'total_interest' => $totalPayment - $amount
    ];
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Loan Calculator
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="amount">Loan Amount</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" class="form-control" id="amount" name="amount"
                                       step="0.01" min="1" required value="<?= $_POST['amount'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="interest_rate">Interest Rate (%)</label>
                            <input type="number" class="form-control" id="interest_rate" name="interest_rate"
                                   step="0.01" min="0" required value="<?= $_POST['interest_rate'] ?? '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="term">Loan Term (months)</label>
                            <input type="number" class="form-control" id="term" name="term"
                                   min="1" required value="<?= $_POST['term'] ?? '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="method">Interest Method</label>
                            <select class="form-control" id="method" name="method" required>
                                <option value="reducing" <?= ($_POST['method'] ?? '') === 'reducing' ? 'selected' : '' ?>>Reducing Balance</option>
                                <option value="flat" <?= ($_POST['method'] ?? '') === 'flat' ? 'selected' : '' ?>>Flat Rate</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Calculate</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($result): ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    Calculation Results
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th>Monthly Payment</th>
                                <td>$<?= number_format($result['monthly_payment'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Total Payment</th>
                                <td>$<?= number_format($result['total_payment'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Total Interest</th>
                                <td>$<?= number_format($result['total_interest'], 2) ?></td>
                            </tr>
                            <tr>
                                <th>Interest Percentage</th>
                                <td><?= number_format(($result['total_interest'] / ($_POST['amount'] ?? 1)) * 100, 2) ?>%</td>
                            </tr>
                        </table>
                    </div>

                    <h5 class="mt-4">Amortization Schedule</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Payment</th>
                                    <th>Principal</th>
                                    <th>Interest</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $balance = floatval($_POST['amount']);
                                $monthlyRate = floatval($_POST['interest_rate']) / 100 / 12;
                                $term = intval($_POST['term']);
                                $method = $_POST['method'];

                                for ($i = 1; $i <= $term; $i++) {
                                    if ($method === 'reducing') {
                                        $interest = $balance * $monthlyRate;
                                        $principal = $result['monthly_payment'] - $interest;
                                    } else {
                                        $principal = floatval($_POST['amount']) / $term;
                                        $interest = ($result['total_payment'] - floatval($_POST['amount'])) / $term;
                                    }

                                    $balance -= $principal;

                                    // Ensure balance doesn't go below zero due to rounding
                                    if ($i === $term) {
                                        $principal += $balance;
                                        $balance = 0;
                                    }

                                    echo "<tr>
                                        <td>$i</td>
                                        <td>" . number_format($result['monthly_payment'], 2) . "</td>
                                        <td>" . number_format($principal, 2) . "</td>
                                        <td>" . number_format($interest, 2) . "</td>
                                        <td>" . number_format(max(0, $balance), 2) . "</td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>