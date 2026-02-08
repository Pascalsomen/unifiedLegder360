<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';

$school = new SchoolFeesSystem($pdo);

$data = [
    'name' => $_POST['full_name'],
    'email' => $_POST['email'],
    'address' => $_POST['address'],
    'phone' => $_POST['phone']
];

$school->addSponsor($data);
$_SESSION['toast'] ="Added Successfully";
echo "<script>window.location ='sponsor_list.php'</script>";
exit;
