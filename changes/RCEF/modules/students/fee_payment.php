<?php
require_once __DIR__ . '/../../includes/header.php';

?>
<div class="container mt-4">
<form method="GET" action="generate_bulk_payslip.php">
    <label for="term_id">Select Term:</label>
    <select name="term_id" required>
        <?php
        $terms = $pdo->query("SELECT id, term_name, year FROM 	terms")->fetchAll();
        foreach ($terms as $term) {
            echo "<option value='{$term['id']}'>{$term['term_name']} - {$term['year']}</option>";
        }
        ?>
    </select>

    <label for="bank_name">Filter by Bank:</label>
    <select name="bank_name">
        <option value="">All Banks</option>
        <?php
        $banks = $pdo->query("SELECT DISTINCT bank_name FROM students")->fetchAll();
        foreach ($banks as $bank) {
            echo "<option value='{$bank['bank_name']}'>{$bank['bank_name']}</option>";
        }
        ?>
    </select>

    <button type="submit">Generate Payment Slips</button>
</form>



</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>