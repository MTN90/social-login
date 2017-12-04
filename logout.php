<?php

	// Removing all sessions and setting autologin sessions time to -1 to make it expire.
	session_start();

	session_unset();
	session_destroy();
	setcookie("autoLogin", null, -1, "/");

	// Redirect to index.php
	header("Location: index.php");

?>