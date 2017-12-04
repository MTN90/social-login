<?php 
	DEFINE("TEST_MODE", 1);

	// DB
	if (TEST_MODE == 1) {
		DEFINE("DB_SERVER", "localhost");
		DEFINE("DB_USER", "root");
		DEFINE("DB_PASSWORD", "");
		DEFINE("DB_DATABASE", "social_login");
		DEFINE("DB_PORT", "3307");
		DEFINE("DB_CHARSET", "utf8");
	} 
	else {
		DEFINE("DB_SERVER", "");
		DEFINE("DB_USER", "");
		DEFINE("DB_PASSWORD", "");
		DEFINE("DB_DATABASE", "");
		DEFINE("DB_PORT", "");
		DEFINE("DB_CHARSET", "utf8");
	}

	// Facebook
	---

	// Google
	---

	// Linkedin
	---

?>