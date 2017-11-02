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


//If edit
if (isset($_GET['edit'])) {

	$editid = intval($_GET['edit']);

	//Check user exists / retrieve user name
	$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('name', 'usergroup')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
	if (!($user_record = $db->record_fetch($user_result))) {
		throw new Exception("Edit unit id \"{$editid}\" not found");
	}

	if ( ($user_record['usergroup'] == auth::LOGIN_GROUP_ADMIN_MAIN) ) {
		throw new Exception('Can not edit main user');
	}

	$user_name_h = htmlentities($user_record['name']);

} else {
	$editid = '';
	$user_name_h = 'New user';
}


//Retrieve array of user ids / groups
$usergroup_options = array();
foreach ($cfg['user_group'] as $group_id => $group) {
	if (auth::LOGIN_GROUP_ADMIN_MAIN != $group_id) {
		$usergroup_options[$group_id] = $group['name'];
	}
}


//Retrieve all valid timezones
$timezones = DateTimeZone::listIdentifiers();

$errormsg_html = '';

//If form posted
if (isset($_POST['formposted'])) {

	if (!lib::chkemailvalid($_POST['username'])) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Email' not valid.</div>
EOHTML;
	}

	if (!$_POST['name']) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Name' not entered.</div>
EOHTML;
	}

	$user_result = $db->table_query($db->tbl($tbl['user']), '1', "username = '" . $db->es($_POST['username']). "' AND id != '{$editid}'", '', 0, 1);
	if ($db->record_count($user_result) > 0) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Email' already registered.</div>
EOHTML;
	}

	if ( (!$editid) && (!$_POST['password']) ) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Password' not entered..</div>
EOHTML;
	}

	if ( ($_POST['password']) && (strlen($_POST['password']) < 4) ) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Password' must be 4 characters or more.</div>
EOHTML;
	}

	if (!in_array($_POST['timezone'], $timezones)) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Timezone' not valid.</div>
EOHTML;
	}

	if (!in_array($_POST['usergroup'], array_keys($usergroup_options))) {
		throw new Exception("Unknown user group id \"{$_POST['usergroup']}\"");
	}



	if (!$errormsg_html) {

		$record = array(
			'name' => $_POST['name'],
			'username' => $_POST['username'],
			'timezone' => $_POST['timezone'],
			'pcacredit' => intval($_POST['pcacredit']),
			'usergroup' => $_POST['usergroup'],
		);

		if ($_POST['password']) {
			$record = array_merge($record, array('password' => md5($_POST['password'])));
		}

		if ($editid) {
			$db->record_update($tbl['user'], $db->rec($record), $db->cond(array("id = {$editid}"), 'AND'));
		} else {
			$record = array_merge($record, array('registered' => $db->datetimenow()));
			$db->record_insert($tbl['user'], $db->rec($record));
			$editid = $db->record_insert_id();
		}

		header("Location: {$config['site_url']}".navpd::back());

		/*
		$successmsg_html = <<<EOHTML
<div class="successmsg">Success</div>
EOHTML;
		*/

	}

}





if (isset($_POST['formposted'])) {

	$formdata = $_POST;

} else {

	if ($editid) {

		//List user info
		$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('name', 'username', 'password', 'pcacredit', 'timezone', 'usergroup')), $db->cond(array("id = {$editid}"), 'AND'), '', 0, 1);
		if (!($user_record = $db->record_fetch($user_result))) {
			throw new Exception("Edit user id \"{$editid}\" not found");
		}

		$formdata = $user_record;

	} else {

		$formdata = array(
			'name' => '',
			'username' => '',
			'password' => '',
			'pcacredit' => '0',
			'timezone' => $cfg['timezone_default'],
			'usergroup' => auth::LOGIN_GROUP_USER,
		);
	}

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


$timezone_options_html = '';
foreach ($timezones as $timezone) {

	$selected = ($timezone == $formdata['timezone']) ? ' selected="selected"' : '';

	$timezone_h = htmlentities($timezone);

	$timezone_options_html .= <<<EOHTML
<option{$selected}>{$timezone_h}</option>
EOHTML;

}

$usergroup_options_html = lib::create_options($usergroup_options, $formdata['usergroup']);

$body_html = <<<EOHTML

<div class="admintitlebar">User Accounts &gt; {$user_name_h}</div>

<form method="post" action="{$link_self}">

{$nav_html}

{$errormsg_html}

	<input type="hidden" name="formposted" value="1" />

	<div class="row">
		<label for="name" class="inputxttitle">Name</label>
		<input type="text" name="name" id="name" value="{$formdata_h['name']}" maxlength="255" class="inputtxt" />
	</div>

	<div class="row">
		<label for="username" class="inputxttitle">Email</label>
		<input type="text" name="username" id="username" value="{$formdata_h['username']}" maxlength="255" class="inputtxt" />
	</div>

	<div class="row">
		<label for="password" class="inputxttitle">Password (only required if changed)</label>
		<input type="password" name="password" id="password" value="" maxlength="8" class="inputtxt" />
	</div>

	<div class="row">
		<label for="usergroup" class="inputxttitle">Type</label>
		<select size="1" name="usergroup" id="usergroup">{$usergroup_options_html}</select>
	</div>

	<div class="row">
		<label for="pcacredit" class="inputxttitle">PDA Credit</label>
		<input type="text" name="pcacredit" id="pcacredit" value="{$formdata_h['pcacredit']}" maxlength="3" class="inputtxt" />
	</div>

	<div class="row">
		<label for="timezone" class="inputxttitle">Timezone</label>
		<select size="1" name="timezone" id="timezone">{$timezone_options_html}</select>
	</div>

	<div class="clear"></div>

{$nav_html}

</form>

EOHTML;




$template = new template();
$template->setmainnavsection('admin_user_account');
//$template->setsubnavsection('track_config');
$template->settitle('User Add/Edit');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>