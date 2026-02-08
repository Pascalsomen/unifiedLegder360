<?php
// payroll_settings.php
require_once __DIR__ . '/../../includes/header.php';

if (!hasRole('accountant') && !hasRole('admin')) {
    redirect($base);
}

// Fetch accounts for dropdowns
$accounts = $pdo->query("SELECT id, account_code, account_name, account_type
                         FROM chart_of_accounts
                         WHERE status = TRUE
                         ORDER BY account_code")->fetchAll();

// Fetch existing payroll settings
$settingsQuery = $pdo->query("SELECT * FROM payroll_settings");
$payrollSettings = $settingsQuery->fetchAll(PDO::FETCH_KEY_PAIR);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Clear existing settings
        $pdo->query("DELETE FROM payroll_settings");

        // Insert new settings
        $stmt = $pdo->prepare("INSERT INTO payroll_settings (setting_key, setting_value) VALUES (?, ?)");

        // Basic Payroll Settings
        $settingsToSave = [
            // Salary Accounts
            'salary_account_id' => $_POST['salary_account_id'] ?? null,
            'allowance_account_id' => $_POST['allowance_account_id'] ?? null,
            'bonus_account_id' => $_POST['bonus_account_id'] ?? null,
            'overtime_account_id' => $_POST['overtime_account_id'] ?? null,

            // Deduction Accounts
            'tax_account_id' => $_POST['tax_account_id'] ?? null,
            'ssf_account_id' => $_POST['ssf_account_id'] ?? null,
            'loan_account_id' => $_POST['loan_account_id'] ?? null,
            'advance_account_id' => $_POST['advance_account_id'] ?? null,
            'other_deduction_account_id' => $_POST['other_deduction_account_id'] ?? null,

            // Payment Account
            'payment_account_id' => $_POST['payment_account_id'] ?? null,

            // Tax Settings
            'tax_threshold' => $_POST['tax_threshold'] ?? 0,
            'tax_rate' => $_POST['tax_rate'] ?? 0,
            'annual_tax_free' => $_POST['annual_tax_free'] ?? 0,

            // SSF Settings
            'ssf_employee_rate' => $_POST['ssf_employee_rate'] ?? 0,
            'ssf_employer_rate' => $_POST['ssf_employer_rate'] ?? 0,
            'ssf_max_salary' => $_POST['ssf_max_salary'] ?? 0,

            // Calculation Formulas
            'overtime_rate' => $_POST['overtime_rate'] ?? 1.5,
            'night_shift_allowance' => $_POST['night_shift_allowance'] ?? 0,
            'transport_allowance_formula' => $_POST['transport_allowance_formula'] ?? '',
            'housing_allowance_formula' => $_POST['housing_allowance_formula'] ?? '',
            'medical_allowance_formula' => $_POST['medical_allowance_formula'] ?? '',

            // Leave Settings
            'annual_leave_rate' => $_POST['annual_leave_rate'] ?? 0,
            'sick_leave_rate' => $_POST['sick_leave_rate'] ?? 0,
            'maternity_leave_rate' => $_POST['maternity_leave_rate'] ?? 0,

            // Payroll Settings
            'payroll_month' => $_POST['payroll_month'] ?? 'current',
            'payment_date' => $_POST['payment_date'] ?? 25,
            'salary_rounding' => $_POST['salary_rounding'] ?? 'nearest',
            'currency_id' => $_POST['currency_id'] ?? 1,

            // Additional Settings
            'enable_automatic_tax' => isset($_POST['enable_automatic_tax']) ? 1 : 0,
            'enable_ssf' => isset($_POST['enable_ssf']) ? 1 : 0,
            'enable_loan_deduction' => isset($_POST['enable_loan_deduction']) ? 1 : 0,
            'enable_advance_deduction' => isset($_POST['enable_advance_deduction']) ? 1 : 0,
            'print_payslip' => isset($_POST['print_payslip']) ? 1 : 0,
            'email_payslip' => isset($_POST['email_payslip']) ? 1 : 0,
        ];

        foreach ($settingsToSave as $key => $value) {
            $stmt->execute([$key, $value]);
        }

        // Save deduction formulas
        if (isset($_POST['deduction_name']) && is_array($_POST['deduction_name'])) {
            // Clear existing formulas
            $pdo->query("DELETE FROM payroll_deduction_formulas");

            $formulaStmt = $pdo->prepare("INSERT INTO payroll_deduction_formulas
                (deduction_name, calculation_type, formula, account_id, is_active)
                VALUES (?, ?, ?, ?, ?)");

            foreach ($_POST['deduction_name'] as $index => $name) {
                $formulaStmt->execute([
                    $name,
                    $_POST['calculation_type'][$index] ?? 'fixed',
                    $_POST['formula'][$index] ?? '',
                    $_POST['formula_account_id'][$index] ?? null,
                    isset($_POST['formula_active'][$index]) ? 1 : 0
                ]);
            }
        }

        $pdo->commit();

        $_SESSION['toast'] = "Payroll settings saved successfully!";
        redirect($base_url . '/accounting/payroll_settings.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error saving settings: " . $e->getMessage();
    }
}

// Fetch deduction formulas
$deductionFormulas = $pdo->query("SELECT pdf.*, ca.account_code, ca.account_name
                                  FROM payroll_deduction_formulas pdf
                                  LEFT JOIN chart_of_accounts ca ON pdf.account_id = ca.id
                                  ORDER BY pdf.id")->fetchAll();

// Fetch currencies
$currencies = $pdo->query("SELECT id, code, name FROM currencies WHERE status = 1 ORDER BY id")->fetchAll();
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Payroll Settings</h2>
            <p class="text-muted mb-0">Configure payroll accounts, tax rates, and deduction formulas</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="saveSettings()">
                <i class="fas fa-save me-2"></i>Save Settings
            </button>
            <button type="button" class="btn btn-secondary ms-2" onclick="resetToDefaults()">
                <i class="fas fa-undo me-2"></i>Reset Defaults
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['toast'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['toast'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Main Form -->
    <form id="payrollSettingsForm" method="POST">
        <div class="row">
            <!-- Left Column - Account Settings -->
            <div class="col-lg-6">
                <!-- Salary & Allowance Accounts -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Salary & Allowance Accounts</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="salary_account_id" class="form-label">Basic Salary Account *</label>
                                <select class="form-select" id="salary_account_id" name="salary_account_id" required>
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['salary_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?> (<?= $account['account_type'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="allowance_account_id" class="form-label">Allowance Account</label>
                                <select class="form-select" id="allowance_account_id" name="allowance_account_id">
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['allowance_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="bonus_account_id" class="form-label">Bonus Account</label>
                                <select class="form-select" id="bonus_account_id" name="bonus_account_id">
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['bonus_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="overtime_account_id" class="form-label">Overtime Account</label>
                                <select class="form-select" id="overtime_account_id" name="overtime_account_id">
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['overtime_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tax & SSF Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Tax & SSF Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tax_account_id" class="form-label">Tax Payable Account</label>
                                <select class="form-select" id="tax_account_id" name="tax_account_id">
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['tax_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="ssf_account_id" class="form-label">SSF Payable Account</label>
                                <select class="form-select" id="ssf_account_id" name="ssf_account_id">
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['ssf_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="tax_threshold" class="form-label">Tax Threshold</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="tax_threshold" name="tax_threshold"
                                           step="0.01" value="<?= $payrollSettings['tax_threshold'] ?? 0 ?>">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="tax_rate" class="form-label">Tax Rate</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="tax_rate" name="tax_rate"
                                           step="0.01" value="<?= $payrollSettings['tax_rate'] ?? 0 ?>">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="annual_tax_free" class="form-label">Annual Tax Free</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="annual_tax_free" name="annual_tax_free"
                                           step="0.01" value="<?= $payrollSettings['annual_tax_free'] ?? 0 ?>">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="ssf_employee_rate" class="form-label">SSF Employee Rate</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="ssf_employee_rate" name="ssf_employee_rate"
                                           step="0.01" value="<?= $payrollSettings['ssf_employee_rate'] ?? 0 ?>">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="ssf_employer_rate" class="form-label">SSF Employer Rate</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="ssf_employer_rate" name="ssf_employer_rate"
                                           step="0.01" value="<?= $payrollSettings['ssf_employer_rate'] ?? 0 ?>">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="ssf_max_salary" class="form-label">SSF Max Salary</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="ssf_max_salary" name="ssf_max_salary"
                                           step="0.01" value="<?= $payrollSettings['ssf_max_salary'] ?? 0 ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Deduction Accounts & Payment -->
            <div class="col-lg-6">
                <!-- Deduction Accounts -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-minus-circle me-2"></i>Deduction Accounts</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="loan_account_id" class="form-label">Loan Repayment Account</label>
                                <select class="form-select" id="loan_account_id" name="loan_account_id">
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['loan_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="advance_account_id" class="form-label">Salary Advance Account</label>
                                <select class="form-select" id="advance_account_id" name="advance_account_id">
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['advance_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="other_deduction_account_id" class="form-label">Other Deductions Account</label>
                                <select class="form-select" id="other_deduction_account_id" name="other_deduction_account_id">
                                    <option value="">Select Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['other_deduction_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_account_id" class="form-label">Payment Account *</label>
                                <select class="form-select" id="payment_account_id" name="payment_account_id" required>
                                    <option value="">Select Payment Account</option>
                                    <?php foreach ($accounts as $account):
                                        $selected = ($payrollSettings['payment_account_id'] ?? '') == $account['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Account from which salary payments are made</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calculation Formulas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Calculation Formulas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="overtime_rate" class="form-label">Overtime Rate Multiplier</label>
                                <input type="number" class="form-control" id="overtime_rate" name="overtime_rate"
                                       step="0.01" value="<?= $payrollSettings['overtime_rate'] ?? 1.5 ?>">
                                <div class="form-text">e.g., 1.5 for time-and-a-half</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="night_shift_allowance" class="form-label">Night Shift Allowance</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="night_shift_allowance" name="night_shift_allowance"
                                           step="0.01" value="<?= $payrollSettings['night_shift_allowance'] ?? 0 ?>">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="transport_allowance_formula" class="form-label">Transport Allowance Formula</label>
                                <input type="text" class="form-control" id="transport_allowance_formula" name="transport_allowance_formula"
                                       value="<?= htmlspecialchars($payrollSettings['transport_allowance_formula'] ?? '') ?>"
                                       placeholder="e.g., basic_salary * 0.05">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="housing_allowance_formula" class="form-label">Housing Allowance Formula</label>
                                <input type="text" class="form-control" id="housing_allowance_formula" name="housing_allowance_formula"
                                       value="<?= htmlspecialchars($payrollSettings['housing_allowance_formula'] ?? '') ?>"
                                       placeholder="e.g., fixed(500) or basic_salary * 0.2">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="medical_allowance_formula" class="form-label">Medical Allowance Formula</label>
                                <input type="text" class="form-control" id="medical_allowance_formula" name="medical_allowance_formula"
                                       value="<?= htmlspecialchars($payrollSettings['medical_allowance_formula'] ?? '') ?>"
                                       placeholder="e.g., fixed(200) or basic_salary * 0.1">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="currency_id" class="form-label">Currency</label>
                                <select class="form-select" id="currency_id" name="currency_id">
                                    <?php foreach ($currencies as $currency):
                                        $selected = ($payrollSettings['currency_id'] ?? 1) == $currency['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $currency['id'] ?>" <?= $selected ?>>
                                            <?= $currency['code'] ?> - <?= $currency['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deduction Formulas Section -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-code me-2"></i>Custom Deduction Formulas</h5>
                        <button type="button" class="btn btn-sm btn-success" onclick="addDeductionFormula()">
                            <i class="fas fa-plus me-1"></i>Add Formula
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="deductionFormulasContainer">
                            <?php if (empty($deductionFormulas)): ?>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No deduction formulas defined. Click "Add Formula" to create one.
                                </div>
                            <?php else: ?>
                                <?php foreach ($deductionFormulas as $index => $formula): ?>
                                    <div class="deduction-formula-row border rounded p-3 mb-3">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Deduction Name *</label>
                                                <input type="text" class="form-control" name="deduction_name[]"
                                                       value="<?= htmlspecialchars($formula['deduction_name']) ?>" required>
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Type</label>
                                                <select class="form-select" name="calculation_type[]">
                                                    <option value="fixed" <?= $formula['calculation_type'] == 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
                                                    <option value="percentage" <?= $formula['calculation_type'] == 'percentage' ? 'selected' : '' ?>>Percentage</option>
                                                    <option value="formula" <?= $formula['calculation_type'] == 'formula' ? 'selected' : '' ?>>Custom Formula</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Formula/Value *</label>
                                                <input type="text" class="form-control" name="formula[]"
                                                       value="<?= htmlspecialchars($formula['formula']) ?>" required
                                                       placeholder="e.g., 100 or basic_salary * 0.05">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Account</label>
                                                <select class="form-select" name="formula_account_id[]">
                                                    <option value="">Select Account</option>
                                                    <?php foreach ($accounts as $account):
                                                        $selected = $formula['account_id'] == $account['id'] ? 'selected' : '';
                                                    ?>
                                                        <option value="<?= $account['id'] ?>" <?= $selected ?>>
                                                            <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1 mb-3 d-flex align-items-end">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="formula_active[]" value="1"
                                                           id="active_<?= $index ?>" <?= $formula['is_active'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="active_<?= $index ?>">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-text">
                                                    <strong>Variables available:</strong> basic_salary, gross_salary, days_worked, hours_overtime
                                                    <strong>Examples:</strong> "100" (fixed), "basic_salary * 0.05" (5% of basic), "days_worked * 10"
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeDeductionFormula(this)">
                                                <i class="fas fa-trash me-1"></i>Remove
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Additional Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_automatic_tax"
                                           name="enable_automatic_tax" <?= ($payrollSettings['enable_automatic_tax'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_automatic_tax">Enable Automatic Tax Calculation</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_ssf"
                                           name="enable_ssf" <?= ($payrollSettings['enable_ssf'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_ssf">Enable SSF Deduction</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_loan_deduction"
                                           name="enable_loan_deduction" <?= ($payrollSettings['enable_loan_deduction'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_loan_deduction">Enable Loan Deduction</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_advance_deduction"
                                           name="enable_advance_deduction" <?= ($payrollSettings['enable_advance_deduction'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_advance_deduction">Enable Advance Deduction</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="print_payslip"
                                           name="print_payslip" <?= ($payrollSettings['print_payslip'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="print_payslip">Print Payslip Automatically</label>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="email_payslip"
                                           name="email_payslip" <?= ($payrollSettings['email_payslip'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="email_payslip">Email Payslip to Employees</label>
                                </div>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="payment_date" class="form-label">Payment Date (Day)</label>
                                <input type="number" class="form-control" id="payment_date" name="payment_date"
                                       min="1" max="31" value="<?= $payrollSettings['payment_date'] ?? 25 ?>">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="salary_rounding" class="form-label">Salary Rounding</label>
                                <select class="form-select" id="salary_rounding" name="salary_rounding">
                                    <option value="nearest" <?= ($payrollSettings['salary_rounding'] ?? 'nearest') == 'nearest' ? 'selected' : '' ?>>Nearest Dollar</option>
                                    <option value="up" <?= ($payrollSettings['salary_rounding'] ?? 'nearest') == 'up' ? 'selected' : '' ?>>Round Up</option>
                                    <option value="down" <?= ($payrollSettings['salary_rounding'] ?? 'nearest') == 'down' ? 'selected' : '' ?>>Round Down</option>
                                    <option value="none" <?= ($payrollSettings['salary_rounding'] ?? 'nearest') == 'none' ? 'selected' : '' ?>>No Rounding</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="payroll_month" class="form-label">Payroll Month</label>
                                <select class="form-select" id="payroll_month" name="payroll_month">
                                    <option value="current" <?= ($payrollSettings['payroll_month'] ?? 'current') == 'current' ? 'selected' : '' ?>>Current Month</option>
                                    <option value="previous" <?= ($payrollSettings['payroll_month'] ?? 'current') == 'previous' ? 'selected' : '' ?>>Previous Month</option>
                                    <option value="next" <?= ($payrollSettings['payroll_month'] ?? 'current') == 'next' ? 'selected' : '' ?>>Next Month</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Database Schema Modal (for reference) -->
<div class="modal fade" id="databaseSchemaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Database Schema Reference</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre><code>
-- Table: payroll_settings
CREATE TABLE payroll_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: payroll_deduction_formulas
CREATE TABLE payroll_deduction_formulas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    deduction_name VARCHAR(100) NOT NULL,
    calculation_type ENUM('fixed', 'percentage', 'formula') DEFAULT 'fixed',
    formula TEXT NOT NULL,
    account_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES chart_of_accounts(id) ON DELETE SET NULL
);
                </code></pre>
            </div>
        </div>
    </div>
</div>

<style>
.deduction-formula-row {
    background-color: #f8f9fa;
    border-left: 4px solid #0d6efd !important;
}

.deduction-formula-row:hover {
    background-color: #f1f3f5;
}

.form-switch .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.card {
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #e0e0e0;
}

.form-text {
    font-size: 0.85rem;
}

.input-group-text {
    background-color: #f8f9fa;
}

.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}

.border-left-success {
    border-left: 4px solid #198754 !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}
</style>

<script>
// Add new deduction formula row
function addDeductionFormula() {
    const container = document.getElementById('deductionFormulasContainer');
    const index = container.querySelectorAll('.deduction-formula-row').length;

    const html = `
        <div class="deduction-formula-row border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Deduction Name *</label>
                    <input type="text" class="form-control" name="deduction_name[]" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="calculation_type[]">
                        <option value="fixed">Fixed Amount</option>
                        <option value="percentage">Percentage</option>
                        <option value="formula">Custom Formula</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Formula/Value *</label>
                    <input type="text" class="form-control" name="formula[]" required
                           placeholder="e.g., 100 or basic_salary * 0.05">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Account</label>
                    <select class="form-select" name="formula_account_id[]">
                        <option value="">Select Account</option>
                        <?php foreach ($accounts as $account): ?>
                            <option value="<?= $account['id'] ?>">
                                <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="formula_active[]" value="1" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-text">
                        <strong>Variables available:</strong> basic_salary, gross_salary, days_worked, hours_overtime
                        <strong>Examples:</strong> "100" (fixed), "basic_salary * 0.05" (5% of basic), "days_worked * 10"
                    </div>
                </div>
            </div>
            <div class="text-end mt-2">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeDeductionFormula(this)">
                    <i class="fas fa-trash me-1"></i>Remove
                </button>
            </div>
        </div>
    `;

    // Remove info alert if present
    const alert = container.querySelector('.alert');
    if (alert) {
        alert.remove();
    }

    container.insertAdjacentHTML('beforeend', html);
}

// Remove deduction formula row
function removeDeductionFormula(button) {
    const row = button.closest('.deduction-formula-row');
    row.remove();

    // Show info message if no formulas left
    const container = document.getElementById('deductionFormulasContainer');
    if (container.querySelectorAll('.deduction-formula-row').length === 0) {
        container.innerHTML = `
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                No deduction formulas defined. Click "Add Formula" to create one.
            </div>
        `;
    }
}

// Save settings
function saveSettings() {
    // Validate required fields
    const requiredFields = ['salary_account_id', 'payment_account_id'];
    let isValid = true;

    requiredFields.forEach(field => {
        const element = document.getElementById(field);
        if (!element.value) {
            element.classList.add('is-invalid');
            isValid = false;
        } else {
            element.classList.remove('is-invalid');
        }
    });

    // Validate deduction formulas
    const deductionNames = document.querySelectorAll('input[name="deduction_name[]"]');
    deductionNames.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        alert('Please fill in all required fields.');
        return;
    }

    // Submit form
    document.getElementById('payrollSettingsForm').submit();
}

// Reset to defaults
function resetToDefaults() {
    if (confirm('Are you sure you want to reset all settings to default values? This cannot be undone.')) {
        // Clear all form fields
        const form = document.getElementById('payrollSettingsForm');
        form.reset();

        // Clear deduction formulas
        document.getElementById('deductionFormulasContainer').innerHTML = `
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                No deduction formulas defined. Click "Add Formula" to create one.
            </div>
        `;

        // Set default values
        document.getElementById('overtime_rate').value = 1.5;
        document.getElementById('payment_date').value = 25;
        document.getElementById('salary_rounding').value = 'nearest';
        document.getElementById('payroll_month').value = 'current';
        document.getElementById('currency_id').value = 1;

        // Check default switches
        document.getElementById('print_payslip').checked = true;

        alert('Settings have been reset to default values.');
    }
}

// Validate formula input based on type
document.addEventListener('change', function(e) {
    if (e.target.name === 'calculation_type[]') {
        const row = e.target.closest('.deduction-formula-row');
        const formulaInput = row.querySelector('input[name="formula[]"]');
        const type = e.target.value;

        if (type === 'percentage') {
            formulaInput.placeholder = 'e.g., 0.05 for 5% or basic_salary * 0.05';
        } else if (type === 'fixed') {
            formulaInput.placeholder = 'e.g., 100 or days_worked * 10';
        } else {
            formulaInput.placeholder = 'e.g., basic_salary * 0.05 + 100';
        }
    }
});

// Auto-format percentage fields
document.addEventListener('blur', function(e) {
    if (e.target.type === 'number' && e.target.id.includes('rate')) {
        let value = parseFloat(e.target.value);
        if (!isNaN(value) && value > 1 && value <= 100) {
            // Convert whole numbers to decimal
            e.target.value = value / 100;
        }
    }
}, true);

// Show database schema
function showDatabaseSchema() {
    const modal = new bootstrap.Modal(document.getElementById('databaseSchemaModal'));
    modal.show();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>