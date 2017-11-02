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


//Retrieve list of all those in the main admin group
$mailadmin_ids = array();
$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('id')), $db->cond(array("usergroup = " . auth::LOGIN_GROUP_ADMIN_MAIN), 'AND'));
while ($user_record = $db->record_fetch($user_result)) {
	$mailadmin_ids[] = $user_record['id'];
}

//Delete customer
if ( (isset($_POST['actiontype'])) && ($_POST['actiontype'] == 'delete') ) {

	$actionid = intval($_POST['actionid']);

	if ($actionid == $authinfo_admin['id']) {
		throw new Exception('Can not delete own user account');
	}

	$user_result = $db->table_query($db->tbl($tbl['user']), $db->col(array('id', 'usergroup')), $db->cond(array("id = {$actionid}"), 'AND'), '', 0, 1);
	if (!($user_record = $db->record_fetch($user_result))) {
		throw new Exception("User \"{$actionid}\" does not exist");
	}

	if (in_array($user_record['usergroup'], $mailadmin_ids)) {
		throw new Exception('Can not delete user in main admin group');
	}

	$db->record_delete($tbl['user'], $db->cond(array("id = ".$actionid), 'AND'));
}

$link_addnew = navpd::forward(array('p' => 'admin_user_addedit'));
$btn_addnew = btn::create('Add User', btn::TYPE_LINK, $link_addnew, '', '', 'Add User');
$right_html = $btn_addnew;

//Table
$tablehtml = new tablehtml();
$tablehtml->shotbuttoncol = true;
$tablehtml->sortby = 'name';
//$tablehtml->table_class = 'examplelist';
$tablehtml->sortable_columns = array('name', 'username', 'registered', 'lastlogin', 'usergroup');
$tablehtml->parsegetvars($_GET);

$tablehtml->addcolumn('name', 'Name');
$tablehtml->addcolumn('username', 'Username');
$tablehtml->addcolumn('registered', 'Registered');
$tablehtml->addcolumn('lastlogin', 'Last Login');
$tablehtml->addcolumn('usergroup', 'Type');
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
	global $cfg, $tbl, $db, $authinfo_admin, $mailadmin_ids;

	$result = $db->table_query($db->tbl($tbl['user']), $db->col(array('id', 'name', 'username', 'registered', 'lastlogin', 'usergroup')), $db->cond(array(), 'AND'), $db->order($query_order), $limit_offset, $limit_count, dbmysql::TBLQUERY_FOUNDROWS);

	$tabledatahtml = array();
	while ($data = $db->record_fetch($result)) {

		$datah = lib::htmlentities_array($data);

		if ( ($data['id'] != $authinfo_admin['id']) && (!in_array($data['usergroup'], $mailadmin_ids)) ) {

			//Delete button
			$name_js = addslashes($datah['name']);
			$link_self = addslashes(navpd::self());
			$onclick_js = "performaction('{$link_self}', 'Really delete user \'{$name_js}\' and all users tracker(s), saved position data?', 'delete', {$data['id']}); return false;";
			$btn_delete = btn::create('Delete', btn::TYPE_LINK, '#', '', $onclick_js, "Delete user \"{$name_js}\"");

			//Login button
			$link_login_unit = navpd::forward(array('p' => 'track_live', 'uid_override' => $data['id']));
			$btn_login_unit = btn::create('Login', btn::TYPE_LINK, $link_login_unit, '', '', 'Login');

		} else {

			$btn_delete = btn::create_nolink('Delete', '');

			$btn_login_unit = btn::create_nolink('Login', '');

		}

		if (!in_array($data['usergroup'], $mailadmin_ids)) {

			//Edit user profile button
			$link_edit = navpd::forward(array('p' => 'admin_user_addedit', 'edit' => $data['id']));
			$btn_edit = btn::create('Profile', btn::TYPE_LINK, $link_edit, '', '', 'Edit User Profile');

		} else {
			$btn_edit = btn::create_nolink('Profile', '');
		}



		//Edit unit button
		$link_edit_unit = navpd::forward(array('p' => 'admin_user_track_config', 'user_id' => $data['id']));
		$btn_edit_unit = btn::create('Tracker(s)', btn::TYPE_LINK, $link_edit_unit, '', '', 'Edit User Tracker');


		$buttons = $tablehtml->html_table_buttons(array($btn_edit_unit, $btn_login_unit, $btn_edit, $btn_delete));

		$group = $datah['usergroup'];

		$tabledatahtml[] = array(
			'name' => htmlentities(appgeneral::trim_length($data['name'], 25)),
			'username' => htmlentities(appgeneral::trim_length($data['username'], 20)),
			'registered' => date('Y-m-d H:i:s', strtotime($datah['registered'] . ' UTC')),
			'lastlogin' => ($datah['lastlogin'] != '0000-00-00 00:00:00') ? date('Y-m-d H:i:s', strtotime($datah['lastlogin'] . ' UTC')) : 'Never',
			'usergroup' => $cfg['user_group'][$group]['name'],
			'button' => $buttons,
		);

	}

	$tablehtml->paging_totrows = $db->query_foundrows();

	return $tabledatahtml;

}

$body_html = <<<EOHTML

<div class="admintitlebar">User Accounts</div>

{$table_html}

EOHTML;


$template = new template();
$template->setmainnavsection('admin_user_account');
$template->settitle('User Accounts');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>