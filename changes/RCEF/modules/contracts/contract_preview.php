<?php
require_once __DIR__ . '/../../includes/header.php';


$contract_id = $_GET['contract_id'] ?? null;
if (!$contract_id) {
    echo "<div class='alert alert-danger'>Missing contract ID.</div>";
    exit;
}

// Fetch contract
$stmt = $pdo->prepare("SELECT c.*, s.name AS supplier_name, s.email, s.phone,s.tax_id FROM contracts c LEFT JOIN suppliers s ON c.supplier_id = s.id WHERE c.contract_id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contract) {
    echo "<div class='alert alert-danger'>Contract not found.</div>";
    exit;
}

// Fetch articles
$stmt = $pdo->prepare("SELECT * FROM contract_articles WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch items
$stmt = $pdo->prepare("SELECT i.*, itm.item_name AS item_name, coa.account_name FROM contract_items i
    LEFT JOIN stock_items itm ON i.item_id = itm.id
    LEFT JOIN chart_of_accounts coa ON i.chart_account_id = coa.id
    WHERE i.contract_id = ?");
$stmt->execute([$contract_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group items by category
$item_categories = ['Fixed' => [], 'Inventory' => [], 'Service' => []];
foreach ($items as $item) {
   $item_categories[$item['item_category']][] = $item;
}

// Fetch signatures
$stmt = $pdo->prepare("SELECT * FROM contract_signatures WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group signatures by type
$grouped_signatures = ['Company' => [], 'Supplier' => []];
foreach ($signatures as $sig) {
    $grouped_signatures[$sig['signer_type']][] = $sig;
}
?>

<center><a   href="print.php?contract_id=<?php echo $contract_id?>&export=pdf" class="btn btn-primary mb-3">Export to PDF</a></center>

<div id="contractContent"  class="container mt-4" style="padding:20px">


    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6 border-end">
       <center> <img src='../../../<?php echo $settings['logo'] ?>' style="height:100px">
            <H3><strong><?= htmlspecialchars($settings['system_name_full']) ?></strong></H3>
            <p><?= nl2br(htmlspecialchars($settings['address'])) ?></p>
              <p><?= nl2br(htmlspecialchars($settings['email'])) ?>  <?= nl2br(htmlspecialchars($settings['phone_number'])) ?> </p>
       </center>
            </div>
        <div class="col-md-6 ">

        <br><br>
         <center>   <H3><strong><?= htmlspecialchars($contract['supplier_name']) ?></strong></H3>
            TIN: <?= htmlspecialchars($contract['tax_id']) ?><br>
            <p>Email: <?= htmlspecialchars($contract['email']) ?><br>
               Phone: <?= htmlspecialchars($contract['phone']) ?></p></center>
        </div>
    </div>

    <center> <h3><?= htmlspecialchars($contract['contract_title']) ?></h3>
     <h3>CONTRACT NO: RCEF-<?= htmlspecialchars($contract['contract_number']) ?></h3> </center>

    <ol>
        <?php foreach ($articles as $a): ?>
            <li><strong><?= htmlspecialchars($a['title']) ?></strong><br><?= nl2br(htmlspecialchars($a['body'])) ?></li>
        <?php endforeach; ?>
    </ol>

    <!-- Items -->
    <?php foreach ($item_categories as $category => $cat_items): ?>
        <?php if (count($cat_items)): ?>
            <h6 class="mt-3"><?= $category ?> Items</h6>
            <table class="table table-sm table-bordered">
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

    <!-- Signatures -->
 <br><br>
    <div class="row">
        <div class="col-md-6 border-end">

            <?php foreach ($grouped_signatures['Company'] as $sig): ?>
                <p>
                    <strong><?= htmlspecialchars($sig['signer_name']) ?></strong><br>
                    <?= htmlspecialchars($sig['signer_title']) ?><br>
                    Date: <?= htmlspecialchars($sig['signature_date']) ?><br>
                    <?php if ($sig['scanned_file']): ?>
                        <a href="/<?= $sig['scanned_file'] ?>" target="_blank">View Signature</a>
                    <?php endif; ?>
                </p>
            <?php endforeach; ?>

            <h6>Stamp And Siganture</h6>
            <br>
            ..........................................
        </div>
        <div class="col-md-6">

            <?php foreach ($grouped_signatures['Supplier'] as $sig): ?>
                <p>
                    <strong><?= htmlspecialchars($sig['signer_name']) ?></strong><br>
                    <?= htmlspecialchars($sig['signer_title']) ?><br>
                    Date: <?= htmlspecialchars($sig['signature_date']) ?><br>
                    <?php if ($sig['scanned_file']): ?>
                        <a href="/<?= $sig['scanned_file'] ?>" target="_blank">View Signature</a>
                    <?php endif; ?>
                </p>
            <?php endforeach; ?>
                  <h6>Stamp And Siganture</h6>
            <br>
            ..........................................
        </div>
    </div>

    <a href="contract_list.php" class="btn btn-secondary mt-4">Back to List</a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

