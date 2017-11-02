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
	
		
$query = "SELECT  `id`, `name` , imei  FROM  `pt_unit`  ORDER BY `name`"; 
	
$result = mysql_query($query);
	
?>
<select id="userCombo" name="userCombo" onchange="getUserReport(this)">
	<option value="-1" selected="selected">Select User</option>
<?php 

	while($row = mysql_fetch_array($result)){ ?>  
		<option value="<?php echo $row['id'];?>"><?php echo $row['name']." - ".$row['imei'];?></option>
<?php } ?>
</select> 

</div>

</body>
</html>