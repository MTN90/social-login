<?php

	// include configurationfile and class'
	require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
	require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');

	// If a POST has been made to server
	if($_SERVER['REQUEST_METHOD'] == 'POST'){

		// If POST and Stored Token is equal
		if($_POST["token"] == $_SESSION["token"]){

			// if a special event has been set, fetch it. 
			$event = "change";
			if(isset($_POST["event"])){
				$event = $_POST["event"];
			}

			if($event == "change"){

				$hash = $_POST['hash'];

				// target information by uniq hashcode - creates temp. session
				$classUser = new user();
				$response = $classUser->mailChangeDetails($hash);

				// If Hash code matches in DB
				if($response != "0"){

					// Perform changes
					$classUser->mailChangeFinal();

					// If user session is set (If user is logged in), then session will be updated.
					if (isset($_SESSION['user'])) {
						$_SESSION['user']['uEmail'] = $response['mcNewMail'];
					}

					echo 1;

				}
				// Else echo response (0)
				else {
					echo $response;
				}
			}
			else if($event == "cancel"){

				$hash = $_POST['hash'];

				// target information by uniq hashcode - creates temp. session
				$classUser = new user();
				$response = $classUser->mailChangeDetails($hash);

				// If Hash code matches in DB
				if($response != 0){

					// delete/archive row
					$response = $classUser->mailChangeCancel();
					echo $response;
				}
				// Else echo response (0)
				else {
					echo $response;
				}
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