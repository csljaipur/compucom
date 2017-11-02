<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Status Since Last Active</title>
<script language="javascript" src="jquery-1.11.3.min.js"></script>
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
	function sendnotification(event,obj,messagebox,messagediv,mobileno,name)
	{
		//alert(obj+" --- "+messagebox+" "+messagediv+"   - "+mobileno);
		var message = document.getElementById(obj).value;
		//alert(message);
		document.getElementById("notificationArea").value = name+", "+message;
		document.getElementById("hiddenMobileNo").value = mobileno;
		
		var x = event.clientX;
		var y = event.clientY;

		//alert("HELLO x = "+x+"  y = "+y);
		var d = document.getElementById('notificationPopup');
		d.style.position = "absolute";
		d.style.left = (x-360)+'px';
		d.style.top= (y-120)+'px';
		d.style.display='block'; 
	}	
	function loadAllNotification()
	{
		alert("Open all Notification Popup");	
		var d = document.getElementById('notificationAllPopup');
		
		d.style.position = "absolute";
		d.style.left = '100px';
		d.style.top=  '120px';
		d.style.display='block'; 	

		var FileName = "includes/pages/notifiactionAllPopup.php";
		$( document ).ready(function() {
		//	alert(FileName);
			$("#loadAllReportDiv").load(FileName);
		});

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
	function dispatchnotification()
	{
		var notification = document.getElementById("notificationArea").value;
		var mobileno = document.getElementById("hiddenMobileNo").value;
		
		//alert(notification+" - "+mobileno);

		var FileName = "includes/pages/sendNotification.php?mobileno="+mobileno+"&msgtext=Dear "+notification+" Compucom Software Limited";
		$( document ).ready(function() {
		//	alert(FileName);
			$.post(FileName);
		});
		CloseNotification();		
	}
	
	function CloseNotification()
	{
		var d = document.getElementById('notificationPopup');
		d.style.display='none'; 		
	}
	function CloseAllNotification()
	{
		var d = document.getElementById('notificationAllPopup');
		d.style.display='none'; 		
	}
	function goBack() {
		window.history.back();
	}	
</script>
<style>
 #notificationAllArea
{
	background-color:#FCFDFF;
	color:#303030;
	width:98%;
	border:1px solid #aaaaaa;
	height:80px;
	border-radius:8px;
	padding:6px;
	box-shadow:0px 0px 5px #999999 inset;
}
#notificationAllPopup{
    width: 96%;
    height: 110px;
	padding:10px;
    margin: 5px;
    border: 2px outset #3333FF;
    float: left;
	z-index:2;
	background-color:#FFFF99;
	color:#3333FF;
	border-radius:6px;
  }
  .notificationAllbtn
  {
	background: #00f;
	color:#FFFFFF;
	font-size:14px;
	background: -webkit-gradient(linear, left top, left bottom, from(#4466aa), to(#99aaff));
	background: -moz-linear-gradient(top,  #7799f2,  #99aaff);	  	
	border-radius:4px;
	padding:2px;
	
  } 
.btn_goBack
{
	cursor:pointer;
	color:#0000FF;
	font-weight:bold;
}
.btn_goBack:hover {
	background: #f00;
	color:#FFFFFF;
	font-size:16px;
	background: -webkit-gradient(linear, left top, left bottom, from(#aa4466), to(#ff99aa));
	background: -moz-linear-gradient(top,  #f24466,  #ff99aa);	
	
}
</style>
</head>
<body>
	<script>
		function checkunchech(obj)
		{
			$(document).ready(function() {
				$(obj).change(function() {
					if(this.checked) {
						$('input:checkbox').prop("checked", true) ;;  
					}
					else{
						$('input:checkbox').prop("checked", false) ;
					}	       
				});
			});
		}
	</script>
<div>
<?php

	mysql_query('SET character_set_results=utf8');
	mysql_query('SET names=utf8');
	mysql_query('SET character_set_client=utf8');
	mysql_query('SET character_set_connection=utf8');
	mysql_query('SET character_set_results=utf8');
	mysql_query('SET collation_connection=utf8_general_ci');
	header("Cache-Control: no-cache");

	date_default_timezone_set('Asia/Calcutta');

	$userid = -1;
	if(isset($_REQUEST['userid']))
	{
		$userid = $_REQUEST['userid'];
	}
	//echo $userid;
	$result = mysql_connect("localhost","root","csl@jantvdb"); //csl@jantvdb"); 
	if (!$result)
	{
	  die('Could not connect: ' . mysql_error());
	}
	$result = mysql_select_db("gpstracker");
	if (!$result)
	{
	  die('Could not select the database: ' . mysql_error());
	}

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
	<form action="sendNotificationAll.php" method="post">
	<div id="notificationAllPopup" style="display:block;">
		<textarea id="notificationAllArea"  name="notificationAllArea">Type your message here</textarea>
		<center><input type="submit"  id="notificationAllbtn" name="SendAllNotification" value="Send Notification To All" onclick="dispatchnotificationToAll()" />
				<input type="button"  id="closeAllnotification" name="closeAllnotification" value="Close" onclick="CloseAllNotification()" />
			<input type="button" value="Back" class="btn_goBack" onclick="goBack()" />	
		</center>
	</div>

	<table width="98%" border="1px" border-radius="8px" style="margin-top:0px; border-collapse:collapse; border:1px solid #5599ff">
	   <tr>	
	   <th width="4%"><input type="Checkbox" name="checkall" id="checkall" onclick="checkunchech(this)" />All</th>
		<th width="24%" style="padding:2px" >User Name</th>
		<th width="8%">Mobile No</th>
		<th width="12%">Last Active Time</th>
		<th width="45%">Last Location</th>
		<th width="6%">Status</th>		
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
		echo "<td><input type='hidden' name='empname$cnt' id='empname$cnt' value='". $row['name'] ."' />" . $row['name'] . "</td>";
		echo "<td>" . $row['imei'] . "</td>";
		echo "<td>". $lastActiveDate . "</td>";
		echo "<td>" . $row['raw_input'] . "</td>";
		echo "<td align='left' id='statustd' style='font-size:16px; color:".$color."; border-radius:8px;'  bgcolor='".$bgcolor."'>".$lastActivateOn."</td>";
		echo "</tr>";
	}
	echo "</table>";
?>
	<input type='hidden' name='totalcnt' id='totalcnt' value='<?php echo $cnt; ?>' />
	<center>
		<input type="submit"  id="notificationAllbtn_below" name="SendAllNotification_below" value="Send Notification To All" onclick="dispatchnotificationToAll()" />		
		<input type="button" value="Back" class="btn_goBack" onclick="goBack()" />
	</center>	
</form> 
</body>
</html>
