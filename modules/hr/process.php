<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasPermission('hr')) {
    redirect('/index.php');
}

// Handle payroll processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $periodId = $_POST['period_id'];
    $employees = $_POST['employees'];

    try {
        $pdo->beginTransaction();

        foreach ($employees as $employeeId => $data) {
            // Calculate tax (simplified - in reality would use tax brackets)
            $taxableIncome = $data['basic_salary'] + $data['taxable_allowances'] - $data['pretax_deductions'];
            $tax = calculateTax($taxableIncome, 'US'); // Country would be from employee record

            $netPay = $taxableIncome - $tax - $data['posttax_deductions'];

            // Insert payroll item
            $stmt = $pdo->prepare("INSERT INTO payroll_items
                                  (payroll_id, employee_id, basic_salary, allowances,
                                   deductions, tax_amount, net_pay)
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $periodId,
                $employeeId,
                $data['basic_salary'],
                $data['taxable_allowances'] + $data['nontaxable_allowances'],
                $data['pretax_deductions'] + $data['posttax_deductions'],
                $tax,
                $netPay
            ]);
        }

        // Update payroll period status
        $stmt = $pdo->prepare("UPDATE payroll_periods SET status = 'processing' WHERE id = ?");
        $stmt->execute([$periodId]);

        $pdo->commit();
        $_SESSION['success'] = "Payroll processed successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Payroll processing failed: " . $e->getMessage();
    }

    redirect('/modules/payroll/process.php');
}

// Fetch active payroll periods
$periods = $pdo->query("SELECT * FROM payroll_periods WHERE status = 'draft' ORDER BY end_date DESC")->fetchAll();

// Tax calculation function
function calculateTax($income, $country) {
    // In a real system, this would use the tax_brackets table
    $brackets = [
        'US' => [
            ['min' => 0, 'max' => 9950, 'rate' => 0.10],
            ['min' => 9951, 'max' => 40525, 'rate' => 0.12],
            // ... more brackets
        ],
        'UK' => [
            // UK tax brackets
        ]
    ];

    $tax = 0;
    foreach ($brackets[$country] as $bracket) {
        if ($income > $bracket['min']) {
            $taxableInBracket = min($income, $bracket['max'] ?? PHP_FLOAT_MAX) - $bracket['min'];
            $tax += $taxableInBracket * $bracket['rate'];
        }
    }

    return $tax;
}
?>

<div class="container-fluid">
    <h2>Process Payroll</h2>

    <div class="card">
        <div class="card-header">
            Select Payroll Period
        </div>
        <div class="card-body">
            <?php if (empty($periods)): ?>
                <div class="alert alert-info">No draft payroll periods available.</div>
                <a href="create_period.php" class="btn btn-primary">Create New Payroll Period</a>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Payroll Period</label>
                        <select class="form-control" name="period_id" id="periodSelect" required>
                            <option value="">-- Select Period --</option>
                            <?php foreach ($periods as $period): ?>
                                <option value="<?= $period['id'] ?>">
                                    <?= $period['name'] ?> (<?= date('M j', strtotime($period['start_date'])) ?> - <?= date('M j, Y', strtotime($period['end_date'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="employeeSection" style="display: none;">
                        <h4 class="mt-4">Employees</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Basic Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Tax</th>
                                        <th>Net Pay</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeRows">
                                    <!-- Employee rows will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">
                            <i class="fas fa-calculator"></i> Process Payroll
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load employees when period is selected
    $('#periodSelect').change(function() {
        const periodId = $(this).val();
        if (!periodId) {
            $('#employeeSection').hide();
            return;
        }

        $.get('/api/payroll.php?action=get_employees&period_id=' + periodId, function(data) {
            if (data.success) {
                let html = '';
                data.employees.forEach(employee => {
                    html += `
                        <tr>
                            <td>
                                ${employee.name}
                                <input type="hidden" name="employees[${employee.id}][basic_salary]" value="${employee.basic_salary}">
                                <input type="hidden" name="employees[${employee.id}][taxable_allowances]" value="${employee.taxable_allowances}">
                                <input type="hidden" name="employees[${employee.id}][nontaxable_allowances]" value="${employee.nontaxable_allowances}">
                                <input type="hidden" name="employees[${employee.id}][pretax_deductions]" value="${employee.pretax_deductions}">
                                <input type="hidden" name="employees[${employee.id}][posttax_deductions]" value="${employee.posttax_deductions}">
                            </td>
                            <td>${employee.basic_salary.toFixed(2)}</td>
                            <td>${(employee.taxable_allowances + employee.nontaxable_allowances).toFixed(2)}</td>
                            <td>${(employee.pretax_deductions + employee.posttax_deductions).toFixed(2)}</td>
                            <td>${employee.tax_estimate.toFixed(2)}</td>
                            <td>${employee.net_pay.toFixed(2)}</td>
                        </tr>
                    `;
                });

                $('#employeeRows').html(html);
                $('#employeeSection').show();
            } else {
                alert('Error: ' + data.message);
            }
        }, 'json');
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>