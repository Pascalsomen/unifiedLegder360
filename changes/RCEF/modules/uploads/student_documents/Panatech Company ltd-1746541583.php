<?php  include '../inc/conn.php';



function getMenuCategory($id){

include '../inc/conn.php';

$sql = "SELECT * FROM menu WHERE menu_id='$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

	return $row['cat_id'];
	
  }
}
	
	
}




function getMenuName($id){

include '../inc/conn.php';

$sql = "SELECT * FROM menu WHERE menu_id='$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

	return $row['menu_name'];
	
  }
}
	
	
}







function servantName($id){

include '../inc/conn.php';

$sql = "SELECT * FROM tbl_users WHERE user_id='$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

	return $row['f_name'] ." ".$row['l_name'];
	
  }
}
	
	
}


function getMenuPrice($id){

include '../inc/conn.php';

$sql = "SELECT * FROM menu WHERE menu_id='$id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

	return $row['menu_price'];
	
  }
}
	
	
}


$sqls = "SELECT cmd_code FROM tbl_cmd_qty where printed = 0 AND cmd_status = 13 GROUP BY  cmd_code LIMIT 1  ";
$results = $conn->query($sqls);
if ($results->num_rows > 0) {
  // output data of each row
  while($rows = $results->fetch_assoc()) {
      
      $id = $rows['cmd_code'];
      
      $response = [
    'transactionId' => $id,  // or use any specific transaction ID
    'items' => [],
    'user' => '',
    
];

$sql = "SELECT cmd_qty_id,Serv_id,cmd_item,cmd_qty,message FROM tbl_cmd_qty where printed = 0 AND cmd_status != 3 AND cmd_code ='$id'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
     
     
   if(getMenuCategory($row['cmd_item']) ==2){
       
      $type = 2; 
       
    $servant = $row['Serv_id'];
    $s = $row['cmd_qty_id'];
    $itemPrice = getMenuPrice($row['cmd_item']);
    $totalPrice = $itemPrice * $row['cmd_qty'];
    $response['items'][] = [
        'name' => getMenuName($row['cmd_item']),
        'quantity' => $row['cmd_qty'],
         'message' => $row['message'],
        'price' => $itemPrice
    ];

    // Add to the total
    $response['user'] = servantName($servant);
    
    
$sql = "UPDATE tbl_cmd_qty SET printed = 1  WHERE cmd_qty_id='$s'";
if ($conn->query($sql) === TRUE) {
 // echo "Record updated successfully";
} else {
 // echo "Error updating record: " . $conn->error;
}
    
    
      
      
    
  }}
  
  if ($type ==2){
  
header('Content-Type: application/json');
echo json_encode($response);
}
  
}



      
      
      
      
    
  }
}









?>