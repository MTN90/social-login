<?php 
	require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
	require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');

	// If a POST has been made to server
	if($_SERVER['REQUEST_METHOD'] == 'POST'){

		// If POST and Stored Token is equal
		if($_POST["token"] == $_SESSION["token"]){

			// Gets verifyCode from ajax call.
			$event = $_POST['event'];
			$classUser = new user();

			if ($event == "verifyCode") {
				$verifyCode = $_POST['verifyCode'];

				// Calls verifyMail method and sets it to 0 if uid and verifycode matches.
				$resp = $classUser->verifyMail($verifyCode);

				// Response is number of rows affect, if 1 its good.
				if ($resp == 1) {
					unset($_SESSION['email']);
				}
				echo $resp;

			} else if ($event == "resendEmail") {
				$userId = $_SESSION['user']['uId'];
				$verifyCode = $classUser->updateVerifyCode($userId);

				// Send verification code!
				$classMail = new mail();

				// fetch verifycode from user
				$content = 'Hello <b>'.$_SESSION['user']['uFirstname'].' '.$_SESSION['user']['uLastname'].'</b> <br><br> Thank you for creating an account on our Social Login. Just to ensure you actually are a human, please use this code to verify your email address: <b>'.$verifyCode.'</b><br><br> Copy and paste the code in the validation form, which will be accessible next time you log in to your account. <br><br><i> Best Regards <br>Daniel Steffensen & Morten Nielsen</i>';
				
				// Send email
				$classMail->sendMail($_SESSION['user']['uEmail'], "Thanks for joining, please verify Your email!", $content);
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