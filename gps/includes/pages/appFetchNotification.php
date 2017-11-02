<?php
	
$servername="localhost";
$username="root";
$password="csl@jantvdb"; //"csl@jantvdb";
$dbname="gpstracker";

$mobileno = $_REQUEST["mobileno"];

$conn = mysql_connect($servername, $username, $password);
if (!$conn)
{
  die('Could not connect: ' . mysql_error());
}
$result = mysql_select_db($dbname);
if (!$result)
{
  die('Could not select the database: ' . mysql_error());
}


$sql = 'SELECT `id`, `notificno`, `title`, `message`, `sendtime` FROM `notifications` where `mobileno` = '.$mobileno." and  status = 0";

$result = mysql_query($sql);

while($row = mysql_fetch_assoc($result)){ 
 
 $tem = $row;
 
 $json = json_encode($tem,JSON_UNESCAPED_UNICODE);
 
}

$sqlUpdate = 'Update `notifications`  Set status = 1 where `mobileno` = '.$mobileno;
mysql_query($sqlUpdate);

 echo $json;
?>