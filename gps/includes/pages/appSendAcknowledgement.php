<?php
	
$servername="localhost";
$username="root";
$password="csl@jantvdb"; //"csl@jantvdb";
$dbname="gpstracker";

$id = $_REQUEST["id"];
$acktype = $_REQUEST["acktype"];
$status = $_REQUEST["status"];
$sendtime = $_REQUEST["sendtime"];

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
$sqlUpdate = "";
if($acktype=="delivered")
{
	$sqlUpdate = "UPDATE `notifications` SET `deliverstatus`= ".$status." ,`delivertime` = '".$sendtime."'  Where `id` = ".$id;
}
if($acktype=="read")
{
	$sqlUpdate = "UPDATE `notifications` SET `readstatus`= ".$status.",`readtime` = '".$sendtime."'  Where `id` = ".$id;
}
//echo $sqlUpdate;
$result = mysql_query($sqlUpdate);

if($result===TRUE){ 
 
 $tem = "Record updated successfully - ".$id;
}
else
{
	$tem = "Error updating record - ".$id;
}
 
 $json = json_encode($tem,JSON_UNESCAPED_UNICODE); 

 echo $json;


//appSendAcknowledgement.php?id=1&acktype=delivered&status=1&sendtime=2017-08-08 06:25:43
?>