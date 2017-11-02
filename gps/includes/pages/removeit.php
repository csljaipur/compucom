<?php
	$removemessage = "";
	$id = $_REQUEST['id'];
	$userentry = $_REQUEST['userentry'];	

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

	$result_del = mysql_query($deletit);
	$removemessage .= $userentry." - Successfully removed";

	echo $removemessage;
?>