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

//Delete unit
if ( (isset($_POST['actiontype'])) && ($_POST['actiontype'] == 'delete') ) {

	$actionid = intval($_POST['actionid']);

	$unit_result = $db->table_query($db->tbl($tbl['unit']), '1', $db->cond(array("user_id = {$user_id}", "id = {$actionid}"), 'AND'), '', 0, 1);
	if (!($unit_record = $db->record_fetch($unit_result))) {
		throw new Exception("Unit not found for specified user");
	}

	$db->record_delete($tbl['unit'], $db->cond(array("id = ".$actionid), 'AND'));

}

$link_addnew = navpd::forward(array('p' => 'admin_user_track_config_addedit', 'user_id' => $user_id));
$btn_addnew = btn::create('Add Tracker', btn::TYPE_LINK, $link_addnew, '', '', 'Add Tracker');
$right_html = $btn_addnew;

//Table
$tablehtml = new tablehtml();
$tablehtml->shotbuttoncol = true;
$tablehtml->sortby = 'name';
//$tablehtml->table_class = 'examplelist';
$tablehtml->sortable_columns = array('name', 'imei');
$tablehtml->parsegetvars($_GET);

$tablehtml->addcolumn('name', 'Name');
$tablehtml->addcolumn('imei', 'Mobile');
$tablehtml->addcolumn('posdata', 'Active');
$tablehtml->addcolumn('button', '');

$table_html = $tablehtml->html(
	$tablehtml->html_action(),
	$tablehtml->html_table(
		$tablehtml->html_table_titles(),
		$tablehtml->html_table_rows(
			$tablehtml->tabledatahtml_fromcallback('callback_tabledatahtml')
		),
		$tablehtml->html_table_nav($right_html),
		$tablehtml->html_table_errors()
	)
);

function callback_tabledatahtml($tablehtml, $limit_offset, $limit_count, $query_order) {
	global $cfg, $tbl, $db, $user_id;

	$result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id', 'name', 'imei')), $db->cond(array("user_id = {$user_id}"), 'AND'), $db->order($query_order), $limit_offset, $limit_count, dbmysql::TBLQUERY_FOUNDROWS);

	$tabledatahtml = array();
	while ($data = $db->record_fetch($result)) {

		$datah = lib::htmlentities_array($data);

		//Edit button
		$link_edit = navpd::forward(array('p' => 'admin_user_track_config_addedit', 'edit' => $data['id'], 'user_id' => $user_id));
		$btn_edit = btn::create('Edit', btn::TYPE_LINK, $link_edit, '', '', 'Edit Tracker');

		//Delete button
		$name_js = addslashes($datah['name']);
		$link_self = addslashes(navpd::self());
		$onclick_js = "performaction('{$link_self}', 'Really delete tracker, saved position data?', 'delete', {$data['id']}); return false;";
		$btn_delete = btn::create('Delete', btn::TYPE_LINK, '#', '', $onclick_js, "Delete user \"{$name_js}\"");

		$buttons = $tablehtml->html_table_buttons(array($btn_edit, $btn_delete));

		//Find out if there is position data for this unit
		$position_result = $db->table_query($db->tbl($tbl['position']), '1', $db->cond(array("unit_id = {$data['id']}"), 'AND'), '', 0, 1);
		if ($position_record = $db->record_fetch($position_result)) {
			$posdata = 'Yes';
		} else {
			$posdata = 'No';
		}

		$tabledatahtml[] = array(
			'name' => $datah['name'],
			'imei' => $datah['imei'],
			'posdata' => $posdata,
			'button' => $buttons,
		);

	}

	$tablehtml->paging_totrows = $db->query_foundrows();

	return $tabledatahtml;

}


//Back Button
$link_back = navpd::back();
$btn_back = btn::create('<< Back', btn::TYPE_LINK, $link_back, '', '', 'Back');


$body_html = <<<EOHTML

<div class="admintitlebar">User Accounts &gt; {$user_name_h} &gt; Tracker Unit(s)</div>

{$table_html}

<br />

{$btn_back}

EOHTML;


$template = new template();
$template->setmainnavsection('admin_user_account');
$template->settitle('User Tracker(s)');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>