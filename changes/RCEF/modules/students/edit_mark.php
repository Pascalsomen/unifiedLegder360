<?php require_once __DIR__ . '/../../includes/header.php';

if (!isset($_GET['id'], $_GET['student_id'])) die("Mark ID and Student ID required.");

$markId = $_GET['id'];
$studentId = $_GET['student_id'];

// Fetch mark
$markStmt = $pdo->prepare("SELECT * FROM student_report WHERE id = ?");
$markStmt->execute([$markId]);
$mark = $markStmt->fetch(PDO::FETCH_ASSOC);
if (!$mark) die("Mark not found");

// Get terms
$terms = $pdo->query("SELECT * FROM terms ORDER BY year DESC")->fetchAll(PDO::FETCH_ASSOC);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("UPDATE student_report SET term = ?, marks = ? WHERE id = ?")
        ->execute([$_POST['term'], $_POST['marks'], $markId]);
        echo "<script>window.location='student_report.php?id=$studentId'</script>";
    exit;
}
?>

<div class="container mt-4">
    <h3>Edit Mark</h3>
    <form method="POST">
        <div class="mb-3">
            <label>Term</label>
            <select name="term" class="form-control" required>
                <?php foreach ($terms as $term): ?>
                    <option value="<?= $term['id'] ?>" <?= $term['id'] == $mark['term'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($term['term_name']) ?> (<?= $term['year'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Marks</label>
            <input type="number" step="0.01" name="marks" class="form-control" value="<?= $mark['marks'] ?>" required>
        </div>
        <button class="btn btn-success">Update Mark</button>
        <a href="student_marks.php?id=<?= $studentId ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
