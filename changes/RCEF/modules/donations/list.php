<?php
require_once '../../includes/header.php';
require_once '../../classes/DonationSystem.php';

$donationSystem = new DonationSystem($pdo);
$donations = $donationSystem->getAllDonations();
?>

<div class="container mt-4">
    <h3>All Donations</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Donor</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Project</th>
                <th>Payment Method</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($donations as $donation): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($donation['donation_date'])) ?></td>
                    <td><?= htmlspecialchars($donation['donor_name']) ?></td>
                    <td><?= number_format($donation['amount'], 2) ?></td>
                    <td><?= $donation['currency'] ?></td>
                    <td><?= htmlspecialchars($donation['project_name'] ?? '-') ?></td>
                    <td><?= $donation['payment_method'] ?></td>
                    <td>
                        <?php if (!empty($donation['receipt_path'])): ?>
                            <a href="/uploads/receipts/<?= htmlspecialchars($donation['receipt_path']) ?>" target="_blank">View</a>
                        <?php else: ?>
                            <em>None</em>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
