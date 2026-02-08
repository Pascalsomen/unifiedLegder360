<?php
require_once __DIR__ . '/../../includes/header.php';


if (!isset($_GET['contract_id'])) {
    echo "<div class='alert alert-danger'>Contract ID missing.</div>";
    exit;
}

$contract_id = (int) $_GET['contract_id'];

// Fetch contract details
$stmt = $pdo->prepare("SELECT contract_number, contract_title FROM contracts WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contract) {
    echo "<div class='alert alert-danger'>Contract not found.</div>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_title = $_POST['article_title'];
    $article_body = $_POST['article_body'];

    if (!empty($article_title) && !empty($article_body)) {
        $insert = $pdo->prepare("INSERT INTO contract_articles (contract_id, title, body) VALUES (?, ?, ?)");
        $insert->execute([$contract_id, $article_title, $article_body]);
        $success = "Article added successfully.";
    } else {
        $error = "Please fill in both title and body.";
    }
}

// Get list of added articles
$articles = $pdo->prepare("SELECT * FROM contract_articles WHERE contract_id = ? ORDER BY article_id ASC");
$articles->execute([$contract_id]);
$articleList = $articles->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <h4 class="mb-3">Add Contract Articles</h4>
    <p><strong>Contract #<?= htmlspecialchars($contract['contract_number']) ?>:</strong> <?= htmlspecialchars($contract['contract_title']) ?></p>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 mb-4">
        <div class="mb-3">
            <label for="article_title" class="form-label">Article Title</label>
            <input type="text" name="article_title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="article_body" class="form-label">Article Body</label>
            <textarea name="article_body" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Add Article</button>
        <a href="add_items.php?contract_id=<?= $contract_id ?>" class="btn btn-primary float-end">Next: Add Items</a>
    </form>

    <?php if ($articleList): ?>
        <h5 class="mb-3">Articles Added</h5>
        <ul class="list-group">
            <?php foreach ($articleList as $article): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($article['title']) ?></strong><br>
                    <small><?= nl2br(htmlspecialchars($article['body'])) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
