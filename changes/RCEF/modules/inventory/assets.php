<?php
require_once __DIR__ . '/../../includes/header.php';

$query = "SELECT * FROM fixed_assets ORDER BY category, asset_name";
$stmt = $pdo->query($query);
$assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentYear = date('Y');
?>

<div class="container mt-5">
    <h3 class="mb-4">Fixed Assets Register <a href="add_asset.php" class="btn btn-info">Add +</a></h3>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Asset Name</th>
                <th>Category</th>
                <th>Purchase Date</th>
                <th class="text-end">Cost</th>
                <th class="text-end">Depreciation (Annual)</th>
                <th class="text-end">Accumulated Depreciation</th>
                <th class="text-end">Net Book Value</th>
                <th class="text-end">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalCost = 0;
            $totalAccumulated = 0;
            $totalNBV = 0;

            foreach ($assets as $asset) {
                $yearsUsed = max(0, $currentYear - date('Y', strtotime($asset['purchase_date'])));
                $yearsUsed = min($yearsUsed, $asset['useful_life']);

                $annualDep = ($asset['cost'] - $asset['salvage_value']) / $asset['useful_life'];
                $accumulated = $annualDep * $yearsUsed;
                $netBookValue = $asset['cost'] - $accumulated;

                $totalCost += $asset['cost'];
                $totalAccumulated += $accumulated;
                $totalNBV += $netBookValue;

                echo "<tr>
                    <td>{$asset['asset_name']}</td>
                    <td>{$asset['category']}</td>
                    <td>{$asset['purchase_date']}</td>
                    <td class='text-end'>" . number_format($asset['cost'], 2) . "</td>
                    <td class='text-end'>" . number_format($annualDep, 2) . "</td>
                    <td class='text-end'>" . number_format($accumulated, 2) . "</td>
                    <td class='text-end'>" . number_format($netBookValue, 2) . "</td>
                     <td class='text-end'><a href='edit_asset.php?id=".$asset['id']."class='btn btn-sm btn-warning'>Edit</a> </td>

                </tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th class="text-end"><?= number_format($totalCost, 2) ?></th>
                <th></th>
                <th class="text-end"><?= number_format($totalAccumulated, 2) ?></th>
                <th class="text-end"><?= number_format($totalNBV, 2) ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
