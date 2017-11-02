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

$link_pda = htmlentities($cfg['site_url'] . 'pda/');
$link_pdademo = htmlentities($cfg['site_url'] . '?p=pda&demo=1');

//Lookup users remaining credits
$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('pcacredit')), $db->cond(array("id = {$authinfo['id']}"), 'AND'), '', 0, 1);
if (!($user_record = $db->record_fetch($user_result))) {
	throw new Exception("User record not found for user id \"{$user_record}\"");
}

$pcacredit = $user_record['pcacredit'];

$body_html = <<<EOHTML

<p>PDA track allows you to view the current location of a specified vehicle on a portable device running e.g. Windows Mobile, Pocket PC which do not support the full maps display interface.</p>

<p>To use PDA live track please visit <a href="{$link_pda}">{$link_pda}</a> in your mobile phone / PDA, and login (demo available at <a href="{$link_pdademo}">{$link_pdademo}</a>).</p>

<p>Please note that each map view, changing of map style, change of zoom level will use up one credit.</p>

<p><strong>Current Credits: {$pcacredit}</strong></p>

EOHTML;


$template = new template();
$template->setmainnavsection('user_profile');
$template->settitle('PDA Track');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>