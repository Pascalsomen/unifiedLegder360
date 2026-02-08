<?php
$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');



$opennings = $pdo->prepare("SELECT SUM(tl.debit) AS otherRevenue FROM transaction_lines tl JOIN transactions t ON t.id = tl.transaction_id JOIN chart_of_accounts a ON a.id = tl.account_id WHERE a.account_type = 'openning' AND t.transaction_date BETWEEN ? AND ?");
$opennings->execute([$startDate, $endDate]);
$otheropennings = $opennings->fetchColumn();



$otherRevenue = $pdo->prepare("SELECT SUM(tl.debit) AS otherRevenue FROM transaction_lines tl JOIN transactions t ON t.id = tl.transaction_id JOIN chart_of_accounts a ON a.id = tl.account_id WHERE a.account_type = 'revenue' AND t.transaction_date BETWEEN ? AND ?");
$otherRevenue->execute([$startDate, $endDate]);
$otherRevenueAmount = $otherRevenue->fetchColumn();



$otherSalaries = $pdo->prepare("SELECT SUM(tl.debit) AS otherRevenue FROM transaction_lines tl JOIN transactions t ON t.id = tl.transaction_id JOIN chart_of_accounts a ON a.id = tl.account_id WHERE t.trx_type = 'salaries' AND t.transaction_date BETWEEN ? AND ?");
$otherSalaries->execute([$startDate, $endDate]);
$otherotherSalaries = $otherSalaries->fetchColumn();


$service = $pdo->prepare("SELECT SUM(tl.debit) AS otherRevenue FROM transaction_lines tl JOIN transactions t ON t.id = tl.transaction_id JOIN chart_of_accounts a ON a.id = tl.account_id WHERE t.trx_type = 'service' AND t.transaction_date BETWEEN ? AND ?");
$service->execute([$startDate, $endDate]);
$otherService = $service->fetchColumn();



$good = $pdo->prepare("SELECT SUM(tl.debit) AS otherRevenue FROM transaction_lines tl JOIN transactions t ON t.id = tl.transaction_id JOIN chart_of_accounts a ON a.id = tl.account_id WHERE t.trx_type = 'goods' AND t.transaction_date BETWEEN ? AND ?");
$good->execute([$startDate, $endDate]);
$otherGoods = $good->fetchColumn();




$otherRevenue = $pdo->prepare("SELECT SUM(tl.debit) AS otherRevenue FROM transaction_lines tl JOIN transactions t ON t.id = tl.transaction_id JOIN chart_of_accounts a ON a.id = tl.account_id WHERE a.account_type = 'revenue' AND t.transaction_date BETWEEN ? AND ?");
$otherRevenue->execute([$startDate, $endDate]);
$otherRevenueAmount = $otherRevenue->fetchColumn();

?>
<div class="container mt-5">
    <h3 class="mb-4"></h3>


<table class="table table-bordered">
<tr>
    <td>1</td>
    <td>Fund Carried Forward - Previous period</td>
    <td><?php echo $otheropennings?></td>
</tr>

<tr>
    <td>2</td>
    <td>Other revenue Generated</td>
    <td><?php echo $otherRevenueAmount; ?></td>
</tr>



<tr>
    <td>3</td>
    <td>Total Funds Receieve during current perion</td>
    <td>0</td>
</tr>


<tr>
    <td>4</td>
    <td>Total Funds in the Period of submission </td>
    <td><?php echo  $G = $otheropennings + $otherRevenueAmount; ?></td>
</tr>

<tr>
    <td>5</td>
    <td>Employee remunaration and transfers to local partner </td>
    <td><?php echo $otherotherSalaries; ?></td>
</tr>

<tr>
    <td>6</td>
    <td>Good Puchased</td>
    <td><?php echo $otherGoods; ?></td>
</tr>

<tr>
    <td>7</td>
    <td>Servicee Obtained</td>
    <td><?php echo $otherService; ?></td>
</tr>



<tr>
    <td>8</td>
    <td>Total Expendutures </td>
    <td><?php echo   $I = $otherService + $otherGoods + $otherotherSalaries?></td>
</tr>


<tr>
    <td>8</td>
    <td>Surplus/Loss of the current Period </td>
    <td><?PHP echo $G - $I ?></td>
</tr>
</table>


</div>
