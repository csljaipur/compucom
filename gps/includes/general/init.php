<?php

//Autoload classes
function __autoload($class_name) {
	global $includes_path;

	require_once $includes_path . 'classes/' . $class_name . '.php';
}

?>