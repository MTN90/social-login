$(document).on("keyup", function(){
	var key = event.which;
	if (key == 13) {
		merge();
	}
});

$("#mergeButton").click(function(){
	merge();
})

function merge(){
	// Gets email and password from form on merge.php (Main folder)
	var mergeEmail = $("#mergeEmail").val();
	var mergePassword = $("#mergePassword").val();
	var token = $("#token").val();

	// Checking if password has been typed in.
	if (mergePassword == "") {
		Materialize.toast('Please enter your password to verify yourself', 4000, 'rounded');
		$("#mergePassword").toggleClass("invalid valid");
	}
	else {

		$.ajax({
			url: 'resources/handler/merge.php',
			data: {
				mergeEmail: mergeEmail,
				mergePassword: mergePassword,
				token: token
			},
			type: 'POST'
			//,dataType:'json'
		}).done(function(response){
			
			if (response == 0){
				Materialize.toast("Incorrect password", 4000, 'rounded');
				$("#mergePassword").toggleClass("invalid valid");
				$("#mergePassword").val("");
				Materialize.updateTextFields();
			} 
			else if(response == 1){
				Materialize.toast('Accounts merged successfully', 2000, 'rounded', function(){location.href = "home.php";}); 	
				//location.href = "home.php";
			} 
			else if(response == 2){
				Materialize.toast("Cooldown, to many login attempts", 4000, 'rounded');
			}
			else {
				Materialize.toast("Sorry, unexpected error accured", 4000, 'rounded');
			}

		}).fail(function(response){
			// console.log(response);
		});		
	}
}