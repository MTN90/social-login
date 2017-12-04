<script>
	//<-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><->
	//		LOGOUT GOOGLE
	//<-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><->


	// Logging out from google.
	//
	function signOut() {
		var auth2 = gapi.auth2.getAuthInstance();
		auth2.signOut().then(function () {
		  location.href = "logout.php";
		});
	}

	// Initializing Google javascript SDK.
	//
	function onLoad() {
	  gapi.load('auth2', function() {
	    gapi.auth2.init();
	  });
	}
</script>
<script src="https://apis.google.com/js/platform.js?onload=onLoad" async defer></script>