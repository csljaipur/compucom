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


$link_user_profile_edit = htmlentities($self . '?p=user_profile_edit');
$link_track_config = htmlentities($self . '?p=track_config');
$link_pdatrack = htmlentities($self . '?p=pda_track');

$body_html = <<<EOHTML

<p>Change user account specific settings.</p>

<ul>
	<li><a href="{$link_user_profile_edit}">Edit Profile</a> - Update account username, change password.</li>
	<li><a href="{$link_track_config}">Tracker Config</a> - Add / Edit / Delete tracker units from the service.</li>
	
</ul>

EOHTML;


$template = new template();
$template->setmainnavsection('user_profile');
$template->settitle('User Profile');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>