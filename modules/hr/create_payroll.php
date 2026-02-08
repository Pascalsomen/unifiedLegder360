<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/HRSystem.php';
$payroll = new HRSystem($pdo);
$employees = $payroll->getAllEmployees(); // create this method to return employee list
?>
<div class="container mt-4">
<h3>Generate Monthly Payroll</h3>
<form method="post" action="process_payroll.php">
    <label>Employee:</label>
    <select class="form-control" name="employee_id" required>
        <option value="">Select</option>
        <?php foreach ($employees as $emp): ?>
            <option value="<?= $emp['id'] ?>"><?= $emp['full_name'] ?></option>
        <?php endforeach; ?>
    </select><br>

    <label>Gross Salary:</label>
    <input class="form-control" type="number"  step="0.01" name="gross_salary" required><br>

    <label>Transport Allowance:</label>
    <input class="form-control" type="number" step="0.01" name="transport" required><br>

    <label>Month:</label>
    <input class="form-control" type="month" name="month" required><br><br>

    <?php if(hasPermission(23)){?>

        <button class="btn btn-info" type="submit">Generate Payroll</button>
<?php }else{
 Echo "You do not have access to generate payroll";
} ?>

</form>
        </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
