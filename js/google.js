//**************************************************************
//	Google
//**************************************************************

	// Login Google
	// This function is called when clicking the google login button, or if there is a cookie stored with former google login.
	//
	function onSignIn(googleUser) {
        var profile = googleUser.getBasicProfile();
		var id_token = googleUser.getAuthResponse().id_token;

		// Creating object to save in session. 
		var objUser = '{"email":"'+profile.getEmail()+'","firstName":"'+profile.getGivenName()+'","lastName":"'+profile.getFamilyName()+'","gender":"unknown","imagePath":"'+profile.getImageUrl()+'?sz=200","socialMediaId":2}';

		$.ajax({
			url: 'resources/handler/socialLogin.php',
			data: {
				socialMedia: 2,
				objUser: objUser,
				access_token: id_token
			},
			type: 'POST'
			,dataType:'json'
		}).done(function(response){

			// Depending on profile status, this will send user to either create.php (New user), merge.php (Mail exists, new social login) or home.php (Account exists).
			if(response != ""){
				if(response.hasOwnProperty('afterAction')){
					var afterAction = new Function(response.afterAction);
					afterAction();
				} else {
					location.href = response.redirectPage;
				}
			}

		}).fail(function(response){
			// console.log(response);
		});
	};