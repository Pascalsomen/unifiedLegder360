<?php
require_once __DIR__ . '/../../includes/header.php';
$termId = $_GET['term_id'];
$bankName = $_GET['bank_name'] ?? '';

$query = "SELECT * FROM students WHERE 1=1 ";
$params = [];

if (!empty($bankName)) {
    $query .= "AND bank_name = ? ";
    $params[] = $bankName;
}

$students = $pdo->prepare($query);
$students->execute($params);
$total =0;
$data = $students->fetchAll();
?><div class="container mt-4"> <?php
// Display list
echo "<h3>Payment Slip for Term ID: $termId</h3>";
echo "<form method='POST' action='record_bulk_payment.php'>";
echo "<input type='hidden' name='term_id' value='$termId'>";
echo "<table border='1' class='table table-bordered'><tr><th>Name</th><th>School Name</th><th>Bank Name</th><th>Bank Account</th><th>Amount</th></tr>";

foreach ($data as $student) {
    $total = $total + $student['fees_payment'];
    echo "<tr>";
    echo "<td>{$student['first_name']}  {$student['last_name']}</td>";
        echo "<td>{$student['school_name']} </td>";
        echo "<td>{$student['bank_name']} </td>";
    echo "<td>{$student['bank_account']} </td>";
    echo "<td>{$student['fees_payment']}</td>";
    echo "</tr>";

    echo "<input type='hidden' name='students[]' value='{$student['id']}'>";
}
echo "<tr>";
echo "<td colspan='2'>Total</td><td>{$total}</td>";
echo "</tr>";
echo "</table>
<input type='hidden' name='total' value='$total'>";

 if(hasPermission(34)){
    echo "<button type='submit'>Confirm and Record Payment</button>";
}else{
 echo "You do not have access to generate new payslip";
}



echo "</form>";

?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
