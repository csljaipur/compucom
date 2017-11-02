<?php

$includes_path = '../includes/';
include $includes_path . 'config.php';

header("Content-Type: text/html; charset=ISO-8859-1");

$location = "{$cfg['site_url']}?p=pda";

header("Location: {$location}");

echo <<<EOHTML
<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$cfg['site_name']}</title>
</head>

<body>

<p>Please <a href="{$location}">click here</a> to continue.</p>

</body>
</html>
EOHTML;

?>