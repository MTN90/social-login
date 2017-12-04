<?php
	require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
	require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');

	// In the Facebook and google .js pages we create a string that is made like a json object. Here we save it in a temporary session for use later. This session is unset on home.php.
	$objUser = $_POST["objUser"];	
	$_SESSION["tempUser"] = $objUser;

	$socialMedia = $_POST["socialMedia"];
	$access_token = $_POST["access_token"];

	// Initializing socialLogin class.
	$classSocialLogin = new socialLogin();

	// Calling socialLogin with access_token and media id. This class will do some checks and return a page where we redirect in our .js file.
	$status = $classSocialLogin->socialLogin($access_token, $socialMedia);

	echo $status;
?>