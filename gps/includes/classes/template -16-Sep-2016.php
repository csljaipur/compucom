<?php

class template {

	private $body_html = '';
	private $title = '';
	private $title_full = '';
	private $headeraddin_html = '';
	private $mainnavsection = '';
	private $subnavsection = '';
	private $page = '';

	public function __construct() {
		global $current_page;

		$this->page = $current_page;
	}

	public function setbodyhtml($body_html) {
		$this->body_html = $body_html;
	}

	public function settitle($title) {
		$this->title = $title;
	}

	public function settitlefull($title_full) {
		$this->title_full = $title_full;
	}

	public function setheaderaddinhtml($headeraddinhtml) {
		$this->headeraddin_html = $headeraddinhtml;
	}

	public function setpage($page) {
		$this->page = $page;
	}

	public function setmainnavsection($mainnavsection) {
		$this->mainnavsection = $mainnavsection;
	}

	public function setsubnavsection($subnavsection) {
		$this->subnavsection = $subnavsection;
	}

	public function display() {
		global $cfg, $authinfo, $authinfo_admin, $current_page, $db, $tbl, $self, $pageself;

		if ($this->title_full) {
			$title_html = htmlentities($this->title_full);
		} else {

			$title_html = htmlentities($cfg['site_name']);

			if ($this->title) {
				$title_html .= ' - ' . htmlentities($this->title);
			} else {
				$title_html .= '';
			}

		}

		$self_h = htmlentities($cfg['site_url']);


		$navigation = array();

		$navigation_part = array(
			'home' => array(
				'name' => 'Home',
				'subsection' => array(),
			),
		);
		$navigation = array_merge($navigation, $navigation_part);

		if (isset($authinfo['id'])) {

			$navigation_part = array(
				'track_live' => array(
					'name' => 'Track',
					'subsection' => array(
						'track_live' => 'Live',
						'track_history' => 'History',
					),
				),

				'user_profile' => array(
					'name' => 'User Profile',
					'subsection' => array(
						//'user_profile' => 'User Profile',
						'user_profile_edit' => 'Edit',
						'track_config' => 'Tracker Config',
						
					),
				),

			);
			$navigation = array_merge($navigation, $navigation_part);

		}

		//If admin, and uid override not in effect show admin menu
		if ( (isset($authinfo_admin['id'])) && ($authinfo['id'] == $authinfo_admin['id']) ) {

			$navigation_part = array(
				'admin_user_account' => array(
					'name' => 'Admin',
					'subsection' => array(
						/*
						'admin_user_account' => 'Users',
						//'admin_backup' => 'Backup',
						*/
					),
				),
			);
			$navigation = array_merge($navigation, $navigation_part);
		}

		//If admin, and uid override not in effect show admin menu
		if ( (isset($authinfo_admin['id'])) && ($authinfo['id'] == $authinfo_admin['id']) ) {

			$navigation_part = array(
				'statusactive' => array(
					'name' => 'Dashboard',
					'subsection' => array(
						'statusactive' => 'Last Active',
						'others' => 'Others',
					),
				),
			);
			$navigation = array_merge($navigation, $navigation_part);			
		}

		if (isset($authinfo['id'])) {

			$navigation_part = array(
				'logout' => array(
					'name' => 'Logout',
					'subsection' => array(),
				),
			);
			$navigation = array_merge($navigation, $navigation_part);

		}


		$nav_main_html = '';
		$nav_sub_html = '';

		foreach ($navigation as $navitem_id => $navitem) {

			if ( ($navitem_id == $current_page) || ($navitem_id == $this->mainnavsection) ) {
				$class = 'selected';
			} else {
				$class = 'nonselected';
			}

			if ($navitem_id == 'logout') {
				$link = htmlentities($self . '?p=home&logout=1');
			} else {
				$link = htmlentities($self . '?p=' . $navitem_id);
			}

			$nav_main_html .= <<<EOHTML
<a href="{$link}" class="{$class}">{$navitem['name']}</a>
EOHTML;

			if ( ($navitem_id == $current_page) || ($navitem_id == $this->mainnavsection) ) {

				foreach ($navitem['subsection'] as $navsubitem_id => $navsubname) {

					if ( ($navsubitem_id == $current_page) || ($navsubitem_id == $this->subnavsection) ) {
						$class = 'selected';
					} else {
						$class = 'nonselected';
					}

					/*
					if ($navsubitem_id == 'logout') {
						$link = htmlentities($self . '?p=home&logout=1');
					} else {
						$link = htmlentities($self . '?p=' . $navsubitem_id);
					}
					*/

					$link = htmlentities($self . '?p=' . $navsubitem_id);

					$nav_sub_html .= <<<EOHTML
<a href="{$link}" class="{$class}">{$navsubname}</a>
EOHTML;

				}

			}

		}

		if ($nav_sub_html) {

			$nav_sub_html = <<<EOHTML
		<div class="nav nav-secondary">
{$nav_sub_html}
		</div>
EOHTML;

		}

		//If there is a user override current in effect
		if ( (isset($authinfo['id'])) && (isset($authinfo_admin['id'])) && ($authinfo['id'] != $authinfo_admin['id']) ) {

			$username_h = htmlentities($authinfo['username']);

			$link_h = $self . '?p=admin_user_account&uid_override=';

			$user_override_html = <<<EOHTML
<div class="user_override">
{$username_h} [<a href="{$link_h}">Cancel</a>]
</div>
EOHTML;
		} else {
			$user_override_html = '';
		}

		header("Content-Type: text/html; charset=ISO-8859-1");

		echo <<<EOHTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<title>{$title_html}</title>

<link rel="stylesheet" type="text/css" href="resources/css/reset-fonts-grids.css">
<link rel="stylesheet" type="text/css" href="resources/css/general.css">
<link rel="stylesheet" type="text/css" href="http://productlog.ns-tech.co.uk/parallel-track/css/" />

<!--<link rel="shortcut icon" href="{$cfg['site_url']}resources/images/favicon/favicon.ico" type="image/vnd.microsoft.icon">-->

<script src="resources/javascript/library.js" language="javascript" type="text/javascript"></script>

{$this->headeraddin_html}
</head>
<body>

<div id="doc2">

	<div id="hd">

		<div id="logo" style="background-image: url(resources/images/template/header.jpg)"><a id="header_link" href="{$self_h}"></a></div>
{$user_override_html}
		<div class="nav nav-main">
{$nav_main_html}
		</div>
{$nav_sub_html}
	</div>
	<div id="bd">

		<div id="page-{$this->page}">

{$this->body_html}

		</div>

	</div>

</div>

</body>
</html>
EOHTML;

	}

}

?>