<?php require_once __DIR__ . '/includes/header.php';
if (!hasRole('admin')) {
    redirect($base);
}

if (isset($_POST['submit']) && isset($_FILES['sql_file'])) {
    $file = $_FILES['sql_file']['tmp_name'];

    if ($_FILES['sql_file']['type'] === 'application/octet-stream' || pathinfo($_FILES['sql_file']['name'], PATHINFO_EXTENSION) === 'sql') {
        // Run restore command
        $command = "mysql --user=$username --password=$password --host=$host $dbname < $file";
        system($command, $output);

        $_SESSION['toast'] = "✅ Data Successfull Restored";
        $link =$_SERVER['HTTP_REFERER'];
        echo "<script>window.location ='$link'</script>";
    } else {
    echo "<center><h2 style ='color:red'>Please upload a valid .sql file.</h2></center>";
    }
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <h4>Restore Data</h4>
                </div>
                <div class="card-body">
<form method="POST" enctype="multipart/form-data">
    <label>Select Database with .sql file  Make sure it valid backup </label>
    <br>   <br>
    <input class="form-control"   type="file" name="sql_file" accept=".sql" required>
    <br>
    <button  class="btn btn-primary"  type="submit" name="submit">Restore Database</button>
</form>
</div>  </div>  </div>  </div>  </div></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
