<?php
ob_start();

require_once __DIR__ . '/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/HRSystem.php';

$stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

$contract_id = $_GET['contract_id'] ?? null;
if (!$contract_id) {
    echo "<div class='alert alert-danger'>Missing contract ID.</div>";
    exit;
}

$stmt = $pdo->prepare("SELECT c.*, s.name AS supplier_name, s.email, s.phone, s.tax_id
                      FROM contracts c
                      LEFT JOIN suppliers s ON c.supplier_id = s.id
                      WHERE c.contract_id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contract) {
    echo "<div class='alert alert-danger'>Contract not found.</div>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM contract_articles WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT i.*, itm.item_name AS item_name, coa.account_name
                      FROM contract_items i
                      LEFT JOIN stock_items itm ON i.item_id = itm.id
                      LEFT JOIN chart_of_accounts coa ON i.chart_account_id = coa.id
                      WHERE i.contract_id = ?");
$stmt->execute([$contract_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$item_categories = ['Fixed' => [], 'Inventory' => [], 'Service' => []];
foreach ($items as $item) {
    $item_categories[$item['item_category']][] = $item;
}

$stmt = $pdo->prepare("SELECT * FROM contract_signatures WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped_signatures = ['Company' => [], 'Supplier' => []];
foreach ($signatures as $sig) {
    $grouped_signatures[$sig['signer_type']][] = $sig;
}

$is_pdf = isset($_GET['export']) && $_GET['export'] == 'pdf';
?>

<?php if ($is_pdf): ?>
<!DOCTYPE html>
<html>
<head>
    <title>Contract <?= htmlspecialchars($contract['contract_number']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 15px; line-height: 1.5; }
        .container { width: 100%; padding: 20px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .col { width: 48%; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 8px; text-align: left; }
        .text-center { text-align: center; }
        .mt-3 { margin-top: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        ol { padding-left: 20px; }
        img { max-width: 200px; max-height: 100px; }
    </style>
</head>
<body>
<?php endif; ?>

<table style="width:100%;border:0px">
    <tr style="border:0px">
    <td style="width:50%;border:0px">
        <center>    <img src="http://localhost/RCEF/uploads/1746799010_logo.png" style="height:100px">
            <h3><strong><?= htmlspecialchars($settings['system_name_full']) ?></strong></h3>
            <p><?= nl2br(htmlspecialchars($settings['address'])) ?></p>
            <p><?= nl2br(htmlspecialchars($settings['email'])) ?> | <?= nl2br(htmlspecialchars($settings['phone_number'])) ?></p>  </center>
</td>
         <td style="border:0px">
            <center>    <h3><strong><?= htmlspecialchars($contract['supplier_name']) ?></strong></h3>
            <p>TIN: <?= htmlspecialchars($contract['tax_id']) ?></p>
            <p>Email: <?= htmlspecialchars($contract['email']) ?><br>Phone: <?= htmlspecialchars($contract['phone']) ?></p>
</center>
</td>
</tr>
</table>

    <div class="text-center mb-4">
       <center>     <h3><?= htmlspecialchars($contract['contract_title']) ?></h3>
        <h4>CONTRACT NO: RCEF-<?= htmlspecialchars($contract['contract_number']) ?></h4>   </center>
    </div>

    <ol>
        <?php foreach ($articles as $a): ?>
            <li><strong><?= htmlspecialchars($a['title']) ?></strong><br><?= nl2br(htmlspecialchars($a['body'])) ?></li>
        <?php endforeach; ?>
    </ol>

    <?php foreach ($item_categories as $category => $cat_items): ?>
        <?php if (count($cat_items)): ?>
            <h4 class="mt-3"><?= $category ?> Items</h4>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cat_items as $i): ?>
                        <tr>
                            <td><?= htmlspecialchars($i['item_name']) ?></td>
                            <td><?= htmlspecialchars($i['quantity']) ?></td>
                            <td><?= number_format($i['unit_price'], 2) ?></td>
                            <td><?= number_format($i['unit_price'] * $i['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>

    <table style="width:100%;border:0px">
        <tr style="border:0px">
        <td style="border:0px">
            <?php foreach ($grouped_signatures['Company'] as $sig): ?>
                <p>
                    <strong><?= htmlspecialchars($sig['signer_name']) ?></strong><br>
                    <?= htmlspecialchars($sig['signer_title']) ?><br>
                    Date: <?= htmlspecialchars($sig['signature_date']) ?><br>
                </p>
            <?php endforeach; ?>
            <h5>Stamp And Signature</h5><br>..........................................
            </td>
        <td style="border:0px">
            <?php foreach ($grouped_signatures['Supplier'] as $sig): ?>
                <p>
                    <strong><?= htmlspecialchars($sig['signer_name']) ?></strong><br>
                    <?= htmlspecialchars($sig['signer_title']) ?><br>
                    Date: <?= htmlspecialchars($sig['signature_date']) ?><br>
                </p>
            <?php endforeach; ?>
            <h5>Stamp And Signature</h5><br>..........................................
            </td>
            </tr>
            </table>


</div>

<?php if ($is_pdf): ?>
</body>
</html>
<?php
    $html = ob_get_clean();

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream("Contract_RCEF-{$contract['contract_number']}.pdf", [
        "Attachment" => true
    ]);
    exit;
?>
<?php endif; ?>
