<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasPermission('loans')) {
    redirect('/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantName = $_POST['applicant_name'];
    $applicantId = $_POST['applicant_id'];
    $productId = $_POST['product_id'];
    $amount = $_POST['amount'];
    $purpose = $_POST['purpose'];
    $collateral = $_POST['collateral'];
    $term = $_POST['term'];

    try {
        $pdo->beginTransaction();

        // Create application
        $stmt = $pdo->prepare("INSERT INTO loan_applications
                              (applicant_name, applicant_id, product_id, amount, purpose, term, status)
                              VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$applicantName, $applicantId, $productId, $amount, $purpose, $term]);
        $applicationId = $pdo->lastInsertId();

        // Add collateral if provided
        if (!empty($collateral)) {
            $stmt = $pdo->prepare("INSERT INTO loan_collateral
                                  (loan_account_id, description, value, collateral_type)
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([$applicationId, $collateral['description'], $collateral['value'], $collateral['type']]);
        }

        $pdo->commit();

        $_SESSION['success'] = "Loan application submitted successfully!";
        redirect("/modules/loans/application.php?id=$applicationId");
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Application failed: " . $e->getMessage();
    }
}

// Fetch loan products
$products = $pdo->query("SELECT * FROM loan_products WHERE is_active = TRUE")->fetchAll();
?>

<div class="container-fluid">
    <h2>New Loan Application</h2>

    <div class="card">
        <div class="card-header">
            Applicant Information
        </div>
        <div class="card-body">
            <form method="POST" id="loanApplicationForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Applicant Name*</label>
                            <input type="text" class="form-control" name="applicant_name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Applicant ID*</label>
                            <input type="text" class="form-control" name="applicant_id" required>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Loan Product*</label>
                            <select class="form-control" name="product_id" id="productSelect" required>
                                <option value="">-- Select Product --</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>"
                                        data-rate="<?= $product['interest_rate'] ?>"
                                        data-method="<?= $product['interest_method'] ?>"
                                        data-term="<?= $product['repayment_period'] ?>">
                                        <?= $product['name'] ?> (<?= $product['interest_rate'] ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Loan Amount*</label>
                            <input type="number" class="form-control" name="amount" id="loanAmount"
                                   step="0.01" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Term (months)*</label>
                            <input type="number" class="form-control" name="term" id="loanTerm"
                                   min="1" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>Purpose of Loan*</label>
                    <textarea class="form-control" name="purpose" rows="3" required></textarea>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Collateral Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Collateral Type</label>
                                    <select class="form-control" name="collateral[type]">
                                        <option value="">-- None --</option>
                                        <option value="real_estate">Real Estate</option>
                                        <option value="vehicle">Vehicle</option>
                                        <option value="equipment">Equipment</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" class="form-control" name="collateral[description]">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Estimated Value</label>
                                    <input type="number" class="form-control" name="collateral[value]"
                                           step="0.01" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Loan Summary</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Interest Rate</th>
                                        <td id="interestRateDisplay">0%</td>
                                    </tr>
                                    <tr>
                                        <th>Interest Method</th>
                                        <td id="interestMethodDisplay">-</td>
                                    </tr>
                                    <tr>
                                        <th>Monthly Payment</th>
                                        <td id="monthlyPaymentDisplay">-</td>
                                    </tr>
                                    <tr>
                                        <th>Total Repayment</th>
                                        <td id="totalRepaymentDisplay">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Required Documents</h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>National ID/Passport</li>
                                    <li>Proof of Income</li>
                                    <li>Collateral Documentation</li>
                                    <li>Bank Statements (3 months)</li>
                                </ul>
                                <div class="form-group mt-3">
                                    <label>Upload Documents</label>
                                    <input type="file" class="form-control-file" multiple>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update loan terms when product changes
    $('#productSelect').change(function() {
        const selectedOption = $(this).find('option:selected');
        const rate = selectedOption.data('rate');
        const method = selectedOption.data('method');
        const term = selectedOption.data('term');

        $('#interestRateDisplay').text(rate + '%');
        $('#interestMethodDisplay').text(method === 'flat' ? 'Flat Rate' : 'Reducing Balance');
        $('#loanTerm').val(term);

        calculateLoanRepayment();
    });

    // Calculate repayment when amount or term changes
    $('#loanAmount, #loanTerm').on('input', calculateLoanRepayment);

    function calculateLoanRepayment() {
        const amount = parseFloat($('#loanAmount').val()) || 0;
        const term = parseInt($('#loanTerm').val()) || 1;
        const rate = parseFloat($('#productSelect').find('option:selected').data('rate')) || 0;
        const method = $('#productSelect').find('option:selected').data('method');

        if (amount > 0 && term > 0 && rate > 0) {
            const monthlyRate = rate / 100 / 12;

            if (method === 'flat') {
                // Flat interest calculation
                const totalInterest = amount * (rate / 100) * (term / 12);
                const monthlyPayment = (amount + totalInterest) / term;
                const totalRepayment = amount + totalInterest;

                $('#monthlyPaymentDisplay').text(monthlyPayment.toFixed(2));
                $('#totalRepaymentDisplay').text(totalRepayment.toFixed(2));
            } else {
                // Reducing balance calculation
                const monthlyPayment = amount * monthlyRate * Math.pow(1 + monthlyRate, term) /
                                     (Math.pow(1 + monthlyRate, term) - 1);
                const totalRepayment = monthlyPayment * term;

                $('#monthlyPaymentDisplay').text(monthlyPayment.toFixed(2));
                $('#totalRepaymentDisplay').text(totalRepayment.toFixed(2));
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>