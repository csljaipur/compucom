<?php

include $includes_path . 'config.php';
include $includes_path . 'general/init.php';

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

//If edit
if (isset($_GET['id'])) {

	$editid = intval($_GET['id']);

	//Auth check
	$unit_result = $db->table_query($db->tbl($tbl['unit']), '1', $db->cond(array("user_id = {$authinfo['id']}", "id = {$editid}"), 'AND'), '', 0, 1);
	if ($db->record_count($unit_result) == 0) {
		throw new Exception("Edit unit id \"{$editid}\" not found");
	}

} else {
	$editid = '';
}


$errormsg_html = '';

//If form posted
if (isset($_POST['formposted'])) {

	if ($authinfo['usergroup'] == auth::LOGIN_GROUP_USER_DEMO) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: Functionality not available in demo mode.</div>
EOHTML;
	}

	if (!$_POST['name']) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Name' not entered.</div>
EOHTML;
	}

	if (!preg_match("/^\d{10}$/", $_POST['imei'])) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Mobile' must be 10 digits.</div>
EOHTML;
	}

	//Check imei does not already exist (unless it is for the unit that is being edited now)
	$cond = array("imei = '".$db->es($_POST['imei'])."'");
	if ($editid) {
		$cond = array_merge($cond, array("id != {$editid}"));
	}

	$unit_result = $db->table_query($db->tbl($tbl['unit']), '1', $db->cond($cond, 'AND'), '', 0, 1);
	if ($db->record_count($unit_result) > 0) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'IMEI' already registered.</div>
EOHTML;
	}

	if ( (!$editid) && (!$_POST['password']) ) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Password' not entered.</div>
EOHTML;
	}

	if ( ($_POST['password']) && (strlen($_POST['password']) > 8) ) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Password' must be 1-8 characters.</div>
EOHTML;
	}

	if (!isset($cfg['icon'][$_POST['icon']])) {
		throw new Exception("Edit unit id \"{$editid}\" icon not valid");
	}

	if (!isset($cfg['linecol'][$_POST['linecol']])) {
		throw new Exception("Edit unit id \"{$editid}\" linecol not valid");
	}

	if (!$errormsg_html) {

		$record = array(
			'name' => $_POST['name'],
			'imei' => $_POST['imei'],
			'icon' => $_POST['icon'],
			'linecol' => $_POST['linecol'],
		);

		if ($_POST['password']) {
			$record = array_merge($record, array('password' => md5($_POST['password'])));
		}

		if ($editid) {
			$db->record_update($tbl['unit'], $db->rec($record), $db->cond(array("id = {$editid}"), 'AND'));
		} else {
			$record = array_merge($record, array('user_id' => $authinfo['id']));
			$db->record_insert($tbl['unit'], $db->rec($record));
			$editid = $db->record_insert_id();
		}

		header("Location: {$config['site_url']}".navpd::back());

	}

}





if (isset($_POST['formposted'])) {

	$formdata = $_POST;

} else {

	if ($editid) {

		//List unit info
		$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('name', 'imei', 'password', 'icon', 'linecol')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
		if (!($unit_record = $db->record_fetch($unit_result))) {
			throw new Exception("Edit unit id \"{$editid}\" not found");
		}

		$formdata = $unit_record;

	} else {
	
		$formdata = array(
			'name' => '',
			'imei' => '',
			'password' => '',
			'icon' => 0,
			'linecol' => 0,
		);
	}

}

if ($editid) {

	$passwordinfo = ' (only required if changed)';

} else {

	$passwordinfo = ' (1-8 characters, must match tracker password)';

}

$formdata_h = lib::htmlentities_array($formdata);

$icon_html = '';
foreach ($cfg['icon'] as $icon_id => $icon) {

	if ($formdata['icon'] == $icon_id) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}

	$iconpath = $icon['img']['config'];

	$name_h = htmlentities($icon['name']);

	$icon_html .= <<<EOHTML
<div class="icongroup">
	<img src="{$iconpath}" width="48" height="48" onclick="$('icon_{$icon_id}').checked = true" alt="{$name_h}" title="{$name_h}" />
	<input type="radio" id="icon_{$icon_id}" value="{$icon_id}" {$checked} name="icon" class="iconselect" />
</div>

EOHTML;

}

$linecol_html = '';
foreach ($cfg['linecol'] as $linecol_id => $linecol) {

	if ($formdata['linecol'] == $linecol_id) {
		$checked = 'checked="checked"';
	} else {
		$checked = '';
	}

	$linecol_html .= <<<EOHTML
<div class="colorgroup">
	<div class="colorsample" style="width: 48px; height: 48px; background-color: {$linecol['html']}" onclick="$('color_{$linecol_id}').checked = true"></div>
	<input type="radio" value="{$linecol_id}" id="color_{$linecol_id}" {$checked} name="linecol" class="colorselect" />
</div>
EOHTML;

}

$link_back = navpd::back();

$nav_html = <<<EOHTML
<div class="pagenav">
	<div class="left"><a href="{$link_back}">&lt;&lt; Back</a></div>
	<div class="right"><input type="submit" value=" Save " name="save" /></div>
</div>
EOHTML;

if ($editid) {
	$args = array('id' => $editid);
} else {
	$args = array();
}

$link_self = navpd::self($args);

$body_html = <<<EOHTML
<form method="post" action="{$link_self}">

{$nav_html}

{$errormsg_html}

	<input type="hidden" name="formposted" value="1" />

	<div class="row">
		<label for="name" class="inputxttitle">Name (your reference)</label>
		<input type="text" name="name" id="name" value="{$formdata_h['name']}" maxlength="255" class="inputtxt" />
	</div>

	<div class="row">
		<label for="imei" class="inputxttitle">Mobile (10 digit ID of tracker unit)</label>
		<input type="text" name="imei" id="imei" value="{$formdata_h['imei']}" maxlength="10" class="inputtxt" />
	</div>

	<div class="row">
		<label for="password" class="inputxttitle">Unit Password{$passwordinfo}</label>
		<input type="password" name="password" id="password" value="" maxlength="8" class="inputtxt" />
	</div>

	<div class="row">
		<label class="inputxttitle">Icon</label>
{$icon_html}
	</div>

	<div class="row">
		<label class="inputxttitle">Line Colour</label>
{$linecol_html}
	</div>

	<div class="clear"></div>

{$nav_html}

</form>

EOHTML;




$template = new template();
$template->setmainnavsection('user_profile');
$template->setsubnavsection('track_config');
$template->settitle('Tracker Configuration');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>