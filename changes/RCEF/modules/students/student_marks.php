<?php
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_GET['id'])) die("Student ID required.");
$studentId = $_GET['id'];

// Get terms
$terms = $pdo->query("SELECT * FROM terms ORDER BY year DESC, start_date DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch marks
$marksStmt = $pdo->prepare("SELECT sm.*, t.term_name, t.year FROM student_report sm
                            JOIN terms t ON sm.term = t.id
                            WHERE sm.student_id = ?");
$marksStmt->execute([$studentId]);
$studentMarks = $marksStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $term = $_POST['term'];
    $mark = $_POST['marks'];
    $pdo->prepare("INSERT INTO student_report (student_id, term, marks, created_at) VALUES (?, ?, ?, NOW())")
        ->execute([$studentId, $term, $mark]);
        $_SESSION['toast'] = "Marks successfully added.";
    echo "<script>window.location='student_marks.php?id=$studentId'</script>";
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $markId = $_GET['delete'];
    $pdo->prepare("DELETE FROM student_report WHERE id = ?")->execute([$markId]);
    $_SESSION['toast'] = "Marks successfully deleted.";
    echo "<script>window.location='student_marks.php?id=$studentId'</script>";
    exit;
}
?>

<div class="container mt-4">
    <h3>Student Marks   <a  class="btn btn-primary float-end" href="list_documents.php?id=<?php echo $_REQUEST['id']?>">Documents</a></h3>

    <!-- Add new mark -->
    <form method="POST" class="mb-4">
        <input type="hidden" name="action" value="add">
        <div class="row">
            <div class="col-md-4">
                <label>Term</label>
                <select name="term" class="form-control" required>
                    <option value="">Select term</option>
                    <?php foreach ($terms as $term): ?>
                        <option value="<?= $term['id'] ?>">
                            <?= htmlspecialchars($term['term_name']) ?> (<?= $term['year'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>Marks</label>
                <input type="number" step="0.01" name="marks" class="form-control" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Add Mark</button>
            </div>
        </div>
    </form>

    <!-- Show existing marks -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Term</th>
                <th>Year</th>
                <th>Marks</th>
                <th>Date Recorded</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($studentMarks as $mark): ?>
                <tr>
                    <td><?= htmlspecialchars($mark['term_name']) ?></td>
                    <td><?= htmlspecialchars($mark['year']) ?></td>
                    <td><?= htmlspecialchars($mark['marks']) ?></td>
                    <td><?= htmlspecialchars($mark['created_at']) ?></td>
                    <td>
                        <a href="edit_mark.php?id=<?= $mark['id'] ?>&student_id=<?= $studentId ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?id=<?= $studentId ?>&delete=<?= $mark['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this mark?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
