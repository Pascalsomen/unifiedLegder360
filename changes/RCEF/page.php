<?php session_start();
$pageTitle = "Payment Voucher #".$_REQUEST['ref'];


ob_start();
?>
    <h4>Voucher No: <?php echo $_SESSION['voucher_no'];?></h4>
    <p><strong>Date:</strong> <?= date('Y-m-d') ?></p>
    <p><strong>Payee:</strong> <?php echo $_SESSION['voucher_payee'];?></p>
    <p><strong>Amount:</strong> RWF <?php echo $_SESSION['voucher_total'];?></p>
    <p><strong>Description:</strong> <?php echo $_SESSION['voucher_desc'];?></p>

    <br><br>
    <div style="margin-top: 50px;">
        <p>___________________________</p>
        <p>Authorized Signature</p>
    </div>
<?php
$content = ob_get_clean();

// Include the reusable voucher template
require_once 'voucher_template.php';

?>