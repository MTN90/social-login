<script type="text/javascript">
	var facebook_api = "<?php echo FACEBOOK_API; ?>";
</script>

<script>
	//<-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><->
	//		LOGOUT FACEBOOK
	//<-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><-><->
	window.fbAsyncInit = function() {
		FB.init({
			appId      : facebook_api,
			cookie     : true,  // enable cookies to allow the server to access the session
			xfbml      : true,  // parse social plugins on this page
			version    : 'v2.5' // use graph api version 2.5
		});
	};
		// Load the SDK asynchronously
		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/sdk.js";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));

	// Logout from facebook.
	function logoutFacebook(){
		FB.getLoginStatus(function(response) {
	        if (response && response.status === 'connected') {
	            FB.logout(function(response) {
	        		location.href = "logout.php";
	            });
	        }
	        else {
	        }
	    });
	}
</script>