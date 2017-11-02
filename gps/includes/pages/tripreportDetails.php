<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Status Since Last Active</title>

</head>

<body>
<div>
<?php
date_default_timezone_set('Asia/Calcutta');
?>
<?php

$result = mysql_connect("localhost","root","csl@jantvdb"); //"csl@jantvdb");  //csl@jantvdb
if (!$result)
{
  die('Could not connect: ' . mysql_error());
}
$result = mysql_select_db("gpstracker");
if (!$result)
{
  die('Could not select the database: ' . mysql_error());
}


mysql_query('SET character_set_results=utf8');
mysql_query('SET names=utf8');
mysql_query('SET character_set_client=utf8');
mysql_query('SET character_set_connection=utf8');
mysql_query('SET character_set_results=utf8');
mysql_query('SET collation_connection=utf8_general_ci');
header("Cache-Control: no-cache");
?>


<?php
	
	$fromd = date('Y-m-d',strtotime("-1 days"));	
	$tod =  date('Y-m-d');
	 
	if(isset($_GET["formd"]))
	{
		$fromd = $_GET["formd"];
	}
	if(isset($_GET["tod"]))
	{
		$tod = $_GET["tod"];
	}
	$tod = date('Y-m-d', strtotime($tod. ' + 1 days'));
	
	//echo $_GET["formd"];
	//echo $_GET["tod"];
/*	
if(isset($_POST['userName'])){
    $selectedValue = $_POST['userName'];
    // code to process the data
    //...
	//echo $selectedValue ;
	
	//$query="select userName,lastUpdate,sessionID,extraInfo,latitude,longitude from gpslocations  where userName = ".$selectedValue." " ;
	$query= " select userName,lastUpdate,sessionID,extraInfo,latitude,longitude from gpslocations  where userName ='".$selectedValue."' and 	  lastUpdate >= '".$fromd."' AND lastUpdate <= '".$tod."' order by lastUpdate DESC";
	$result = mysql_query($query);
// start a table tag in the HTML
 */
	$blue = "#00f";	
	$green = "#0f0";
	$yellow = "#ffff00";
	$red = "#dd0000";
	$black = "#666";
	$white = "#fff";
	$color = "#fff";
	
	$bgcolor = "#000";
	
	$checkdate = 1;
	
	$unitid = 6;
	
	if(isset($_REQUEST['unitid'])){ $unitid = $_REQUEST['unitid']; }
	
	if(isset($_REQUEST['checkdate'])){ $checkdate = $_REQUEST['checkdate']; }
	$dd = 0;
	$rd = date_sub(date_create(date("Y-m-d")),date_interval_create_from_date_string("$dd days"));
	$requiredDate = date_format(date_sub(date_create(date("Y-m-d")),date_interval_create_from_date_string("$dd days")),"Y-m-d");


	//echo "-------";
	//$query = "SELECT  `name` , imei,  datetime_received , raw_input FROM  `pt_unit` pu , pt_position ppt where pu.id = ppt.unit_id and pu.id = $unitid and datetime_received >= '".$fromd."' AND datetime_received <= '".$tod."' order by datetime_received desc"; 
	$query = "SELECT  `name` , imei,  max(datetime_received) as max_datetime_received , raw_input FROM  `pt_unit` pu , pt_position ppt where pu.id = ppt.unit_id and pu.id = $unitid and datetime_received >= '".$fromd."' AND datetime_received <= '".$tod."' GROUP BY DATE(datetime_received), HOUR(datetime_received) order by max_datetime_received desc "; 
	
	
$result = mysql_query($query);
	
?>

	<table width="98%" style="margin-top:-12px;">
	   <tr>	
	   	<th>SNo</th>
		<th>Active Time</th>
		<th>Last Location</th>
	   </tr>	
<?php 

	$i = 0;
	while($row = mysql_fetch_array($result)){   //Creates a loop to loop through results
		$i++;
		$lastActiveDate = $row['max_datetime_received'];
		echo "<tr><td>" . $i . "</td>";
		echo "<td>" . $row['max_datetime_received'] . "</td>";
		echo "<td>" . $row['raw_input'] . "</td>";
		echo "</tr>";
	}
	echo "</table>";
?>
</div>
</body>
</html>
