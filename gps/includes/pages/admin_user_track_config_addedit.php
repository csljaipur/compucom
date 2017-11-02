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
$auth->login_required_admin();


$user_id = intval($_GET['user_id']);

$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('name')), $db->cond(array("id = {$user_id}"), 'AND'), '', 0, 1);
if (!($user_record = $db->record_fetch($user_result))) {
	throw new Exception("Unknown user id \"{$user_record}\"");
}

$user_name_h = htmlentities($user_record['name']);



//If edit
if (isset($_GET['edit'])) {

	$editid = intval($_GET['edit']);

	//Check unit exists
	$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('name')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
	if (!($unit_record = $db->record_fetch($unit_result))) {
		throw new Exception("Edit unit id \"{$editid}\" not found");
	}

	$unit_name_h = htmlentities($unit_record['name']);

} else {
	$editid = '';
	$unit_name_h = 'New unit';
}


$errormsg_html = '';

//If form posted
if (isset($_POST['formposted'])) {

	if (!$_POST['name']) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Name' not entered.</div>
EOHTML;
	}

	if (!preg_match("/^\d{10}$/", $_POST['imei'])) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Mobile no.' must be 10 digits.</div>
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
<div class="errormsg">Error: 'Mobile' already registered.</div>
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

	if (!$errormsg_html) {

		$record = array(
			'name' => $_POST['name'],
			'imei' => $_POST['imei'],
		);

		if ($_POST['password']) {
			$record = array_merge($record, array('password' => md5($_POST['password'])));
		}

		if ($editid) {
			$db->record_update($tbl['unit'], $db->rec($record), $db->cond(array("id = {$editid}"), 'AND'));
		} else {
			$record = array_merge($record, array('user_id' => $user_id));
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
		$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('name', 'imei', 'password')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
		if (!($unit_record = $db->record_fetch($unit_result))) {
			throw new Exception("Edit unit id \"{$editid}\" not found");
		}

		$formdata = $unit_record;

	} else {
	
		$formdata = array(
			'name' => '',
			'imei' => '',
			'password' => '',
		);
	}

}

if ($editid) {

	$passwordinfo = ' (only required if changed)';

} else {

	$passwordinfo = ' (1-8 characters, must match tracker password)';

}

$formdata_h = lib::htmlentities_array($formdata);

$link_back = navpd::back();
$btn_back = btn::create('<< Back', btn::TYPE_LINK, $link_back, '', '', 'Back');
$btn_save = btn::create('Save', btn::TYPE_SUBMIT);

$nav_html = <<<EOHTML
<div class="pagenav">
	<div class="left">{$btn_back}</div>
	<div class="right">{$btn_save}</div>
</div>
EOHTML;

if ($editid) {
	$args = array('id' => $editid);
} else {
	$args = array();
}

$link_self = navpd::self($args);

$body_html = <<<EOHTML

<div class="admintitlebar">User Accounts &gt; {$user_name_h} &gt; Tracker Unit(s) &gt; {$unit_name_h}</div>

<form method="post" action="{$link_self}">

{$nav_html}

{$errormsg_html}

	<input type="hidden" name="formposted" value="1" />

	<div class="row">
		<label for="name" class="inputxttitle">Name (your reference)</label>
		<input type="text" name="name" id="name" value="{$formdata_h['name']}" maxlength="255" class="inputtxt" />
	</div>

	<div class="row">
		<label for="imei" class="inputxttitle">Mobile No. (10 digit ID )</label>
		<input type="text" name="imei" id="imei" value="{$formdata_h['imei']}" maxlength="15" class="inputtxt" />
	</div>

	<div class="row">
		<label for="password" class="inputxttitle">Unit Password{$passwordinfo}</label>
		<input type="password" name="password" id="password" value="" maxlength="8" class="inputtxt" />
	</div>

	<div class="clear"></div>

{$nav_html}

</form>

EOHTML;




$template = new template();
$template->setmainnavsection('admin_user_account');
//$template->setsubnavsection('track_config');
$template->settitle('User Tracker Config');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>