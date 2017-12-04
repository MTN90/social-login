<?php 
	// Generate and store token
	$token = hash('sha256', uniqid(mt_rand(), false)); 
	$_SESSION["token"] = $token;
?>