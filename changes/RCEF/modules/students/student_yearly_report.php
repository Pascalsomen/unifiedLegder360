<?php
require_once __DIR__ . '/../../includes/header.php';

// Default to current academic year if not set
$yearFilter = $_GET['year'] ?? '2024-2025';

// Get available academic years from terms
$years = $pdo->query("SELECT DISTINCT year FROM terms ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);

// Fetch yearly average marks and pass/fail per student
$stmt = $pdo->prepare("
    SELECT
        s.id AS student_id,
        CONCAT(s.first_name, ' ', s.last_name) AS full_name,
        s.grade,
        s.school_name,
        s.father_name,
        s.mother_name,
        t.year,
        ROUND(AVG(sr.marks), 2) AS average_marks,
        CASE
            WHEN AVG(sr.marks) >= 60 THEN 'PASS'
            ELSE 'FAIL'
        END AS status
    FROM student_report sr
    JOIN terms t ON sr.term = t.id
    JOIN students s ON sr.student_id = s.id
    WHERE t.year = ?
    GROUP BY sr.student_id
    ORDER BY full_name
");

$stmt->execute([$yearFilter]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3>Student Yearly Performance Report</h3>

    <!-- Year Selector -->
    <form method="GET" class="mb-3">
        <label>Select Academic Year:</label>
        <select name="year" onchange="this.form.submit()" class="form-control" style="width: 200px; display: inline-block;">
            <?php foreach ($years as $year): ?>
                <option value="<?= htmlspecialchars($year) ?>" <?= $year == $yearFilter ? 'selected' : '' ?>>
                    <?= htmlspecialchars($year) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Donors')">Export to Excel</button>
    </form>


    <!-- Results Table -->
    <table id="table" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Grade</th>
                <th>School</th>
                <th>Father's Name</th>
                <th>Mother's Name</th>
                <th>Year</th>
                <th>Average Marks</th>
                <th>Status</th>
                <th>View Marks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)): ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['grade']) ?></td>
                        <td><?= htmlspecialchars($row['school_name']) ?></td>
                        <td><?= htmlspecialchars($row['father_name']) ?></td>
                        <td><?= htmlspecialchars($row['mother_name']) ?></td>
                        <td><?= htmlspecialchars($row['year']) ?></td>
                        <td><?= htmlspecialchars($row['average_marks']) ?>%</td>
                        <td>
                            <span class="badge <?= $row['status'] === 'PASS' ? 'bg-success' : 'bg-danger' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="student_marks.php?id=<?= $row['student_id'] ?>" class="btn btn-sm btn-primary">Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No results found for <?= htmlspecialchars($yearFilter) ?>.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
