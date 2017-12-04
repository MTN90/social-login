<?php
	
	// include config anf class'
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

			if($_POST["data"]["token"] == $_SESSION["token"]){

				// if specialevent is signUp
				if($event == "signUp"){

					// Preset "allow userinsert" in DB to true.
					$allowInsert = true;

					// Array of validation failures (name attr fields gets in here)
					$aValidationFails = Array();

					// All data from submitted form
					$data = $_POST["data"];

					// New validation 
					$classValidation = new validation();

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

							//email is in use by another user
							$allowInsert = false;
							array_push($aValidationFails, "data[uEmail]");
						} else {
							$status = $classSocialLogin->checkSocialEmailExist($data["uEmail"]);
							if ($status == 1) {

							//email is in use by another user on their social user.
							$allowInsert = false;
							array_push($aValidationFails, "data[uEmail]");
							}
						}
					}

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
					//echo $data["uGender"];

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

					// Validate PP field
					if(isset($data["uppActive"])){
						if($data["uppActive"] != 1){

							// Int is not a Profile picture flag, Not allowed!
							$allowInsert = false;
							array_push($aValidationFails, "data[uppActive]");
						}
					} else {
						$data["uppActive"] = 0;
					}

					//Validate Password
					if($data["uPassword"] == $data["uConfirm"]){
						$status = $classValidation->validatePassword($data["uPassword"]);
						if($status == false){

							//String is not a password, OR password is not strong enough
							$allowInsert = false;
							array_push($aValidationFails, "data[uPassword]");
						}
					} else {
						//Password string and confirm password string does NOT match, Not allowed!
						$allowInsert = false;
						array_push($aValidationFails, "data[uConfirm]");
					}
					
					/* check for conflicts - if $allowInsert is still true insert user in db,
					if $allowInsert is false, echo array with errors*/
					if($allowInsert == false){
						echo json_encode($aValidationFails);
						die();
					} 

					// Create new user if no confilcts found
					$classUser = new user();
					$classUser->create($data);

					// Login response
					$loginResponse = "";

					// Further verification status
					$verificationNeeded = 0;

					// If user has signedIn with social media 
					if (isset($_SESSION["tempUser"])) {

						// set object with social media informations
						$objUser = json_decode($_SESSION["tempUser"]);

						// If user continues using same social email - No further validation required
						if ($objUser->email == $data["uEmail"]) {
							$classUser->verifyUserMail($data["uEmail"]);
						} else {

							// If user has changed his email - further validation required!
							$verificationNeeded = 1;
						}	

						// auto signIn user
						$classUser->createUserObject($objUser->socialUserId);
						$loginResponse = '{"status":1}';

					} else {

						// User has manualy typed in an email, further validation required!
						$verificationNeeded = 1;

						//Sign user in by login method. 
						$loginResponse = $classUser->login($data["uEmail"], $data["uPassword"]);
					}

					// if further validation required send email to user
					if ($verificationNeeded == 1) {

						// Send verification code!
						$classMail = new mail();

						// fetch verifycode from user
						$verifyCode = $classUser->checkVerifyCode($_SESSION['user']['uId']);

						// fetch verifycode from user
						$content = 'Hello <b>'.$data["uFirstname"].' '.$data["uLastname"].'</b> <br><br> Thank you for creating an account on our Social Login. Just to ensure you actually are a human, Please use this code to verify your email address: <br>'.$verifyCode.'</b><br><br> Copy and paste the code in the validation form, wich will be accessible next time you log in to your account. <br><br><i> Bedst Regards <br>Daniel Steffensen & Morten Nielsen</i>';
						
						// Send email
						$classMail->sendMail($data["uEmail"], "Thanks for joining, please verify Your email!", $content);	
					}

					// Return login status response
					echo $loginResponse;
					
				}
			} 
			else {
				// No POST has been made to server
				echo json_encode(["Error","Token Error"]);		
			} 

		} 
		else {

			// if token [token] is set
			if(isset($_POST["token"])){

				// If POST and Stored Token is equal
				if($_POST["token"] == $_SESSION["token"]){
			
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

								// Check that user comes from a social login
								if (isset($_SESSION['tempUser'])) {

									// Check that its the typing user who owns the email
									$classUser = new user();
									$uId = $classUser->getUserIdByEmail($email);

										// Add Social userId to existing user session
										$objUser = json_decode($_SESSION["tempUser"]);
										$objUser->userId = $uId;

										// Update socialUserId in session
										$_SESSION["tempUser"] = json_encode($objUser);

									// Current typed email is allowed (maybe owned by the typing user)
									$response = 1;

								} else {

									// Current typed email is NOT allowed /typing user will have to login instead)
									$response = 3;
								}

							} else {

								// Checking if email exist in usersocial, gives it color.
								$statusSocial = $classSocialLogin->checkSocialEmailExist($email);

								if ($statusSocial == 1) {
									$response = 4;
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
						// return response for AJAX call
						echo $response;
					}
				} 
				else {
					// No POST has been made to server
					echo json_encode(["Error","Token Error"]);		
				}
			}
		} 
	} 
	else {
		// No POST has been made to server
		echo json_encode(["Error","Post Error"]);
	}
?>