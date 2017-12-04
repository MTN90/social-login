<?php

	// include configurationfile and class'
	require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
	require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');

	// If a POST has been made to server
	if($_SERVER['REQUEST_METHOD'] == 'POST'){

		// if a special event has been set, fetch it. 
		$event = "signUp";
		if(isset($_POST["event"])){
			$event = $_POST["event"];
		}

		// If POST and Stored Token is equal
		if(isset($_POST["data"]["token"])){

			// If POST and Stored Token is equal
			if($_POST["data"]["token"] == $_SESSION["token"]){

				/* this event is callen to check that an typed email is valid and its not in use by another user.
				if its used by another user, check that the owner is the person who has typed the email */
				if($event == "checkEmail"){

					//Email variable from post
					$email = $_POST["email"];

					//response return
					$response;

					// Validate that the given email is a valid typed email. 
					$classValidation = new validation();
					$status = $classValidation->validateEmail($email);

					// If email is valid
					if($status == true){

						// Check if email is in use by another user.
						$classSocialLogin = new socialLogin();
						$status = $classSocialLogin->checkEmail($email);

						// If email is in use by another user.
						if ($status == 1) {

							// Check that email belongs to the typing user	
							$classUser = new user();		
							$status = $classUser->checkEmailOwner($_SESSION['user']["uId"], $email);

							// if email belongs to typing user
							if ($status == 1) {

								// Email is allowed
								$response = 0;
							} else {

								// Email belongs to other user - email is NOT allowed
								$response = 2;
							}
						} else {

							// Checks if email exist in usersocial
							$statusSocial = $classSocialLogin->checkSocialEmailExist($email);

							if ($statusSocial == 1) {
								$userId = $_SESSION['user']['uId'];
								// Checks if email is connected to current user.
								$status = $classSocialLogin->checkSocialEmailExist($email, $userId);

								if ($status == 1) {
									$response = 0;
								}
								else {
									$response = 3;
								}
							} else {
								/* Current typed Email is rather Allowed or NOT allowed
								 response status can be 0 (Allowed) or 2 (error, multiple 
								 users found with this email) */
								$response = $status;
							}

							
						}

					} else {

						// NOT allowed (typed "email" is not a valid email)
						$response = 2;
					}
					
					// return email status result to Ajax
					echo $response;

				} else {

					// Preset "allow userinsert" in DB to true.
					$allowInsert = true;

					// Array of validation failures (name attr fields gets in here)
					$aValidationFails = Array();

					// All data from submitted form
					$data = $_POST["data"];

					// Validation class
					$classValidation = new validation();

					// User Class
					$classUser = new user();

					// Validate Email
					$status = $classValidation->validateEmail($data["uEmail"]);
					if($status == false){

						// Email is not an email
						$allowInsert = false;
						array_push($aValidationFails, "data[uEmail]");
					} else {

						// Email is an email, check that email is not in use
						$classSocialLogin = new socialLogin();
						$status = $classSocialLogin->checkEmail($data["uEmail"]);
						if($status == 1){

							// Email is an email, check that email is not in use
							$status = $classUser->checkEmailOwner($_SESSION['user']["uId"], $data["uEmail"]);
							if($status == 0){

								//email is in use by another user
								$allowInsert = false;
								array_push($aValidationFails, "data[uEmail]");
							}
						} else {
							// Checks if email exist in usersocial table
							$status = $classSocialLogin->checkSocialEmailExist($data["uEmail"]);
							if ($status == 1) {
								$userId = $_SESSION['user']['uId'];
								// Checks if email is connected to current user.
								$status = $classSocialLogin->checkSocialEmailExist($data["uEmail"], $userId);
								if ($status == 0) {
									$allowInsert = false;
									array_push($aValidationFails, "data[uEmail]");
								}
							}
						}
					}



					// Required! ---------------------------------------------------



					// Validate Firstname
					$status = $classValidation->validateName($data["uFirstname"]);
					if($status == false){

						// String is not a firstname, Not allowed!
						$allowInsert = false;
						array_push($aValidationFails, "data[uFirstname]");
					}

					// Validate Lastname
					$status = $classValidation->validateName($data["uLastname"]);
					if($status == false){

						// String is not a lastname, Not allowed!
						$allowInsert = false;
						array_push($aValidationFails, "data[uLastname]");
					}

					// Validate Gender
					$status = $classValidation->validateGender($data["uGender"]);
					if($status == false){

						// Int is not a gender, Not allowed!
						$allowInsert = false;
						array_push($aValidationFails, "data[uGender]");
					}

					//Validate BirthDate
					$status = $classValidation->validateBirthDate($data["uBirthDate"]);
					if($status == false){

						// String is not a birthdate, Not allowed!
						$allowInsert = false;
						array_push($aValidationFails, "data[uBirthDate]");
					}

					//Validate Password
					if($data["uPassword"] == $data["uConfirm"]){
						if($data["uPassword"] != ""){

							//String is not a password, OR password is not strong enough
							$status = $classValidation->validatePassword($data["uPassword"]);
							if($status == false){
								$allowInsert = false;
								array_push($aValidationFails, "data[uPassword]");
							}
						}
					} else {

						//Password string and confirm password string does NOT match, Not allowed!
						$allowInsert = false;
						array_push($aValidationFails, "data[uConfirm]");
					}



					// NOT Required! ---------------------------------------------------



					// Validate Phone
					if($data["uPhone"] != ""){
					$status = $classValidation->validatePhone($data["uPhone"]);
						if($status == false){

							//String is not a Phonenumber, NOT allowed!
							$allowInsert = false;
							array_push($aValidationFails, "data[uPhone]");
						}
					}

					// Validate Address
					if($data["uAddress"] != ""){
					$status = $classValidation->validateAddress($data["uAddress"]);
						if($status == false){

							// String is not a address, Not allowed!
							$allowInsert = false;
							array_push($aValidationFails, "data[uAddress]");
						}
					}

					// Validate Number
					if($data["uAddressNumber"] != ""){
					$status = $classValidation->validateNumber($data["uAddressNumber"]);
						if($status == false){

							// Int is not a number, Not allowed!
							$allowInsert = false;
							array_push($aValidationFails, "data[uAddressNumber]");
						}
					}

					// Validate Misc
					if($data["uAddressMisc"] != ""){
					$status = $classValidation->validateMisc($data["uAddressMisc"]);
						if($status == false){

							// String is not a misc, Not allowed!
							$allowInsert = false;
							array_push($aValidationFails, "data[uAddressMisc]");
						}
					}

					// Validate Zip
					if($data["uAddressZip"] != "" || $data["uAddressCity"] != ""){
					$status = $classValidation->validateZip($data["uAddressZipCityId"]);
						if($status == false){

							// Int is not a Zip & or string is not a city Not allowed!
							$allowInsert = false;
							array_push($aValidationFails, "data[uAddressZip]");
							array_push($aValidationFails, "data[uAddressCity]");
						}
					}

					// Validate Country
					if($data["uAddressCountry"] != ""){
					$status = $classValidation->validateCountry($data["uAddressCountry"]);
						if($status == false){

							// String is not a coutry, Not allowed!
							$allowInsert = false;
							array_push($aValidationFails, "data[uAddressCountry]");
						}
					}



					// ------------------------------------------------------------------
					


					/* check for conflicts - if $allowInsert is still true insert user in db,
					if $allowInsert is false, echo array with errors*/
					if($allowInsert == false){
						echo json_encode($aValidationFails);
						die();
					} 

					//Check that typed email belongs to user
					$mailStatus = $classUser->checkEmailOwner($_SESSION['user']["uId"], $data['uEmail']);

					/* if new typed email doesnt exist in db, new email needs to be validated 
					before new email is inserted in user tabel. new email is temperrory inserted in mailchange table */
					if ($mailStatus == 0) {

						// Create mail change row.
						$hashCode = $classUser->mailChangeAddRow($data['uEmail']);

						// Send mail to user with link according to MODE
						if(TEST_MODE == 1) {
							$link = "http://localhost:8080/login/mailChange.php?code=".$hashCode;
						}
						else{
							$link = "https://login.coffee.build/mailChange.php?code=".$hashCode;
						}
						// Send validation email to user
						$classMail = new mail();
						$content = 'Hello <b>'.$data['uFirstname'].' '.$data['uLastname'].'</b> <br><br> You are about to change your primary email on login.coffee.build. If you want to proceed, please click <a href="'.$link.'"><b>here</b></a> and confirm your changes. <br><br> If you cant click the link, please copy and paste this line in your browser: '.$link.'<br><br><i> Best Regards <br>Daniel Steffensen & Morten Nielsen</i>';
						$classMail->sendMail($data['uEmail'], "Change of email on login.coffee.build", $content);

						// TODO: Remove this when mail works.
						// $_SESSION['editMail'] = $classMail->sendMail($data['uEmail'], "Skift til ny mail på -navn-", $content);

						// Push Email notify to user
						array_push($aValidationFails, "emailSend");
					}

					// Update users information
					$classUser->edit($_SESSION['user']["uId"], $data);

					// echo validation errors if any given. 
					echo json_encode($aValidationFails);
				}

			}
			else {
				// Tokens is not equal
				echo json_encode(["Error","Token Error"]);
			}

		} 
		else { 

			if(isset($_POST["token"])){
			
				if($_POST["token"] == $_SESSION["token"]){

					// If a zip has been changed/added in zip field, find the matching city in zipCity table
					if($event == "uAddressZip"){

						$zip = $_POST["zipCity"];
						$classUser = new user();	

						// return result to Ajax
						echo $classUser->getCityFromZip($zip);

					// If a city has been changed/added in city field, find the matching zip in zipCity table
					} else if($event == "uAddressCity"){

						$city = $_POST["zipCity"];
						$classUser = new user();

						// return result to Ajax
						echo $classUser->getZipFromCity($city);

					// If no special event has been set, update users information in db
					}
				
				} 
				else {
					// Tokens is not equal
					echo json_encode(["Error","Token Error"]);
				}
			} else {
				echo json_encode(["Error","No tokens found"]);
			}
		} 
	} 
	else {
		// No POST has been made to server
		echo json_encode(["Error","Post Error"]);
	}

?>