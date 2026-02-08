<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

$data = [
    'term_name' => $_POST['term_name'],
    'year' => $_POST['year'],
    'start_date' => $_POST['start_date'],
    'end_date' => $_POST['end_date'],
];

$school->addTerm($data);
echo "<script>window.location ='terms_list.php'</script>";
exit;
