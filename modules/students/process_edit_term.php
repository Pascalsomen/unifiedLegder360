<?php require_once '../../includes/header.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $term_id = $_POST['term_id'];
    $term_name = trim($_POST['term_name']);
    $year = trim($_POST['year']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($term_name && $year && $start_date && $end_date) {
        $stmt = $pdo->prepare("UPDATE terms SET term_name = ?, year = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->execute([$term_name, $year, $start_date, $end_date, $term_id]);

        $_SESSION['toast'] = "Term updated successfully.";
    } else {
        $_SESSION['error'] = "All fields are required.";
    }

    echo "<script>window.location ='terms_list.php'</script>";
    exit;
} else {
    $_SESSION['error'] = "Invalid request.";
    echo "<script>window.location ='terms_list.php'</script>";
}