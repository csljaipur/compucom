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
	$blue = "#00f";	
	$green = "#0f0";
	$yellow = "#ffff00";
	$red = "#dd0000";
	$black = "#666";
	$white = "#fff";
	$color = "#fff";
	
	$bgcolor = "#000";
	$query = "SELECT `id`, `user_entry`, `datetime`, `datetime_received`, `lat`, `lon`, `alt`, `deg`, `speed_km`, `speed_kn`, `sattotal`, `fixtype`, `raw_input`, `hash` FROM `pt_position_unreguser` order by datetime_received desc"; 
	
$result = mysql_query($query);
	
?>

	<table width="98%" style="margin-top:-12px;">
	   <tr>
	    <th>SNo.</th>	
		<th>User Entry</th>
		<th>Last Active Time</th>
		<th>Last Location</th>
		<th>Register</th>
		<th>Remove</th>		
	   </tr>	

<?php 
	$i=0;
	while($row = mysql_fetch_array($result)){   //Creates a loop to loop through results
		$i++;
		$lastActiveDate = $row['datetime_received'];
		
		echo "<tr><td>" . $i . "</td>";
		echo "<td>" . $row['user_entry'] . "</td>";
		echo "<td>". $lastActiveDate . "</td>";
		echo "<td>" . $row['raw_input'] . "</td>";
	//	echo "<td><a href='#' onclick='registerit($row[\"id\"],$row[\"user_entry\"])'>register</a></td>";
		echo "<td><a href='#' onclick='popupRegisterWindow(event,".$row["id"].",\"".$row["user_entry"]."\",\"txtUserName\")'><img src='includes/pages/register.png' alt='register' width='30px;'  height='30px;' /></a></td>";
		echo "<td><a href='#' onclick='popupRemoveWindow(event,".$row["id"].",\"".$row["user_entry"]."\")'><img src='includes/pages/remove.png' alt='remove' width='30px' height='30px;' /> </a></td>";
		echo "</tr>";
	}
	echo "</table>";
?>