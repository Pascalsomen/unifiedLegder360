<?php require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/InventorySystem.php';
$inventorySystem = new InventorySystem($pdo);

if (!isset($_GET['id'])) {
    die("No Purchase Order ID specified.");
}

$poId = (int) $_GET['id'];
$po = $inventorySystem->getPurchaseOrderById($poId);

if (!$po) {
    die("Purchase Order not found.");
}

$supplier = $inventorySystem->getSupplierById($po['supplier_id']);
$poItems = $inventorySystem->getPurchaseOrderItems($poId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Purchase Order #<?= $po['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">

        <button onclick="window.print()" class="btn btn-primary no-print">Print</button>
    </div>




     <center>  <img src="../../../ASSETS/logo.png" alt="Logo"  style="height:100px" class="voucher-logo mb-2">
     <h4>RWANDA CHILDREN EDUCATION Foundation</h4>
        <div class="voucher-contact">
            <p>P.O BOX 1787  Kigali Rwanda</p>
            <p>Email: rcfrw@gmail.com | Phone: +2507893208</p>
        </div>


    </center>



    <table class="table" style="width:100%" width ="0">
        <thead>
            <tr>

                <td> <H5><strong>Supplier:</strong> <?= htmlspecialchars($supplier['name']); ?></H5>
                <H5><strong>Tin Number:</strong> <?= htmlspecialchars($supplier['tax_id']); ?></H5> </td>
                <td> <h4 style="text-align:right">Purchase Order # RCEF-PO-<?= $po['id'] ?> </h4> <H5 style="text-align:right"><strong> Date: </strong><?= htmlspecialchars($po['order_date']) ?> </H5>
            <h5></h5> </td>
            </tr>
        </thead>
       </table>

       


    
<br><br>
  

    <h4 class="mt-4">Purchase Items</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total </th>
            </tr>
        </thead>
        <tbody>
            <?php
             $total = 0;
            foreach ($poItems as $index => $item):
              $total = $total +  $item['quantity'] * $item['price']?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($item['item_name']) ?></td>
                <td><?= htmlspecialchars($item['quantity']) ?></td>
                <td><?= number_format($item['price']) ?></td>
                <td><?= number_format($item['quantity'] * $item['price'],2) ?></td>
            </tr>
            <tr>

                <td colspan="4">Total</td>

                <td><?= number_format($total,2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <table class="table" style="width:100%" width ="0">
        <thead>
            <tr>

                <td> <center> <p class="mt-4"><strong>Prepared by: ...........................</strong>  <br><br>Signatue: ...........................</p></center> </td>
                <td> <center>  <p class="mt-4"><strong>Approved by: ........................... </strong> <br><br>Signatue: ...........................</p></center> </td>
            </tr>
        </thead>
       </table>


</body>
</html>
