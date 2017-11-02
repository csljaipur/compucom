<html>
<head><title>Report of GPS Tracker</title>
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
.reportheading
{
	text-align:center;
	font-size:24px;
	color:#00f;
}
</style>
</head>
<body>




<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';
//date_default_timezone_set('Asia/Calcutta');
//date_default_timezone_set('Asia/Kolkata');
//Set exception handler
exceptions::sethandler();

//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

//Authentication
$auth = new auth();
$auth->handle();
$authinfo = $auth->getauthinfo();
$authinfo_admin = $auth->getauthinfo_admin();
$auth->login_required();


$unit_id = isset($_GET['unit']) ? intval($_GET['unit']) : 0;

$cond = array();
$cond = array_merge($cond, array("unit_id = {$unit_id}")); //, "(fixtype = 2 OR fixtype = 3)"
$cond = array_merge($cond, history::condition($_GET));

//Retrieve unit info
$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('name')), $db->cond(array("user_id = {$authinfo['id']}", "id = {$unit_id}"), 'AND'));
if (!($unit_record = $db->record_fetch($unit_result))) {
	throw new Exception("Unit \"{$unit_id}\" not found / not authorised to view");
}



$csvarray = array();

$result_position = $db->table_query($db->tbl($tbl['position']), $db->col(array('datetime', 'lat', 'lon', 'raw_input')), $db->cond($cond, 'AND'), $db->order(array(array('id', 'ASC'))));
while ($record_position = $db->record_fetch($result_position)) {

	$csvarray[] = $record_position;
	//lib::prh($record_position);
}


$name = $unit_record['name'];
$name = preg_replace("%[^a-zA-Z0-9\ ]%", '', $name);

$filename = $name;
$filename = strtolower(str_replace(' ', '_', $filename)).'.html';

header('Content-type: text/html');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
?>

	<table width="95%">
		<tr  class="reportheading"><th colspan="5"><?php echo "Report of ".$name; ?></th></tr>
	   <tr>	
	    <th>S. No.</th>
	    <th>Date Time</th>
		<th>Latitute</th>
		<th>Lonfitute</th>
		<th>Location</th>		
	   </tr>
<?php
if (count($csvarray) > 0) {
	echo arraytohtml($csvarray);
}
//Array to csv format file
function arraytohtml($csvarray) {

	$csvdata = '';

	//Get title row
	if (count($csvarray > 0)) {
		foreach ($csvarray[0] as $title => $null) {
			$title = str_replace('"', '""', $title);
			//$csvdata .= "\"{$title}\",";
		}

		//$csvdata = rtrim($csvdata, ',');
		//$csvdata .= "\n";
	}

	//Go through all the row data, adding it to csv file
	$sno = 0;
	foreach ($csvarray as $rowdata) {
		$sno +=1;
		$csvdata .= "<tr><td>".$sno."</td>";
		foreach ($rowdata as $rowitem) {
			
			$rowitem = str_replace('"', '""', $rowitem);
			  $csvdata .= "<td>".$rowitem."</td>";
			//$csvdata .= "\"{$rowitem}\",";
		}

		$csvdata = rtrim($csvdata, ',');
		$csvdata .= "</tr>";

	}

	return $csvdata;

}

?>

	</table>
</body>
</html>