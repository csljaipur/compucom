<?php

class template_plain {

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

	public function setheaderaddinhtml($headeraddinhtml) {
		$this->headeraddin_html = $headeraddinhtml;
	}

	public function setpage($page) {
		$this->page = $page;
	}

	public function display() {
		global $cfg, $authinfo, $current_page, $db, $tbl, $self, $pageself;

		$title_html = htmlentities($cfg['site_name']);

		if ($this->title) {
			$title_html .= ' - ' . htmlentities($this->title);
		} else {
			$title_html .= '';
		}

		header("Content-Type: text/html; charset=ISO-8859-1");

		echo <<<EOHTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
<title>{$title_html}</title>

<link rel="stylesheet" type="text/css" href="resources/css/reset-fonts-grids.css">
<link rel="stylesheet" type="text/css" href="resources/css/plain.css">

<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">

<script src="resources/javascript/library.js" language="javascript" type="text/javascript"></script>

{$this->headeraddin_html}
</head>
<body>

<div id="page-{$this->page}">

{$this->body_html}

</div>

</div>

</body>
</html>
EOHTML;

	}

}

?>