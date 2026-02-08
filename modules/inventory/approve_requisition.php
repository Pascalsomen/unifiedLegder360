<?php
require_once __DIR__ . '/../../includes/header.php';
$termId = $_GET['id'];
$stmt = $pdo->prepare("UPDATE internal_requisitions SET status  = 'approved' WHERE id='$termId'");
$stmt->execute();

?>