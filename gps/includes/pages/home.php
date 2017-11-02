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
//$auth->login_required();




$link_contact = htmlentities($self . '?p=contact');

$registered = false;

$fields = array('username', 'password', 'password_confirm');

$link_self = navpd::self_h();

$errormsg_html = '';

//Handle new user registration
if ( (isset($_POST['formposted_register'])) && (!isset($authinfo['id'])) ) {

	$chkfields = array(
		'username' => 'Email',
		'password' => 'Password',
		'password_confirm' => 'Confirm Password',
	);

	foreach ($chkfields as $chkfield => $chkfieldname) {
		if (!$_POST[$chkfield]) {
			$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: '{$chkfieldname}' not entered.</div>
EOHTML;
		}
	}

	if (!lib::chkemailvalid($_POST['username'])) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Email' not valid.</div>
EOHTML;
	}

	if ($_POST['password'] != $_POST['password_confirm']) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Password' / 'Confirm Password' do not match.</div>
EOHTML;
	}

	if (strlen($_POST['password']) < 4) {
		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: 'Password' must be 4 characters or more.</div>
EOHTML;
	}


	$user_result = $db->table_query($db->tbl($tbl['user']), '1', "username = '" . $db->es($_POST['username']). "'", '', 0, 1);
	if ($db->record_count($user_result) > 0) {

		$errormsg_html .= <<<EOHTML
<div class="error">Error: Email address already been reistered, if you have forgotten your password, please <a href="{$link_contact}">contact us</a>.</div>
EOHTML;

	}

	if (!$errormsg_html) {

		$md5password = md5($_POST['password']);

		$record = array(
			'username' => $_POST['username'],
			'password' => $md5password,
			'timezone' => $cfg['timezone_default'],
			'registered' => $db->datetimenow(),
			'lastlogin' => $db->datetimenow(),
		);

		$db->record_insert($tbl['user'], $db->rec($record));
		$user_id = $db->record_insert_id();

		$authinfo = array_merge($record, array('id' => $user_id));

		$auth->setuser($_POST['username'], $md5password);
		$auth->cookie_save();

		$loggedin = true;
		$registered = true;

	}

}



//If logged in, show logged in, do not show register
if (isset($authinfo['id'])) {

	$useremail_h = htmlentities($authinfo['username']);

	$link_logout = htmlentities($self . '?p=home&logout=1');

	$panelright_html = <<<EOHTML
		<div class="sidepanelbox">

			<div class="sidepanelboxtitle">Logged In&nbsp;&nbsp;[<a href="{$link_logout}">Logout</a>]</div>
			<div class="sidepanelboxcontent">

				<strong>User:</strong> {$useremail_h}

			</div>
		
		</div>
EOHTML;

	if ($registered) {

		$link_track_config = htmlentities($self . '?p=track_config');

		$panelright_html = <<<EOHTML
		<div class="sidepanelbox">

			<div class="sidepanelboxtitle">Registration Success</div>
			<div class="sidepanelboxcontent">
				<p>Thank you for registering, please proceed to the <a href="{$link_track_config}">Tracker Configuration</a> page to add vehicles.</p>
			</div>
		
		</div>
EOHTML;

	}

} else {

	if (isset($_POST['formposted_register'])) {
		$formdata = $_POST;

		$formdata['password'] = '';
		$formdata['password_confirm'] = '';

	} else {
		$formdata = lib::prepare_formdata($fields);
	}

	$formdata_h = lib::htmlentities_array($formdata);

	$link_login = htmlentities($self . '?p=track_live');

	$link_logindemo = htmlentities($self . '?p=track_live&demo=1');

	$link_tandcs = htmlentities($self . '?p=terms_and_conditions');

	$tandcs_checked = ( (isset($_POST['tandcs'])) && ($_POST['tandcs']) ) ? 'checked="checked"' : '';

	$panelright_html = <<<EOHTML

		<div class="sidepanelbox">

			<div class="sidepanelboxtitle">Login</div>
			<div class="sidepanelboxcontent">

				<form method="post" action="{$link_login}">

					<label for="login_username" class="inputxttitle">Email</label>
					<input type="text" name="login_username" id="login_username" maxlength="255" class="inputxt" />

					<label for="login_password" class="inputxttitle">Password</label>
					<input type="password" name="login_password" id="login_password" maxlength="255" class="inputxt" />

					<div class="autologin">
						<label for="login_auto">Autologin:</label> <input type="checkbox" name="login_auto" id="login_auto" value="1" />
					</div>

					<input type="submit" value="Login" name="login" />

				</form>

			</div>
		
		</div>

		


		<script type="text/javascript">
		  //<![CDATA[

			function checkform() {

				
					return true;
				

			}

		  //]]>
		</script>


EOHTML;

}





$link_loginpdademo = htmlentities($self . '?p=pda&demo=1');

$link_trackbox = htmlentities($self . '?p=trackbox');
$link_systems_integrators = htmlentities($self . '?p=systems_integrators');

$body_html = <<<EOHTML
<div class="yui-ge">

	<div class="yui-u first mainpanel">

		<div class="maincontent">

			<a href="resources/images/home/map_screenshot.jpg" target="_blank"><img src="resources/images/home/map_screenshot_small.jpg" width="260" align="right" alt="Map screenshot" class="screenshotimg" /></a>

			<p><em>CSL GPS Tracker is a web based tracking service, allowing you to quickly and easily find the location of your device online.</em></p>

			<h2>Features:</h2>

			<ul>
				<li>Multiple Device display.</li>
				<li>History feature showing activity for previous days. exportable in CSV & HTML Format</li>				
			</ul>

			<p>For details please see <a href="http://www.compucom.co.in/">Compucom Software Limited</a>.</p>

		</div>

	</div>

	<div class="yui-u">
{$panelright_html}
	</div>

</div>


EOHTML;

$template = new template();
$template->settitlefull('CSL GPS Tracker - GPS based tracking software');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>