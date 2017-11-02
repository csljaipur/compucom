<?php

class template_pda {

	private $body_html = '';
	private $title = '';
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

	public function display() {
		global $cfg, $authinfo, $current_page, $db, $tbl, $self, $pageself;

		$title_html = htmlentities($cfg['site_name']) . ' (PDA)';

		if ($this->title) {
			$title_html .= ' - ' . htmlentities($this->title);
		} else {
			$title_html .= '';
		}

		$link_logout = htmlentities($self . '?p=pda&logout=1');

		if (isset($authinfo['id'])) {

			$footer_html = <<<EOHTML
<div class="footer">
	<p>[<a href="{$link_logout}">Logout</a>]</p>
</div>
EOHTML;

		} else {
			$footer_html = '';
		}

		$page_title_h = htmlentities($cfg['site_name']);


		header("Content-Type: text/html; charset=ISO-8859-1");

		$q = '?';
		echo <<<EOHTML
<?xml version="1.0"{$q}>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$title_html}</title>

<style type="text/css">
  <!--

	/* General */

	body {
		font-size: 10pt;
		font-family: sans-serif;
		background-color: #ffffff;
	}

	/* Links */
	a:link, a:visited, a:active, a:hover {
		color: #000000;
	}

	a:link, a:visited {
		text-decoration: none;
	}

	a:active, a:hover {
		text-decoration: underline;
	}

	/* Login */
	.loginbox .inputtxt, .loginbox .autologin {
		display: block;
		margin-bottom: 5px;
	}

	.loginbox .loginboxtitle {
		font-weight: bold;
	}

	.crediterror {
		color: #ff0000;
		font-weight: bold;
	}

  -->
</style>

</head>

<body>

<h1>{$page_title_h}</h1>

{$this->body_html}

{$footer_html}

</body>
</html>
EOHTML;

	}

}

?>