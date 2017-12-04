<?php

	require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
	require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');

	// If a POST has been made to server
	if($_SERVER['REQUEST_METHOD'] == 'POST'){

		// If POST and Stored Token is equal
		if($_POST["token"] == $_SESSION["token"]){

			$event = "signUp";
			if(isset($_POST["event"])){
				$event = $_POST["event"];
			}

			if($event == "logIn"){

				$allowInsert = true;
				$aValidationFails = Array();

				$email = $_POST["email"];
				$password = $_POST["password"];
				$autoLogin = $_POST["autoLogin"];

				$classValidation = new validation();

				// Validate Email
				$status = $classValidation->validateEmail($email);
				if($status == false){
					// String isnt a valid email.
					$allowInsert = false;
					array_push($aValidationFails, "loginEmail");
				} 
				// TODO: Diskuter om systemet må fortælle om det er email, eller brugernavn som er forkert.
				// else {
				// 	$classSocialLogin = new socialLogin();
				// 	$status = $classSocialLogin->checkEmail($email);
				// 	if($status == 0){
				// 		// Email allready exist in database.
				// 		$allowInsert = false;
				// 		array_push($aValidationFails, "logIn-email");
				// 	}
				// }

				//Validate Password
				$status = $classValidation->validatePassword($password);
				if($status == false){
					// String isnt allowed as password.
					$allowInsert = false;
					array_push($aValidationFails, "loginPassword");
				}

				// check for conflicts
				if($allowInsert == false){
					echo json_encode($aValidationFails);
					die();
				} 

				// Create new userLogin Obj if no confilcts found
				$classUser = new user();

				// Check for login Attempts
				$attempts = $classUser->logInAttempts();
				if($attempts < 5){
					echo $classUser->login($email, $password, $autoLogin);
				} else {
					// On 5th unsuccesfull login attempt, user gets a cooldown.
					echo '{"status":0,"message":"Login cooldown"}';
				}
				
			// Triggers if mail have been changed on a social media, and if the mail doesnt match the userlogin mail in the user table.
			} else if($event == "updatePrimaryEmail"){

				$objUser = json_decode($_SESSION["tempUser"]);

				$classUser = new user();
				$classUser->updateEmail($objUser->socialUserId, $objUser->socialMediaId, $objUser->email);

				$_SESSION['user']['uEmail'] = $objUser->email;

				echo 1;
			}
		} 
		else {
			// Tokens is not equal
			echo json_encode(["Error","Token Error"]);
		}
	} 
	else {
		// No POST has been made to server
		echo json_encode(["Error","Post Error"]);
	}

?>