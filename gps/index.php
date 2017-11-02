<?php

//error_reporting(0);
error_reporting(E_ALL | E_STRICT);

//Set paths
$includes_path = defined('INCLUDES_PATH') ? INCLUDES_PATH : 'includes/';
$includes_pages_path = $includes_path . 'pages/';
$publichtml_path = '';


//If a page was passed in, make it safe and use it
if (defined('PAGE')) {
	$current_page = PAGE;

} else if (isset($_GET['p'])) {
	$current_page = preg_replace("%[^a-zA-Z0-9_]%", '', $_GET['p']);
} else {
	//Set the default home page
	$current_page = 'home';
}

$self = pathinfo($_SERVER['PHP_SELF'], PATHINFO_BASENAME);
$pageself = $self."?p={$current_page}";

//Check page exists, otherwise give 404
if (!file_exists($includes_pages_path.$current_page.'.php')) {
	$current_page = 'error_404';
}

header("Content-Type: text/html; charset=ISO-8859-1");

include $includes_pages_path . $current_page . '.php';

?>