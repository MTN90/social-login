

$("#emailChangeButton").click(function(){

	var hash = $("#emailChangeHash").val();
	var token = $("#token").val();

	$.ajax({
		url: 'resources/handler/mailChange.php',
		data: {
			event: "change",
			hash: hash,
			token: token
		},
		type: 'POST'
	}).done(function(response){

		if (response == 1) {
			// If the hash was correct then the users email will be updated in the database.
			Materialize.toast('Email was succesfully changed!', 4000, 'rounded', function(){location.href = "home.php";});
		} 
		else if(response == 0){
			Materialize.toast('Email Link was not recognized, no changes was made', 4000, 'rounded', function(){location.href = "home.php";});
		}
		else {
			Materialize.toast("Sorry, An unexpected error accured", 4000, 'rounded');
		}

	}).fail(function(response){
		
	});

});


$("#emailChangeCancel").click(function(){

	var hash = $("#emailChangeHash").val();
	var token = $("#token").val();

	$.ajax({
		url: 'resources/handler/mailChange.php',
		data: {
			event: "cancel",
			hash: hash,
			token: token
		},
		type: 'POST'
	}).done(function(response){

		if (response == 1) {
			// If the hash was correct then the users email will be updated in the database.
			Materialize.toast('Email change was canceled!', 4000, 'rounded', function(){location.href = "home.php";});
		} 
		else if(response == 0){
			Materialize.toast('Email Link was not recognized, no changes was made', 4000);
		}
		else{
			Materialize.toast("Sorry, An unexpected error accured", 4000, 'rounded');
		}

	}).fail(function(response){
		
	});

});