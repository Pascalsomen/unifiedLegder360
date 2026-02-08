<?php
require_once '../../includes/header.php';
require_once '../../classes/DonationSystem.php';

$donationSystem = new DonationSystem($pdo);

// Handle filters
$filters = [
    'donor_id' => $_GET['donor_id'] ?? null,
    'project_id' => $_GET['project_id'] ?? null,
    'from_date' => $_GET['from_date'] ?? null,
    'to_date' => $_GET['to_date'] ?? null
];

$reportData = $donationSystem->getFilteredDonations($filters);

// Fetch donors and projects
$donors = $pdo->query("SELECT id, name FROM donors")->fetchAll();
$projects = $pdo->query("SELECT id, name FROM donation_projects")->fetchAll();
?>

<div class="container mt-4">
    <h3>Donation Report <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Donors')">Export to Excel</button></h3>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <label>Donor</label>
            <select name="donor_id" class="form-select">
                <option value="">All Donors </option>
                <?php foreach ($donors as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $filters['donor_id'] == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Project</label>
            <select name="project_id" class="form-select">
                <option value="">All Projects</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $filters['project_id'] == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>From</label>
            <input type="date" name="from_date" value="<?= $filters['from_date'] ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <label>To</label>
            <input type="date" name="to_date" value="<?= $filters['to_date'] ?>" class="form-control">
        </div>
        <div class="col-md-2 align-self-end">
            <button class="btn btn-primary" type="submit">Filter</button>
        </div>
    </form>

    <table id="table" class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Donor</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Purpose</th>
                <th>Project</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reportData as $d): ?>
                <tr>
                    <td><?= date('M d, Y', strtotime($d['donation_date'])) ?></td>
                    <td><?= htmlspecialchars($d['donor_name']) ?></td>
                    <td><?= number_format($d['amount'], 2) ?></td>
                    <td><?= $d['currency'] ?></td>
                    <td><?= htmlspecialchars($d['purpose']) ?></td>
                    <td><?= htmlspecialchars($d['project_name'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
