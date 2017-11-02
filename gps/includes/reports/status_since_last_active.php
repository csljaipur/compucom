<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Status Since Last Active</title>

<style>
body{
	font-family:Arial, Helvetica, sans-serif;
	background: url(background.jpg);
	margin:0 auto;
	width:100%;
}
a:link {
	color: #666;
	font-weight: bold;
	text-decoration:none;
}
a:visited {
	color: #666;
	font-weight:bold;
	text-decoration:none;
}
a:active,
a:hover {
	color: #bd5a35;
	text-decoration:underline;
}


/*
Table Style - This is what you want
------------------------------------------------------------------ */
table a:link {
	color: #666;
	font-weight: bold;
	text-decoration:none;
}
table a:visited {
	color: #999999;
	font-weight:bold;
	text-decoration:none;
}
table a:active,
table a:hover {
	color: #bd5a35;
	text-decoration:underline;
}
table {
	font-family:Arial, Helvetica, sans-serif;
	color:#666;
	font-size:12px;
	text-shadow: 1px 1px 0px #fff;
	background:#eaebec;
	margin:20px;
	border:#ccc 1px solid;

	-moz-border-radius:3px;
	-webkit-border-radius:3px;
	border-radius:3px;

	-moz-box-shadow: 0 1px 2px #d1d1d1;
	-webkit-box-shadow: 0 1px 2px #d1d1d1;
	box-shadow: 0 1px 2px #d1d1d1;
}
table th {
	padding:21px 25px 22px 25px;
	border-top:1px solid #fafafa;
	border-bottom:1px solid #e0e0e0;

	background: #ededed;
	background: -webkit-gradient(linear, left top, left bottom, from(#ededed), to(#ebebeb));
	background: -moz-linear-gradient(top,  #ededed,  #ebebeb);
}
table th:first-child{
	text-align: left;
	padding-left:20px;
}
table tr:first-child th:first-child{
	-moz-border-radius-topleft:3px;
	-webkit-border-top-left-radius:3px;
	border-top-left-radius:3px;
}
table tr:first-child th:last-child{
	-moz-border-radius-topright:3px;
	-webkit-border-top-right-radius:3px;
	border-top-right-radius:3px;
}
table tr{
	text-align: center;
	padding-left:20px;
}
table tr td:first-child{
	text-align: left;
	padding-left:20px;
	border-left: 0;
}
table tr td {
	padding:18px;
	border-top: 1px solid #ffffff;
	border-bottom:1px solid #e0e0e0;
	border-left: 1px solid #e0e0e0;
	
	background: #fafafa;
	background: -webkit-gradient(linear, left top, left bottom, from(#fbfbfb), to(#fafafa));
	background: -moz-linear-gradient(top,  #fbfbfb,  #fafafa);
}
table tr.even td{
	background: #f6f6f6;
	background: -webkit-gradient(linear, left top, left bottom, from(#f8f8f8), to(#f6f6f6));
	background: -moz-linear-gradient(top,  #f8f8f8,  #f6f6f6);
}
table tr:last-child td{
	border-bottom:0;
}
table tr:last-child td:first-child{
	-moz-border-radius-bottomleft:3px;
	-webkit-border-bottom-left-radius:3px;
	border-bottom-left-radius:3px;
}
table tr:last-child td:last-child{
	-moz-border-radius-bottomright:3px;
	-webkit-border-bottom-right-radius:3px;
	border-bottom-right-radius:3px;
}
table tr:hover td{
	background: #f2f2f2;
	background: -webkit-gradient(linear, left top, left bottom, from(#f2f2f2), to(#f0f0f0));
	background: -moz-linear-gradient(top,  #f2f2f2,  #f0f0f0);	
}
.clickable
{
	color:blue;
	cursor:pointer;
}


#leftside
{
	position:absolute;	
	height:auto;
	width:55%;
	margin-left:1.5%;
}
#map_canvas
{
	top:20px;
	left:58%;
	position:fixed;
	height:460px;
	width:40%;
}
#pageheading
{
	color:#00f;
	font-size:36px;
	padding:5px;
	margin-left:200px;
	
}

</style>


</head>

<body>
<div>
<?php
date_default_timezone_set('Asia/Calcutta');
?>
<?php

$result = mysql_connect("localhost","root","csl@jantvdb");  //csl@jantvdb
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
	
/*	$fromd = date('Y-m-d H:i:s',strtotime("-1 days"));	
	$tod =  date('Y-m-d H:i:s');
	if(isset($_POST["fromdatetext"]))
	{
		$fromd = $_POST["fromdatetext"];
	}
	if(isset($_POST["todatetimetext"]))
	{
		$tod = $_POST["todatetimetext"];
	}
	
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

	$green = "#0f0";
	$yellow = "#ffff00";
	$red = "#f00";
	$black = "#000";
	$color = "#fff";
	
	$checkdate = 1;
	
	if(isset($_REQUEST['checkdate'])){ $checkdate = $_REQUEST['checkdate']; }
	$dd = 0;
	$rd = date_sub(date_create(date("Y-m-d")),date_interval_create_from_date_string("$dd days"));
	$requiredDate = date_format(date_sub(date_create(date("Y-m-d")),date_interval_create_from_date_string("$dd days")),"Y-m-d");

	$query = "SELECT  `name` , imei, MAX( datetime_received ) as LastActive, raw_input FROM  `pt_unit` LEFT JOIN pt_position ON pt_unit.id = pt_position.unit_id GROUP BY  pt_unit.id ORDER BY MAX( datetime_received ) desc , name";
$result = mysql_query($query);
	
?>
	<table width="95%">
	   <tr>	
		<th>User Name</th>
		<th>Mobile No</th>
		<th>Last Active Time</th>
		<th>Last Location</th>
		<th>Status</th>		
	   </tr>	
	
	

<?php 

	while($row = mysql_fetch_array($result)){   //Creates a loop to loop through results
		$lastActiveDate = $row['LastActive'];
		$ld = date_create($lastActiveDate);
	//	$rd = $requiredDate;
	    $diff=date_diff($ld,$rd);
		$formateDiff = $diff->format("%R%a");
		if ( $lastActiveDate == "") {$color = $black; }
		else if($formateDiff<=$checkdate){ $color = $green; }
		else if($formateDiff<=(2*$checkdate)){ $color = $yellow; } 
		else if($formateDiff<=(3*$checkdate)){ $color = $red; }
		
		echo "<tr><td>" . $row['name'] . "</td>";
		echo "<td>" . $row['imei'] . "</td>";
		echo "<td>". $lastActiveDate . "</td>";
		echo "<td>" . $row['raw_input'] . "</td>";
		echo "<td style='color:".$color."'>".$diff->format("%R%a days")."</td>";
		echo "</tr>";
	}
	echo "</table>";
?>
</div>

</body>
</html>
