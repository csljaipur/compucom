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
$authinfo_admin = $auth->getauthinfo();
$auth->login_required();

if ($authinfo_admin['usergroup'] != auth::LOGIN_GROUP_ADMIN_MAIN) {
	throw new Exception('Not authorised to view this page');
}

exceptions::viewlogs();

?>