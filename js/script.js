//**************************************************************
// General
//**************************************************************

var extension = ".php";
var pageName = getPageName(window.location.href) + extension;

//**************************************************************
//	Materialize Initializations
//**************************************************************

$(document).ready(function () {

	// Modals
	$('.modal').modal({
		dismissible: true, // Modal can be dismissed by clicking outside of the modal
		opacity: .5, // Opacity of modal background
		in_duration: 300, // Transition in duration
		out_duration: 200, // Transition out duration
		starting_top: '4%', // Starting top style attribute
		ending_top: '10%', // Ending top style attribute
	});

	// Select Boxes
	$('select').material_select();

	// Datepicker
	var date = new Date();
	var endOfYear = '31/12/' + date.getFullYear();

	$('.datepicker').pickadate({
		selectMonths: true, // Creates a dropdown to control month
		selectYears: 75, // Creates a dropdown of 15 years to control year
		format: 'dd/mm/yyyy',
		min: [1940,1,1],
  		max: endOfYear,
  		//closeOnSelect: true, // Not working in materializecss, workaround added using 'onSet'.
    	closeOnClear: true,    	
    	today: '',
    	clear: '',
    	close: 'Cancel',
    	onSet: function (arg){
    		if ('select' in arg) {
    			this.close();
    		}
    	}
	});

	// Tooltips
	$('.tooltipped').tooltip({delay: 50});
	
	// Autocomplete
	$('#autocomplete-country').autocomplete({
		data: {
		  "Denmark": null,
		  "Faroe Islands": null,
		  "Greenland": null
		}
	});

	// sidenav
	$('.button-collapse').sideNav({
      menuWidth: 300, // Default is 240
      edge: 'left', // Choose the horizontal origin
      closeOnClick: false, // Closes side-nav on <a> clicks, useful for Angular/Meteor
      draggable: true // Choose whether you can drag to open on touch screens
    }
  );

	//Autoupdate textfields
	Materialize.updateTextFields();
});

//**************************************************************
//	Validate City/Zip field on change
//**************************************************************

// When user inputs data in either zip field, or city field. This method will get the equiliant data from our database and input to the other field.
$("#uAddressZip, #uAddressCity").on("change",function(){

	var zipCity = $(this).val();
	var event = $(this).attr('id');
	var target = (event == "uAddressZip")  ? "uAddressCity": "uAddressZip";
	var token = $("#token").val();

	$.ajax({
		url: 'resources/handler/'+pageName,
		data: {
			event: event,
			zipCity: zipCity,
			token: token
		},
		type: 'POST'
		,dataType:'json'
	}).done(function(response){

		if(response.status == 1){
			$('#'+target).val(response.name);
			$('#uAddressZipCityId').val(response.id);
			$("#uAddressZip, #uAddressCity").addClass("valid");
			$(function() {
			    Materialize.updateTextFields();
			});
		} else {
			//$('#'+event).css("border","2px solid tomato");
			$('#'+target).addClass("invalid");
			$('#'+target).val('');
		}

	}).fail(function(response){
		console.log(response);
	});
});

//**************************************************************
//	Validate Email field on change
//**************************************************************

// Here we check if the email is valid, or if it exist in the database allready. If it exist and the user is on the create page, he can accept to be sent to the merge page.
$("#signupEmail, #editEmail").on("change",function(){

	var email = $(this).val();
	var token = $("#token").val();

	if(email != ""){

		$.ajax({
			url: 'resources/handler/'+pageName,
			data: {
				event: "checkEmail",
				email: email,
				token: token
			},
			type: 'POST'
			//,dataType:'json'
		}).done(function(response){
			
			if(response == 1){
				$("#signupEmail, #editEmail").toggleClass("invalid valid");
	   			$('#modalMerge').modal('open');
	   			$("#modalMergeButton").attr("href","merge.php?email="+email);
			} 
			else if(response == 2){
				Materialize.toast('Indtast en gyldig email!', 4000, 'rounded');
			} 
			else if(response == 3){
				$("#signupEmail, #editEmail").toggleClass("invalid valid");
				Materialize.toast('This email is already in use!', 4000, 'rounded');
			}

		}).fail(function(response){
			console.log(response);
		});
	}
});

//**************************************************************
//	Signup Button Click
//**************************************************************

// When creating a new user this will be called, it will return with validation if a field is filled out incorrect, or else it will redirect to home.php or verifymail.php depending on your account verification.
$("#signupButton").click(function(){
	regSignup();
});

$(document).on("keyup", function(){
	var key = event.which;
	if (key == 13) {

		if(pageName == "index.php"){
			regLogin();
		}
		else if(pageName == "create.php"){
			regSignup();
		}
		else if(pageName == "edit.php"){
			regEdit();
		}
	}
});

//**************************************************************
//	Login Button Click
//**************************************************************

// This here is used when trying to login with our own login system (Not social), here it will validate fields and if they are fine it will run the login check. Again, depending on account status it will either login directly or send user to verifymail.php.
$("#loginButton").click(function(){
	regLogin();
});

//**************************************************************
//	Edit Button Click
//**************************************************************

// Edit is doing a validation check, then if everything is good, then it will run the edit method in classUser for the update.
$("#editButton").click(function(){
	regEdit();
});

$("#modalUpdateEmailButton").click(function(){
	updateEmail();
})


//**************************************************************
//	Functions
//**************************************************************

// Login function
function regLogin(){
	var email = $("#loginEmail").val();
	var password = $("#loginPassword").val();
	var token = $("#token").val();

	if ($("#autologin").is(":checked")) {
		var autologin = 1;
	} else {
		var autologin = 0;
	}

	$.ajax({
		url: 'resources/handler/login.php',
		data: {
			event: 'logIn',
			token: token,
			email: email,
			password: password,
			autoLogin: autologin
		},
		type: 'POST'
		,dataType:'json'
	}).done(function(response){

		if(response.constructor === Array){

			if(response[0] == "Error"){
				Materialize.toast(response[1], 4000, 'rounded');
			} 
			else {
				for(var i = 0; i < response.length; i++){
					$("#"+response[i]).toggleClass("invalid valid");
					var lbl = $("#"+response[i]).siblings("label").text();
					Materialize.toast(lbl + ' has been typed wrong', 4000, 'rounded');
				}
			}
		} else {
			if(response.status === 1) {
				location.href = "home.php";
			} 
			else if(response.status === 3) {
				location.href = "verifyMail.php";				
			} 
			else {
				if(response.message == "Wrong Email or Password"){
					Materialize.toast(response.message, 4000, 'rounded');
					$("#loginEmail, #loginPassword").toggleClass("invalid valid");
				} 
				else {
					Materialize.toast(response.message, 4000, 'rounded');
				}
			}
		}
	}).fail(function(response){
		console.log(response);
	});
}

// Signup function
function regSignup(){
	if($("#signupAgree").prop('checked') == true){

		$.ajax({
			url: 'resources/handler/create.php',
			data: $("#signupBox").serialize(),
			type: 'POST'
			,dataType:'json'

		}).done(function(response){

			var email = $("#signupEmail").val();

			if(response.constructor === Array){

				if(response[0] == "Error"){
					Materialize.toast(response[1], 4000, 'rounded');
				} 
				else {
					for(var i = 0; i < response.length; i++){
						$("input[name='"+response[i]+"']").toggleClass("invalid valid");
						var lbl = $("input[name='"+response[i]+"']").siblings("label").text();
						Materialize.toast(lbl + ' has been typed wrong', 4000, 'rounded');	
					}
				}
			}
			else {
				if(response.status === 1) {
					location.href = "home.php";
				} 
				else if (response.status === 3) {
					//location.href = "verifyMail.php";
					Materialize.toast('A validation email has been sent to '+email+'. If you cant find it, please check your SPAM folder.', 4000, 'rounded', function(){location.href = "verifyMail.php";});
				} 
			}

		}).fail(function(response){
			console.log(response);
		});
	} 
	else {
		Materialize.toast('You have to accept the License agreements!', 4000, 'rounded');
	}
}

// Edit function
function regEdit(){
	$.ajax({
		url: 'resources/handler/edit.php',
		data: $("#editBox").serialize(),
		type: 'POST'
		,dataType:'json'
		
	}).done(function(response){

		if(response.length === 0){
			Materialize.toast('Successfully Saved', 4000, 'rounded');	
		} 
		else if(response[0] === "emailSend"){

			// TODO: Remove callback when mail works.
			// Local: Materialize.toast('An validationemail has been send to your new submitted emailaddress', 6000, 'rounded', function(){location.href = "edit.php";}); 	
			Materialize.toast('An validationemail has been send to your new submitted emailaddress. If you cant find it, please check your SPAM folder.', 6000, 'rounded'); 	
		}
		else if(response[0] == "Error"){
			Materialize.toast(response[1], 4000, 'rounded');
		}
		else {
			for(var i = 0; i < response.length; i++){
				$("input[name='"+response[i]+"']").toggleClass("invalid valid");
				var lbl = $("input[name='"+response[i]+"']").siblings("label").text();
				Materialize.toast(lbl + ' has been typed wrong', 4000, 'rounded');
			}
		}
	}).fail(function(response){
		console.log(response);
	});
}

// Gets the name from the page that is active, to use for handlers.
function getPageName(url) {
    var index = url.lastIndexOf("/") + 1;
    var filenameWithExtension = url.substr(index);
    var filename = filenameWithExtension.split(".")[0]; // <-- added this line
    return filename;                                    // <-- added this line
}

// redirect
function redirect(destination){
	location.href = destination;
}

//**************************************************************
//	AfterAction Functions
//**************************************************************

function triggerUpdateEmail(email){
	$('#updateEmailSpan').html(email);
	$('#modalUpdateEmail').modal('open');
}

// This function is called if there is a new social email, it will be called from the classSocialLogin from a object (Line 90 - currently).
function updateEmail(){

	var token = $("#token").val();
	    
    $.ajax({
		url: 'resources/handler/login.php',
		data: {
			event: 'updatePrimaryEmail',
			token: token
		},
		type: 'POST'
		,dataType:'json'
	}).done(function(response){

		if(response == 1){
			$('#modalUpdateEmail').modal('close');
			Materialize.toast('Your primary email address was updated', 4000, 'rounded');
			Materialize.toast('Redirecting ...', 4000, 'rounded', function(){location.href = "home.php";}); 	
		} else {
			Materialize.toast('Sorry, unexpected error accured!', 4000, 'rounded', function(){location.href = "home.php";});
		}
		
	}).fail(function(response){
		console.log(response);
	});
	
}