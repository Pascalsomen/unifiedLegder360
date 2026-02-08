
<?php

require_once __DIR__ . '/../../includes/header.php';

$term_id = $_GET['term_id'];
$bank_name = $_GET['bank_name'] ?? null;

$sql = "SELECT * FROM students WHERE 1=1";
$params = [];

if ($bank_name) {
    $sql .= " AND bank_name = ?";
    $params[] = $bank_name;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
?><div class="container mt-4"><?php
echo "<h2>Payment Slips for Term ID: $term_id</h2>";

foreach ($students as $student) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin:10px;'>";
    echo "<strong>Name:</strong> {$student['first_name']}  {$student['last_name']}<br>";
    echo "<strong>Fee Amount:</strong> {$student['fees_payment']}<br>";
    echo "<strong>School Bank:</strong> {$student['bank_account']} ({$student['bank_name']})<br>";
    echo "<form method='POST' action='record_payment.php'>";
    echo "<input type='hidden' name='student_id' value='{$student['id']}'>";
    echo "<input type='hidden' name='term_id' value='$term_id'>";
    echo "<br> <input type='text'  class='form-control' name='reference_number' placeholder='Reference Number' required><br> ";
    echo "<input type='date' class='form-control' name='payment_date' required><br>";
    echo "<button type='submit' class='btn btn-info' >Record Payment</button>";
    echo "</form></div>";
}
?>

</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
