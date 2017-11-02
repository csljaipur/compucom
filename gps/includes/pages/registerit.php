<?php
	$registermessage = "Register Message - ";
	$id = $_REQUEST['id'];
	$userentry = $_REQUEST['userentry'];	
	$userName = $_REQUEST['userName'];

	$check_query = "Select * from pt_unit where imei = '".$userentry."'";		
//INSERT INTO `pt_unit`(`id`, `user_id`, `name`, `imei`, `password`, `icon`, `linecol`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7])		
	$registerit = "INSERT INTO `pt_unit`(`user_id`, `name`, `imei`, `password`, `icon`, `linecol`) VALUES (1,'".$userName."','".$userentry."','".$userentry."123',0,0)";

	$deletit = "DELETE FROM `pt_position_unreguser` WHERE id  = $id";
	
	$result = mysql_connect("localhost","root","csl@jantvdb"); //csl@jantvdb"
	if (!$result)
	{
	  die('Could not connect: ' . mysql_error());
	}
	$result = mysql_select_db("gpstracker");
	if (!$result)
	{
	  die('Could not select the database: ' . mysql_error());
	}
	

	$result_check = mysql_query($check_query);
	//$rowcnt = mysql_num_rows ($result_check );
	
	if($row_check = mysql_fetch_array($result_check))
	{
		$result_del = mysql_query($deletit);
		$registermessage .= $userentry." - Already Registered as - ".$row_check["name"]." , removed from Unregister";
	}
	else
	{
		$result = mysql_query($registerit);
		$result_del = mysql_query($deletit);
		$registermessage .= $userentry." - Successfully Register as - ".$row_check["name"]." , removed from Unregister";
	}
	
	echo $registermessage;
?>