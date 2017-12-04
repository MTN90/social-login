<?php
	// error_reporting(E_ALL);
	// ini_set("display_errors","On");

	$root = $_SERVER['DOCUMENT_ROOT'].'/';

	require($root.'resources/class/classDb.php');
	require($root.'resources/class/classSocialLogin.php');
	require($root.'resources/class/classValidation.php');
	require($root.'resources/class/classUser.php');
	require($root.'resources/class/classMail.php');
	session_start();
?>