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

$errormsg_html = '';

//Delete entry
if ( (isset($_POST['action'])) && ($_POST['action'] == 'delete') ) {

	$deleteid = intval($_POST['actionid']);

	//Auth check
	$unit_result = $db->table_query($db->tbl($tbl['unit']), '1', $db->cond(array("user_id = {$authinfo['id']}", "id = {$deleteid}"), 'AND'), '', 0, 1);
	if ($db->record_count($unit_result) == 0) {
		throw new Exception("Edit unit id \"{$editid}\" not found");
	}


	if ($authinfo['usergroup'] != auth::LOGIN_GROUP_USER_DEMO) {

		//Delete unit history
		//$db->record_delete($tbl['position'], $db->cond(array("unit_id = {$deleteid}"), 'AND'));

		//Delete unit
		$db->record_delete($tbl['unit'], $db->cond(array("id = {$deleteid}"), 'AND'));

	} else {

		$errormsg_html .= <<<EOHTML
<div class="errormsg">Error: Functionality not available in demo mode.</div>
EOHTML;

	}

}


//List out all units
$unitlist_html = '';
$unit_result = $db->table_query($db->tbl($tbl['unit']), $db->col(array('id', 'name', 'icon')), $db->cond(array("user_id = {$authinfo['id']}"), 'AND'), $db->order(array(array('name', 'ASC'))));
while ($unit_record = $db->record_fetch($unit_result)) {

	$unit_record_h = lib::htmlentities_array($unit_record);

	$link_edit = navpd::forward(array('p' => 'track_config_addedit', 'id' => $unit_record['id']));

	$name = addslashes($unit_record_h['name']);

	$iconpath = $cfg['icon'][$unit_record['icon']]['img']['config'];

	$unitlist_html .= <<<EOHTML
	<tr>
		<td class="icon"><img src="{$iconpath}" width="48" height="48" /></td>
		<td>{$unit_record_h['name']}</td>
		<td class="edit">[<a href="{$link_edit}">Edit</a>] [<a href="#" onclick="performaction('Delete unit \'{$name}\'?\\nAll history will be lost', 'delete', {$unit_record['id']}); return false;">Delete</a>]</td>
	</tr>
EOHTML;

}


$link_self = navpd::self_h();

if ($unitlist_html) {

	$unitlist_html = <<<EOHTML

<form method="post" action="{$link_self}" name="frm_action" id="frm_action">
	<input type="hidden" name="action" id="action" value="" />
	<input type="hidden" name="actionid" id="actionid" value="" />
</form>

<script type="text/javascript">
  <!--

	//Perform action
	function performaction(confirmtext, action, actionid) {

		if ( ( (confirmtext) && (confirm(confirmtext)) ) || (!confirmtext) ) {

			$('action').value = action;
			$('actionid').value = actionid;
			$('frm_action').submit();

		}

	}

  //-->
</script>

{$errormsg_html}

<table cellspacing="0" class="tblunit">
{$unitlist_html}
</table>
EOHTML;

} else {
	$unitlist_html = <<<EOHTML
<div class="infomsg">No existing tracker units setup, use link above to add new units.</div>
EOHTML;
}

$link_addnew = navpd::forward(array('p' => 'track_config_addedit'));

$body_html = <<<EOHTML

<div class="pagenav">
	<a class="addnew" href="{$link_addnew}">Add New</a>
</div>

{$unitlist_html}

EOHTML;




$template = new template();
$template->setmainnavsection('user_profile');
$template->settitle('Tracker Configuration');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>