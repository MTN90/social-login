$("#verifyButton").click(function(){
	// Saves verify code in a variable, ready to be sent to verifyMail.php (Handler)
	var verifyCode = $("#verifyCode").val();
	var token = $("#token").val();

	$.ajax({
		url: 'resources/handler/verifyMail.php',
		data: {
			event: "verifyCode",
			verifyCode: verifyCode,
			token: token
		},
		type: 'POST'
	}).done(function(response){
		console.log(response);
		if (response == 1) {
			// If the code was correct then it will be updated in the database, and the user is cleared to login.
			location.href = "home.php";
		} 
		else if(response == 0){
			Materialize.toast('Code was not recognized', 4000, 'rounded');
			$("#verifyCode").val("");
			Materialize.updateTextFields();
		}
		else {
			Materialize.toast("Sorry, An unexpected error accured", 4000, 'rounded');
		}

	}).fail(function(response){
		
	});
})

$("#verifyResend").click(function(){
	// Saves verify code in a variable, ready to be sent to verifyMail.php (Handler)
	var token = $("#token").val();

	$.ajax({
		url: 'resources/handler/verifyMail.php',
		data: {
			event: "resendEmail",
			token: token
		},
		type: 'POST'
	}).done(function(response){

		Materialize.toast("New code, has been sent to your email", 4000, 'rounded', function(){location.href = "verifyMail.php";});

	}).fail(function(response){
		
	});
})