<style>
#yourmessage
{
	margin:0 auto;
	width:90%;
	height:auto;
	background-color: yellow;
	margin-bottom:20px;
	padding:8px;
	font-size:14px;
}
#ackTable
{
	margin:0 auto;
	width:90%;
	height:auto;
}
</style>
<?php
	
$servername="localhost";
$username="root";
$password="csl@jantvdb"; //"csl@jantvdb";
$dbname="gpstracker";

$acknowledgeMessage = "";

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

$msgtext = $_REQUEST["notificationAllArea"];
$acknowledgeMessage = "<div id='yourmessage'>".$msgtext."</div><table border='1' style='border-collapse:collapse' id='ackTable'><tr><td colspan='3'><b>Message sent to following receipents</b></td></tr><tr><td>Sno</td><td>Employee Name</td><td>Mobile No</td></tr>";
$totalcnt = $_REQUEST["totalcnt"];
$sno = 0;
$i=0;
while($i<$totalcnt){
	$i++;
	if(isset($_REQUEST["checkall".$i]))
	{	
		$sno++;
		$mobileno=$_REQUEST["checkall".$i];
		$empname = $_REQUEST["empname".$i];
		$sqlCheck = "SELECT max(notificno) nno FROM `notifications` group by mobileno having `mobileno` = '".$mobileno."'";
		$resultCheck = mysql_query($sqlCheck);
		$notificationNo = 1;

		if($row = mysql_fetch_array($resultCheck))
		{
			$notificationNo = $row['nno'] + 1;
		}


		$sql = "insert into notifications(mobileno,notificno,title,message,sendtime,status) values('".$mobileno."',".$notificationNo.",'Compucom Tracker Notification','".$msgtext."', now(),0)";

		$result = mysql_query($sql);		
		$acknowledgeMessage = $acknowledgeMessage."<tr><td>$sno</td><td>".$empname."</td><td>".$mobileno."</td></tr>";
	}
	
	
}

echo $acknowledgeMessage  = $acknowledgeMessage."</table>";
?>