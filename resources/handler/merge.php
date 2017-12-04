<?php 

	require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
	require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');

	// If a POST has been made to server
	if($_SERVER['REQUEST_METHOD'] == 'POST'){

		// If POST and Stored Token is equal
		if($_POST["token"] == $_SESSION["token"]){

			// Get data from ajax call.
			$email = $_POST['mergeEmail'];
			$password = $_POST['mergePassword'];

			//if tempUser isset
			if(isset($_SESSION["tempUser"])){

				// Create user object from tempUser.
				$objUser = json_decode($_SESSION["tempUser"]);

				// Create new userLogin Obj if no confilcts found
				$classUser = new user();

				// Check for login Attempts
				$attempts = $classUser->logInAttempts();
				if($attempts < 5){

					$classUser = new user();
					$loginResponse = $classUser->login($email, $password, 0, 1, 0);

					// Create object from login method.
					$objResponse = json_decode($loginResponse);

					if ($objResponse->status == 1) {
						// Adds social information to database connecting it to the user.
						$classUser->createUserSocial($objResponse->userid);
						// Creating image path from the social image.
						$imagePath = $classUser->getSocialImage($objUser->imagePath);
						// Saving the image in our own folder.
						$classUser->saveSocialImage($objResponse->userid, $objUser->socialMediaId, $imagePath, $objUser->imagePath);
						// Creating user object and stores it in $_SESSION['user'], this happends when logged in.
						$classUser->createUserObject($objUser->socialUserId);
						echo 1;
					}
					else {
						echo 0;
					}

				} else {
					// On 5th unsuccesfull login attempt, user gets a cooldown.
					echo 2;
				}

			} else {
				// No tempUser set (possible hacker attack)
				echo 3;
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