<?php

/*

//Authentication
$auth = new auth();
$auth->handle();
$authinfo = $auth->getauthinfo();
$auth->login_required();

*/

//Authentication
class auth {

	const LOGIN_TYPE_FORM = 1;
	const LOGIN_TYPE_COOKIE = 2;
	const LOGIN_TYPE_DEMO = 3;

	const LOGIN_ERROR_USERPASS = 1;

	const LOGIN_GROUP_ADMIN_MAIN = 1;
	const LOGIN_GROUP_ADMIN = 2;
	const LOGIN_GROUP_USER_DEMO = 3;
	const LOGIN_GROUP_USER = 4;

	private $username = '';
	private $password = '';
	private $user_id_override = false;
	private $authinfo = array();
	private $authinfo_admin = array();
	private $autologin = false;
	private $login_attempted = false;
	private $login_success = false;
	private $login_type = 0;
	private $login_error = 0;

	public function handle() {
		global $db, $tbl, $cfg;

		//If logout, delete cookie
		if ( (isset($_GET['logout'])) && ($_GET['logout']) ) {

			$this->cookie_clear();

		} else {

			//If demo login specified
			if ( (isset($_GET['demo'])) && ($_GET['demo'] == 1) ) {

				$this->username = $cfg['demo_username'];
				$this->password = md5('demo');
				$this->autologin = false;
				$this->user_id_override = false;

				$this->login_attempted = true;
				$this->login_type = self::LOGIN_TYPE_DEMO;

			} else if ( (isset($_POST['login_username'])) && (isset($_POST['login_password'])) ) {
				//If username / password posted

				$this->username = $_POST['login_username'];
				$this->password = md5($_POST['login_password']);
				$this->autologin = (isset($_POST['login_auto']) && ($_POST['login_auto'])) ? true : false;
				$this->user_id_override = false;

				$this->login_attempted = true;
				$this->login_type = self::LOGIN_TYPE_FORM;

			} else if (isset($_COOKIE['auth'])) {
				//If cookie is already set

				//Parse out username / password
				parse_str($_COOKIE['auth'], $cookiedata);

				$this->username = $cookiedata['username'];
				$this->password = $cookiedata['password'];
				$this->autologin = ($cookiedata['autologin']) ? true : false;

				$this->login_attempted = true;
				$this->login_type = self::LOGIN_TYPE_COOKIE;

				//If new user id override specified, then use it
				if (isset($_GET['uid_override'])) {

					//If has data
					if ($_GET['uid_override']) {
						$this->user_id_override = $_GET['uid_override'];
					} else {
						$this->user_id_override = false;
					}

				} else {

					//Otherwise see if there is one in the cookie already
					$this->user_id_override = (isset($cookiedata['uid_override'])) ? intval($cookiedata['uid_override']) : false;

				}

			}

		}

		//If login attempted
		if ($this->login_attempted == true) {

			$cond = array("username = '".$db->es($this->username)."'", "nonactive = 0");

			//if ($this->password != $cfg['admin_password']) {
			//}

			$cond = array_merge($cond, array("password = '".$db->es($this->password)."'"));

			//Check username / password
			$user_result = $db->table_query($db->tbl($tbl['user']), '*', $db->cond($cond, 'AND'), '', 0, 1);
			if ($user_record = $db->record_fetch($user_result)) {

				$curr_user_record = $user_record;

				$curr_authinfo = $curr_user_record;

				$this->login_success = true;

				//If the logged in user is an admin
				if ( ($user_record['usergroup'] == self::LOGIN_GROUP_ADMIN_MAIN) || ($user_record['usergroup'] == self::LOGIN_GROUP_ADMIN) ) {

					//Set admin authiinfo
					$this->authinfo_admin = $curr_user_record;

					//Check if there is a uid override set
					if ($this->user_id_override) {

						//If there is a uid override set

						//Lookup details for the user

						//Check username / password
						$user_result = $db->table_query($db->tbl($tbl['user']), '*', $db->cond(array("id = {$this->user_id_override}"), 'AND'), '', 0, 1);
						if ($user_record = $db->record_fetch($user_result)) {

							//Check user id is not a main admin
							if ($user_record['usergroup'] == self::LOGIN_GROUP_ADMIN_MAIN) {
								throw new Exception('Can not user override on a main admin');
							}

							//Check user id is not this user (no point in overriding to self)
							if ($user_record['id'] == $curr_user_record['id']) {
								throw new Exception('Can not user override to be yourself');
							}

							//Replace current user with the user we want to be (specified in the override)
							$curr_authinfo = $user_record;

						} else {

							//Specified user id to override with not found
							$this->user_id_override = false;

						}

					}

				} else {

					//Clear uid userride if set (should not be set, but if it is for any reason)
					$this->user_id_override = false;

				}

				$this->authinfo = $curr_authinfo;

				//Save cookie
				$this->cookie_save();

				//Update last login flag
				$lastlogin_user_id = (isset($this->authinfo_admin['id'])) ? $this->authinfo_admin['id'] : $this->authinfo['id'];
				$db->record_update($tbl['user'], $db->rec(array('lastlogin' => $db->datetimenow())), $db->cond(array("id = {$lastlogin_user_id}"), 'AND'));

				//Set timezone
				$timezone = (isset($this->authinfo_admin['timezone'])) ? $this->authinfo_admin['timezone'] : $curr_authinfo['timezone'];
				date_default_timezone_set($timezone);

			} else {

				//Otherwise if login failed

				$this->login_error = self::LOGIN_ERROR_USERPASS;

				//If cookie is set
				if ($this->login_type == self::LOGIN_TYPE_COOKIE) {

					//Clear cookie
					$this->cookie_clear();
				}

			}

		}

	}

	public function setuser($username, $password) {
		$this->username = $username;
		$this->password = $password;
		$this->autologin = false;
	}

	private function cookie_clear() {
		global $cfg;

		//Delete cookie
		setcookie('auth', '', time() - $cfg['auth_cookie_expiry']);
	}

	public function cookie_save() {
		global $cfg;

		$cookiedata = array(
			'username' => $this->username,
			'password' => $this->password,
			'autologin' => ($this->autologin) ? 1 : 0,
		);

		if ($this->user_id_override) {
			$cookiedata = array_merge($cookiedata, array('uid_override' => $this->user_id_override));
		}

		if ($this->autologin) {
			$expiry = time() + $cfg['auth_cookie_expiry'];
		} else {
			$expiry = null;
		}

		setcookie('auth', http_build_query($cookiedata), $expiry);

	}

	public function login_required() {

		if ($this->login_success == true) {
			//Allow continute processing....
		} else {
			$this->display_loginform();
		}

	}

	public function login_required_admin() {

		if (isset($this->authinfo_admin['id'])) {
			//Allow continute processing....
		} else {
			$this->display_loginform();
		}

	}

	public function display_loginform() {
		global $cfg, $current_page;

		$username_h = $this->username;

		//If autologin
		if ($this->autologin) {
			$autologin_checked = 'checked="checked"';
		} else {
			$autologin_checked = '';
		}

		$errormsg_html = '';
		if ($this->login_error == self::LOGIN_ERROR_USERPASS) {

			$errormsg_html = <<<EOHTML
<div class="errormsg">Error: Username / Password not recognised</div>
EOHTML;

		}

		$link_h = navpd::self_h(array('logout' => null));

		$body_html = <<<EOHTML

<div class="loginbox">

{$errormsg_html}

	<div class="loginboxcontent">

		<div class="loginboxtitle">Login</div>

		<div class="loginboxcontentinner">

			<form method="post" action="{$link_h}">

				<div>
					<label for="login_username" class="inputxttitle">Email</label>
					<input type="text" name="login_username" id="login_username" value="{$username_h}" maxlength="255" class="inputtxt" />
				</div>

				<div>
					<label for="login_password" class="inputxttitle">Password</label>
					<input type="password" name="login_password" id="login_password" value="" maxlength="255" class="inputtxt" />
				</div>

				<div class="autologin">
					<label for="login_auto">Autologin:</label> <input type="checkbox" name="login_auto" id="login_auto" {$autologin_checked} value="1" />
				</div>

				<div><input type="submit" value="Login" name="login" /></div>

			</form>

		</div>

	</div>

</div>

EOHTML;

		//If PDA version, use PDA template display
		if (in_array($current_page, $cfg['pda_pages'])) {

			$template = new template_pda();
			$template->settitle('Login');
			$template->setbodyhtml($body_html);
			$template->display();

		} else {

			$template = new template();
			$template->setpage('login');
			$template->settitle('Login');
			//$template->setheaderaddinhtml($headeraddin_html);
			$template->setbodyhtml($body_html);
			$template->display();

		}

		exit;

	}

	public function getauthinfo() {
		return $this->authinfo;
	}

	public function getauthinfo_admin() {
		return $this->authinfo_admin;
	}

}

?>