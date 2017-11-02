<?php
date_default_timezone_set('Asia/Calcutta');
?>
<?php
$userid = -1;
if(isset($_REQUEST['userid']))
{
	$userid = $_REQUEST['userid'];
}
//echo $userid;
$result = mysql_connect("localhost","root","csl@jantvdb"); //csl@jantvdb"); //csl@jantvdb");  //csl@jantvdb
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
	
	$checkdate = 1;
	
	if(isset($_REQUEST['checkdate'])){ $checkdate = $_REQUEST['checkdate']; }
	$dd = 0;
	$rd = date_sub(date_create(date("Y-m-d")),date_interval_create_from_date_string("$dd days"));
	$requiredDate = date_format(date_sub(date_create(date("Y-m-d")),date_interval_create_from_date_string("$dd days")),"Y-m-d");
	
	if($userid==1 || $userid==-1)
	{
		$query = "SELECT  `name` , imei, MAX( datetime_received ) as LastActive, raw_input, count(ppt.unit_id) as noOfTimes, MAX(ppt.id) as mid FROM  `pt_unit` LEFT JOIN pt_position ppt ON pt_unit.id = ppt.unit_id and ppt.id  in (select max(id) from pt_position group by unit_id)  GROUP BY  pt_unit.id ORDER BY MAX( datetime_received )  desc , name"; 		
	}
	else
	{
		$query = "SELECT  `name` , imei, MAX( datetime_received ) as LastActive, raw_input, count(ppt.unit_id) as noOfTimes, MAX(ppt.id) as mid FROM  `pt_unit` LEFT JOIN pt_position ppt ON pt_unit.id = ppt.unit_id and ppt.id  in (select max(id) from pt_position group by unit_id) where   user_id = $userid  GROUP BY  pt_unit.id ORDER BY MAX( datetime_received )  desc , name"; 
	}	
$result = mysql_query($query);


	
?>
	<table width="98%" style="margin-top:-12px;">
	   <tr>	
		<th><input type="Checkbox" name="checkall" id="checkall" />Select All</th>
		<th>User Name</th>
		<th width="14%">Mobile No</th>
		<th style="line-height:15px; padding:0px; margin:0px;">Status</th>		
	   </tr>	
	
	

<?php 
	$cnt = 0;
	while($row = mysql_fetch_array($result)){   //Creates a loop to loop through results
		$cnt++;
		$lastActiveDate = $row['LastActive'];
		$ld = date_create($lastActiveDate);
	//	$rd = $requiredDate;
	    $diff=date_diff($ld,$rd);
		$formateDiff = $diff->format("%R%a");
		$lastActivateOn = $diff->format("%R%a days");
		if($lastActivateOn == "-0 days" )
		{
			if ($lastActiveDate == "")
			{
				$message = " Please activate GPS Tracker - Your are not activated since registered.";
			}
			else
			{
				$message = " Thanks for activating GPS Tracker Today.";
			}
			
		}
		else
		{
			$message = " Please activate GPS Tracker - Your Tracker is last activated - ". $lastActivateOn." back.";
		}

		if ( $lastActiveDate == "") {$color = $white; $bgcolor = $black; }
		else if($formateDiff<=$checkdate){ $color = $blue; $bgcolor = $green;}
		else if($formateDiff<=(2*$checkdate)){ $color = $red; $bgcolor = $yellow; } 
		else { $color = $white;  $bgcolor = $red; }
		
		echo "<tr><td><input type='Checkbox' name='checkall$cnt' id='checkall$cnt' value='".$row['imei']."' /></td>";
		echo "<td>" . $row['name'] . "</td>";
		echo "<td>" . $row['imei'] . "</td>";
		echo "<td align='left' id='statustd' style='font-size:16px; color:".$color."; border-radius:8px;'  bgcolor='".$bgcolor."'>".$lastActivateOn."</td>";
		echo "<td style='display:none'><input type='hidden' id='sendmessage$cnt' name='sendmessage' value='".$message."'></td>";			
		echo "</tr>";
	}
	echo "</table>";
?>