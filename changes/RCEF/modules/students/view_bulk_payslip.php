<?php require_once __DIR__ . '/../../includes/header.php'; ?>
<div class="container mt-4">
<form method="GET" action="">
    <label>Select Term:</label>
    <select name="term_id" required>
        <?php
        $terms = $pdo->query("SELECT id, term_name, year FROM terms")->fetchAll();
        foreach ($terms as $term) {
            echo "<option value='{$term['id']}'" .
                 (isset($_GET['term_id']) && $_GET['term_id'] == $term['id'] ? ' selected' : '') .
                 ">{$term['term_name']} - {$term['year']}</option>";
        }
        ?>
    </select>

    <label>Filter by Bank:</label>
    <select name="bank_name">
        <option value="">All Banks</option>
        <?php
        $banks = $pdo->query("SELECT DISTINCT bank_name FROM students")->fetchAll();
        foreach ($banks as $bank) {
            echo "<option value='{$bank['bank_name']}'" .
                 (isset($_GET['bank_name']) && $_GET['bank_name'] == $bank['bank_name'] ? ' selected' : '') .
                 ">{$bank['bank_name']}</option>";
        }
        ?>
    </select>

    <button type="submit">View Payslip</button>
</form>
<hr>

<?php
if (isset($_GET['term_id'])) {
    $termId = $_GET['term_id'];
    $bankName = $_GET['bank_name'] ?? '';

    $sql = "SELECT s.first_name,s.last_name, s.bank_name,s.bank_account, s.fees_payment,s.school_name, sp.payment_date
            FROM student_payments sp
            JOIN students s ON sp.student_id = s.id
            WHERE sp.term_id = ?";
    $params = [$termId];

    if (!empty($bankName)) {
        $sql .= " AND s.bank_name = ?";
        $params[] = $bankName;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    if ($results) {
        echo "<h3>Generated Bulk Payment Slip</h3>";
        echo "<button onclick='window.print()'>🖨️ Print</button>";
        ?><button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Payment Slip')">Export to Excel</button><?php
        echo " <br><br><table id='table' class='table table-bordered' cellpadding='8' cellspacing='0'>
        <thead>
          <tr>
            <th>Student Name</th>
            <th>School</th>
            <th>Bank</th>
            <th>Amount</th>
            <th>Payment Date</th>
          </tr>
        </thead>
        <tbody>";
foreach ($results as $row) {
    echo "<tr>
            <td>{$row['first_name']} {$row['last_name']}</td>
            <td>{$row['school_name']}</td>
            <td>{$row['bank_account']} {$row['bank_name']}</td>
            <td>{$row['fees_payment']}</td>
            <td>{$row['payment_date']}</td>
          </tr>";
}
echo "</tbody></table>";

    } else {
        echo "<p>No payments found for the selected term and bank.</p>";
    }
}
?>

</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>