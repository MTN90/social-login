<?php 
	require('config.php');
	require("resources/includes/inclHead.php");
	
	if (!isset($_SESSION['user'])) {
		header("Location: index.php");
		exit;
	} 
	else {
		// If user session is set, but mail havent been verified, then user will be sent to verifyMail.php
		$classUser = new user();
		$verifyCode = $classUser->checkVerifyCode($_SESSION['user']['uId']);
		if ($verifyCode == "0") {
			header("Location: home.php");
			exit;
		}
	}

	include("resources/includes/inclToken.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Verify Mail</title>
	<title>Social Login | Verify Email</title>
	<?php require("resources/includes/inclStyles.php");?>
	<link rel="stylesheet" href="css/verifyMail.css">
</head>
	<body>

	<?php
	if (TEST_MODE == 1) {	
		if (!isset($_SESSION['email'])) {
			echo "Check your mail for verifycode (Click Resend to get new code here)";
		} else {
			echo $_SESSION['email'];
		}
	}
	?>

		<div id="boxWrap" class="row valign-wrapper">
			<div id="box" class="valign col s12 l4 z-depth-2">
				
				<div id="boxHeader" class="row z-depth-2">
					<div class="centerContent">
						<h5>Verification</h5>
						<p>Check your Email indbox, to verify yourselv.</p>
					</div>
				</div>

				<div id="boxVerify" class="row">
					<div class="centerContent">
						<div class="col s5">

							<div class="right"><div class="z-depth-2" id="boxVerify-id"><i class="fa fa-id-card" aria-hidden="true"></i></div></div>

						</div>	
						<div class="col s2 center-align">
							<span class="fa-stack fa-lg">
							  <i class="fa fa-circle fa-stack-2x grey-text text-lighten-3"></i>
							  <i class="fa fa-exchange fa-stack-1x fa-inverse grey-text text-lighten-1"></i>
							</span>
						</div>
						<div class="col s5">
						<?php 
							if($_SESSION['user']["uppImagePath"] == ""){
								echo '<div class="left"><div class="z-depth-2" id="boxVerify-user"><i class="fa fa-user" aria-hidden="true"></i></div></div>';
							} 
							else {
								echo '<div class="left"><img src="'.$imagePath.'" class="z-depth-2 circle responsive-img"></div>';
							}
						?>
						</div>
					</div>
				</div>

				<div id="boxContent" class="row">
					<div class="centerContent">
						<div class="input-field col s12">
				          <input id="verifyEmail" type="email" value="<?php echo $_SESSION['user']["uEmail"]; ?>" disabled>
				          <label for="verifyEmail" class="active">Email</label>
				        </div>
						<div class="input-field col s12">
				          <input id="verifyCode" type="text">
				          <label for="verifyCode">Code</label>
				        </div>
				        <input type="hidden" id="token" name="data[token]" value="<?php echo $token; ?>">
					</div>
				</div>
				<div id="boxAction" class="row">
					
				   <a id="verifyButton" class="btn-floating btn-large waves-effect waves-light pink accent-2">
					 	<i class="fa fa-check" aria-hidden="true"></i>
					</a>

				</div>
				<div id="boxBottom" class="row">
					<div class="centerContent">
						<a href="logout.php">Dismiss</a> | 
						<a id="verifyResend">Resend Email</a>
					</div>
				</div>
			</div>
		</div>

		<?php require("resources/includes/inclFooter.php"); ?>
		<script src="js/verifyMail.min.js"></script>
	</body>
</html>