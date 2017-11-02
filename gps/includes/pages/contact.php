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

$email_parts = explode('@', $cfg['email_contact']);



$fields = array('name', 'email', 'comments');

$formdata = array();
foreach ($fields as $field) {
	$formdata[$field] = (isset($_POST[$field])) ? $_POST[$field] : '';
}

$errormsg = '';

$sentsuccess = false;

if (isset($_POST['formposted'])) {

	if ( (isset($_POST['name'])) && ($_POST['name']) && (isset($_POST['comments'])) && ($_POST['comments']) ) {

		if ( (!$_POST['email']) || ( ($_POST['email']) && (lib::chkemailvalid($_POST['email'])) ) ) {

			$email = ($_POST['email']) ? $_POST['email'] : $cfg['email_system_from'];

			$headers = 'From: ' . $email;
			$message = wordwrap($_POST['comments'], 70);

			$message = "Sender Name: {$_POST['name']}\nSender Email: {$_POST['email']}\n\n" . $message . "\n\n------------------------------\nSender IP: " . $_SERVER['REMOTE_ADDR'];

			$status = mail($cfg['email_contact'], $cfg['site_name'] . ' Comments Form', $message, $headers);

			if ($status) {

				$sentsuccess = true;

			} else {
				$errormsg = "Error: Internal website configuration error.";
			}

		} else {
			$errormsg = "Error: 'Email' not valid.";
		}

	} else {
		$errormsg = "Error: 'Name' and 'Comments' are required.";
	}

}

if ($sentsuccess) {

	$form_html = <<<EOHTML
<p>Thank you for your comments.</p>
EOHTML;

} else {

	if ($errormsg) {

		$errormsg_html = <<<EOHTML
<div class="errormsg">{$errormsg}</div>
EOHTML;

	} else {
		$errormsg_html = '';
	}

	$formdata_h = lib::htmlentities_array($formdata);

	$link_self = navpd::self_h();

	$form_html = <<<EOHTML

{$errormsg_html}

<p>To contact ..., please use the contact form below or email <a id="email" href="#">[JavaScript required for this link due to spam protection]</a>.

<form method="post" action="{$link_self}">
	<input type="hidden" name="formposted" value="1" />

	<p>
		<label for="name" class="inputxttitle">Name *</label>
		<input type="text" name="name" id="name" value="{$formdata_h['name']}" class="inputxt" />
	</p>

	<p>
		<label for="email" class="inputxttitle">Email</label>
		<input type="text" name="email" id="email" value="{$formdata_h['email']}" class="inputxt" />
	</p>

	<p>
		<label for="comments" class="inputxttitle">Comments *</label>
		<textarea rows="13" name="comments" id="comments" cols="51" class="inputxt">{$formdata_h['comments']}</textarea>
	</p>

	<p><input type="submit" value="Send Comments" name="send" /></p>

</form>

<div class="contactfooter">
	<p>Parallel Track is based in South East London, United Kingdom.</p>
</div>


<script type="text/javascript">
  <!--

	var partb = "{$email_parts[1]}";
	var parta = "{$email_parts[0]}";

	document.getElementById("email").innerHTML = parta + "@" + partb;
	document.getElementById("email").href = "mailto:" + parta + "@" + partb;

  //-->
</script>

EOHTML;

}


$body_html = <<<EOHTML

{$form_html}

EOHTML;


$template = new template();
$template->settitle('Contact');
//$template->setheaderaddinhtml($headeraddin_html);
$template->setbodyhtml($body_html);
$template->display();

?>