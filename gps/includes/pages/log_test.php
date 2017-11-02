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

//if ($authinfo['username'] != 'mail@paralleltrack.co.uk') {
//	throw new Exception('Not authorised to view this page');
//}

$body_html = <<<EOHTML

<div id="admin_logtest">

	<form method="POST" action="{$cfg['site_url']}log/">

		a: <input type="text" name="a" value="test" size="80" /><br />
		v: <input type="text" name="v" value="5" size="80" /><br />
		i: <input type="text" name="i" value="111111111111111" size="80" /><br />

		d[]: <input type="text" name="d[]" value="110939.999,5018.1907N,00000.1135E,0.8,43.2,3,250.30,13.57,7.32,270607,10" size="80" /><br />
		d[]: <input type="text" name="d[]" value="201842.999,5107.2633N,00000.1790E,1.0,37.7,3,10.83,0.28,0.15,270607,10" size="80" /><br />
		d[]: <input type="text" name="d[]" value="100010.999,5018.3187N,00003.2409E,0.8,76.3,3,18.96,60.01,32.36,110707,10" size="80" /><br />
		d[]: <input type="text" name="d[]" value="202404.000,5017.4750N,00003.2650E,38.5,103.1,3,295.7,0.0,001.00,190707,03" size="80" /><br />

		<br />

		<input type="submit" value="  Send Data  " />

	</form>

</div>

EOHTML;

$template = new template();
$template->settitle('Log Test');
$template->setbodyhtml($body_html);
$template->display();

?>