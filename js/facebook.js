//**************************************************************
//	Facebook
//**************************************************************

function statusChangeCallback(response) {
	console.log('statusChangeCallback');
	console.log(response);

	if (response.status === 'connected') {
	  // Logged into your app and Facebook.
	  fetchUser("id,name,first_name,last_name,email,gender,picture.width(200).height(200)", response.authResponse.accessToken);

	} else if (response.status === 'not_authorized') {
	  // The person is logged into Facebook, but not your app.

	} else {
	  // The person is not logged into Facebook, so we're not sure if they are logged into this app or not.

	}
}

function checkLoginState() {
	FB.getLoginStatus(function(response) {
	  statusChangeCallback(response);
	});
}

window.fbAsyncInit = function() {
	FB.init({
		appId      : facebook_api,
		cookie     : true,  // enable cookies to allow the server to access the session
		xfbml      : true,  // parse social plugins on this page
		version    : 'v2.5' // use graph api version 2.5
	});

	//Autologin / status
	FB.getLoginStatus(function(response) {
		statusChangeCallback(response);
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

function fetchUser(string, aToken) {

	FB.api('/me', {fields:  string }, function(response) {

	    var objUser = '{"email":"'+response.email+'","firstName":"'+response.first_name+'","lastName":"'+response.last_name+'","gender":"'+response.gender+'","imagePath":"'+response.picture.data.url+'","socialMediaId":1}';
	    
	    // test objects: 
	    // var objUser = '{"email":"saintdaniel@gmail.com","firstName":"'+response.first_name+'","lastName":"'+response.last_name+'","gender":"'+response.gender+'","imagePath":"'+response.picture.data.url+'","socialMediaId":1}';

		$.ajax({
			url: 'resources/handler/socialLogin.php',
			data: {
				socialMedia: 1,
				objUser: objUser,
				access_token: aToken
			},
			type: 'POST'
			,dataType:'json'
		}).done(function(response){
			//alert(JSON.stringify(response));

			if(response != ""){
				if(response.hasOwnProperty('afterAction')){
					var afterAction = new Function(response.afterAction);
					afterAction();
				} else {
					location.href = response.redirectPage;
				}
			}

		}).fail(function(response){
			console.log(response);
		});
	});
}

$("#fb-login").click(function(){
	FB.login(function(response){
    	// handle the response
    	checkLoginState();
	}, {
	    scope: 'email,public_profile', 
	    return_scopes: true
	});
});