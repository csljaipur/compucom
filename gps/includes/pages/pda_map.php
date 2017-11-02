<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

//Set exception handler
//exceptions::sethandler();
set_exception_handler('exception_handler_image');


//Connect to database
$db = new dbmysql($cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database']);

//Authentication
$auth = new auth();
$auth->handle();
$authinfo = $auth->getauthinfo();
$authinfo_admin = $auth->getauthinfo_admin();
$auth->login_required();


if (isset($_GET['id'])) {
	$position_id = intval($_GET['id']);
} else {
	throw new Exception('Position id not specified');
}

//Retrieve specified id position
$position_result = $db->table_query($db->tbl($tbl['position']), $db->col(array('unit_id', 'lat', 'lon')), $db->cond(array("(fixtype = 2 OR fixtype = 3)", "id = {$position_id}"), 'AND'));

//If have last position
if ($position_record = $db->record_fetch($position_result)) {

	//Check user has permission to view specified position
	$unit_result = $db->table_query($db->tbl($tbl['unit']), '1', $db->cond(array("user_id = {$authinfo['id']}", "id = {$position_record['unit_id']}"), 'AND'));
	if ($db->record_count($unit_result) == 0) {
		throw new Exception("Requested position id \"{$position_id}\" however not authorised to view unit \"{$position_record['unit_id']}\" which the position is allocated to");
	}

	if ($authinfo['usergroup'] == auth::LOGIN_GROUP_USER_DEMO) {
		$style = intval($_GET['style']);
		if ($style != 0) {
			throw new Exception("Demo login requires style to be \"0\", not \"{$style}\"");
		}
	}

	//Retrieve map
	$pca = new postcodeanywhere();
	$pca->setlatlon($position_record['lat'], $position_record['lon']);
	$pca->setwidthheight($cfg['pda_map_size']['width'], $cfg['pda_map_size']['height']);
	$pca->setstyle($_GET['style']);
	$pca->setzoom($_GET['zoom']);
	$pca->generatecachename();
	$pca->checkcache();

	//Check if in cache
	if ($pca->retrievecachestatus() == false) {

		//Lookup users remaining credits
		$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('pcacredit')), $db->cond(array("id = {$authinfo['id']}"), 'AND'), '', 0, 1);
		if (!($user_record = $db->record_fetch($user_result))) {
			throw new Exception("User record not found for user id \"{$user_record}\"");
		}

		//If not in cache check credit, if have credit
		if ($user_record['pcacredit'] > 0) {

			//Retrieve map
			$pca->savemapcache();

			//Remove 1 credit
			$db->record_update($tbl['user'], 'pcacredit = pcacredit - 1', $db->cond(array("id = {$authinfo['id']}"), 'AND'));

		} else {
			throw new Exception('No credit available to retrieve map');
		}

	}

	$pca->display();

} else {
	throw new Exception("Requested position id \"{$position_id}\" not found");
}



function exception_handler_image($exception) {
	global $cfg;

	exceptions::savelogentry($exception);

	$im = imagecreate($cfg['pda_map_size']['width'], $cfg['pda_map_size']['height']) or die("Cannot Initialize new GD image stream");
	$background_color = imagecolorallocate($im, 255, 255, 255);
	$text_color = imagecolorallocate($im, 0, 0, 0);
	imagestring($im, 3, 5, 5,  "Sorry, an error has occured.", $text_color);
	imagestring($im, 3, 5, 40,  "Please report issues via", $text_color);
	imagestring($im, 3, 5, 55,  "contact form on site.", $text_color);
	header('Content-type: image/gif');
	imagegif($im);
	imagedestroy($im);

}

?>