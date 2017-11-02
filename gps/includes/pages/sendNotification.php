<?php
	
$servername="localhost";
$username="root";
$password="csl@jantvdb"; //"csl@jantvdb";
$dbname="gpstracker";

$mobileno = $_REQUEST["mobileno"];
$msgtext = $_REQUEST["msgtext"];

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

echo $sqlCheck = "SELECT max(notificno) nno FROM `notifications` group by mobileno having `mobileno` = '".$mobileno."'";
$resultCheck = mysql_query($sqlCheck);
$notificationNo = 1;

if($row = mysql_fetch_array($resultCheck))
{
	$notificationNo = $row['nno'] + 1;
}


echo $sql = "insert into notifications(mobileno,notificno,title,message,sendtime,status) values('".$mobileno."',".$notificationNo.",'Compucom Tracker Notification','".$msgtext."', now(),0)";

$result = mysql_query($sql);

/*while($row = mysql_fetch_assoc($result)){ 
 
 $tem = $row;
 
 $json = json_encode($tem,JSON_UNESCAPED_UNICODE);
 
}

$sqlUpdate = 'Update `notifications`  Set status = 1 where `mobileno` = '.$mobileno;
mysql_query($sqlUpdate);

 echo $json;
*/
?>