<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Status Since Last Active</title>
<script language="javascript" src="includes/pages/jquery-1.9.1.js"></script>

</head>
<script language="javascript">
	function sendmessage(event,obj,messagebox,messagediv,mobileno,name)
	{
		//alert(messagebox+" "+messagediv+"   - "+mobileno);
		var message = document.getElementById(obj).value;
		//alert(message);
		document.getElementById("sendmessageArea").value = name+", "+message;
		document.getElementById("hiddenMobileNo").value = mobileno;
		
		var x = event.clientX;
		var y = event.clientY;

		//alert("HELLO x = "+x+"  y = "+y);
		var d = document.getElementById('sendmessagePopup');
		d.style.position = "absolute";
		d.style.left = (x-360)+'px';
		d.style.top= (y-120)+'px';
		d.style.display='block'; 
	}	
	
	function CloseSendMessage()
	{
		var d = document.getElementById('sendmessagePopup');
		d.style.display='none'; 		
	}
		
	function dispatchmessage()
	{
		var message = document.getElementById("sendmessageArea").value;
		var mobileno = document.getElementById("hiddenMobileNo").value;
		
		//alert(message+" - "+mobileno);

		var FileName = "http://mshastra.com/sendurlcomma.aspx?user=20067525&pwd=u6niux&senderid=Compum&mobileno="+mobileno+"&msgtext=Dear "+message+" Compucom Software Limited&smstype=0";
		$( document ).ready(function() {
			$.post(FileName);
		});
		CloseSendMessage();
	}
</script>

<body>
<div>
<?php
date_default_timezone_set('Asia/Calcutta');
?>
<?php

$result = mysql_connect("localhost","root","csl@jantvdb"); //csl@jantvdb");  //csl@jantvdb
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
	
	$query = "SELECT  `name` , imei, MAX( datetime_received ) as LastActive, raw_input, count(ppt.unit_id) as noOfTimes, MAX(ppt.id) as mid FROM  `pt_unit` LEFT JOIN pt_position ppt ON pt_unit.id = ppt.unit_id and ppt.id in (select max(id) from pt_position group by unit_id) GROUP BY  pt_unit.id ORDER BY MAX( datetime_received ) desc , name"; 
	
$result = mysql_query($query);


	
?>
	<table width="98%" style="margin-top:-12px;">
	   <tr>	
		<th>User Name</th>
		<th width="14%">Mobile No</th>
		<th>Last Active Time</th>
		<th>Last Location</th>
		<th style="line-height:15px; padding:0px; margin:0px;">Status</th>
		<th style="display:none">Message</th>		
		<th>SMS</th>
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
		
		echo "<tr><td>" . $row['name'] . "</td>";
		echo "<td>" . $row['imei'] . "</td>";
		echo "<td>". $lastActiveDate . "</td>";
		echo "<td>" . $row['raw_input'] . "</td>";
		echo "<td align='left' id='statustd' style='font-size:16px; color:".$color."; border-radius:8px;'  bgcolor='".$bgcolor."'>".$lastActivateOn."</td>";
		echo "<td style='display:none'><input type='hidden' id='sendmessage$cnt' name='sendmessage' value='".$message."'></td>";
		echo  "<td><a href='#' onclick='sendmessage(event,\"sendmessage".$cnt."\",\"sendmessageArea\",\"sendmessagePopup\",\"".$row['imei']."\",\"".$row['name']."\")'><img src='includes/pages/sms.png' alt='SMS' width='40px' height='22px' /></a></td>";
		echo "</tr>";
	}
	echo "</table>";
?>
</div>
<div id="sendmessagePopup" style="display:none;">
	<textarea id="sendmessageArea"  name="sendmessageArea">Activate GPS Tracker</textarea></form>
	<input type="hidden"  value="-1" name="hiddenMobileNo" id="hiddenMobileNo"  />
	<center><input type="button"  id="sendbtn" name="SendMessage" value="Send Message" onclick="dispatchmessage()" />
			<input type="button"  id="close" name="close" value="Close" onclick="CloseSendMessage()" />
	</center>
</div>
</body>
</html>
