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

    <label>Filter by Student Name:</label>
    <input type="text" name="student_name" value="<?= htmlspecialchars($_GET['student_name'] ?? '') ?>" placeholder="Enter student name">

    <button type="submit">Generate Report</button>
</form>


<hr>

<?php
if (isset($_GET['term_id'])) {




  $termId = $_GET['term_id'];
  $bankName = $_GET['bank_name'] ?? '';
  $studentName = $_GET['student_name'] ?? '';

  $sql = "SELECT s.first_name,s.last_name, s.bank_name, s.bank_account,s.fees_payment, sp.payment_date
            FROM student_payments sp
            JOIN students s ON sp.student_id = s.id
            WHERE sp.term_id = ?";
  $params = [$termId];

  if (!empty($bankName)) {
      $sql .= " AND s.bank_name = ?";
      $params[] = $bankName;
  }

  if (!empty($studentName)) {
      $sql .= " AND s.first_name LIKE ?";
      $params[] = '%' . $studentName . '%';
  }


  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $results = $stmt->fetchAll();







    if ($results) {
        $total = array_sum(array_column($results, 'fees_payment'));

        echo "<h3>School Fee Payment Report</h3>";
        echo "<button onclick='window.print()'>🖨️ Print</button> ";


        echo "<table class='table table-bordered' cellpadding='8' cellspacing='0'>
                <tr>
                    <th>Student Name</th>
                    <th>Bank Account / Name</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                </tr>";

        foreach ($results as $row) {
            echo "<tr>
                    <td>{$row['first_name']}  {$row['last_name']}</td>
                    <td> {$row['bank_account']} {$row['bank_name']}</td>
                    <td>{$row['fees_payment']}</td>
                    <td>{$row['payment_date']}</td>
                  </tr>";
        }

        echo "<tr style='font-weight: bold;'>
                <td colspan='2'>Total Paid</td>
                <td colspan='2'>{$total}</td>
              </tr>";
        echo "</table>";
    } else {
        echo "<p>No records found for selected criteria.</p>";
    }
}
?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
