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


//Retrieve all valid timezones
$timezones = DateTimeZone::listIdentifiers();

$successmsg_html = '';
$errormsg_html = '';

//If form posted
if (isset($_POST['formposted'])) {

	if ($authinfo['usergroup'] == auth::LOGIN_GROUP_USER_DEMO) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: Functionality not available in demo mode.</div>
EOHTML;
	}

	if (!lib::chkemailvalid($_POST['username'])) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Email' not valid.</div>
EOHTML;
	}

	if ( ($_POST['password'] || $_POST['password_confirm']) && ($_POST['password'] != $_POST['password_confirm']) ) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Password' / 'Confirm Password' do not match.</div>
EOHTML;
	}

	if (!$_POST['name']) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Name' not entered.</div>
EOHTML;
	}

	$user_result = $db->table_query($db->tbl($tbl['user']), '1', "username = '" . $db->es($_POST['username']). "' AND id != {$authinfo['id']}", '', 0, 1);
	if ($db->record_count($user_result) > 0) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Email' already registered.</div>
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

	if (!$errormsg_html) {

		$record = array(
			'name' => $_POST['name'],
			'username' => $_POST['username'],
			'timezone' => $_POST['timezone'],
		);

		if ($_POST['password']) {
			$record = array_merge($record, array('password' => md5($_POST['password'])));
		}

		$db->record_update($tbl['user'], $db->rec($record), $db->cond(array("id = {$authinfo['id']}"), 'AND'));

		//If an admin is not logged in, or an admin is logged in, but not using the override
		if ( (!isset($authinfo_admin['id'])) || ( (isset($authinfo_admin['id'])) && ($authinfo_admin['id'] == $authinfo['id']) ) ) {

			//Resave cookie
			$authinfo = array_merge($authinfo, $record);

			$auth->setuser($authinfo['username'], $authinfo['password']);
			$auth->cookie_save();

		}

		//header("Location: {$config['site_url']}".navpd::back());

		$successmsg_html = <<<EOHTML
<div class="successmsg">Success: Profile Updated.</div>
EOHTML;

	}

}

$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('name', 'username', 'timezone')), $db->cond(array("id = {$authinfo['id']}"), 'AND'));
if (!($user_record = $db->record_fetch($user_result))) {
	throw new Exception("Edit user id \"{$editid}\" not found");
}


if (isset($_POST['formposted'])) {
	$formdata = $_POST;
} else {
	$formdata = $user_record;
}

$formdata_h = lib::htmlentities_array($formdata);

$link_back = htmlentities($self . '?p=user_profile');

$nav_html = <<<EOHTML
<div class="pagenav">
	<div class="left"><a href="{$link_back}">&lt;&lt; Back</a></div>
	<div class="right"><input type="submit" value=" Save " name="save" /></div>
</div>
EOHTML;

$link_self = navpd::self();

$timezone_options_html = '';
foreach ($timezones as $timezone) {

	$selected = ($timezone == $formdata['timezone']) ? ' selected="selected"' : '';

	$timezone_h = htmlentities($timezone);

	$timezone_options_html .= <<<EOHTML
<option{$selected}>{$timezone_h}</option>
EOHTML;

}

$body_html = <<<EOHTML
<form method="post" action="{$link_self}">

{$nav_html}

{$successmsg_html}

{$errormsg_html}

	<div><input type="hidden" name="formposted" value="1" /></div>

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
		<label for="password_confirm" class="inputxttitle">Password Confirm (retype password)</label>
		<input type="password" name="password_confirm" id="password_confirm" value="" maxlength="8" class="inputtxt" />
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
$template->setmainnavsection('user_profile');
//$template->setsubnavsection('user_profile_edit');
$template->settitle('User Profile - Edit');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>