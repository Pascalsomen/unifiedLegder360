<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/HRSystem.php';
$payroll = new HRSystem($pdo);



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payroll->calculateAndSavePayroll(
        $_POST['employee_id'],
        $_POST['gross_salary'],
        $_POST['transport'],
        $_POST['month']
    );



     $ref = 'RCEF-'.date('Ymdhis');

    $employee =  $payroll->getEmployeeById($_POST['employee_id']);
    $employee =  $employee['full_name'];
    $_SESSION['voucher_total'] = $_POST['gross_salary'];
    $_SESSION['voucher_date'] = date('Y-m-d');
    $_SESSION['voucher_no'] = $ref;
    $_SESSION['voucher_payee'] = $employee;
    $_SESSION['voucher_desc']  =  'Salary Payment for '.  $_POST['month'];

echo "<script> window.open('$base./page.php?ref=$ref', '_blank'); </script>";
echo "<script> window.location='payroll_list.php?success=1'</script>";
    exit;
}
?>
