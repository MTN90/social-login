<?php
	require('config.php');
	require("resources/includes/inclHead.php");
	unset($_SESSION["tempUser"]);

	// If no user session is set, then user will be redirected to index.php
	if (!isset($_SESSION['user'])) {
		header("Location: index.php");
		exit;	
	} else if (isset($_SESSION['user'])) {
		// If user session is set, but mail havent been verified, then user will be sent to verifyMail.php
		$classUser = new user();
		$verifyCode = $classUser->checkVerifyCode($_SESSION['user']['uId']);
		if ($verifyCode != "0") {
			header("Location: verifyMail.php");
			exit;
		}	
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Social Login | Home</title>
	<?php require("resources/includes/inclStyles.php");?>
	<link rel="stylesheet" href="css/home.css">
</head>
	<body>

		<?php include("resources/includes/navigation.php"); ?>

		<div class="container">
			<div class="row">

				<div class="log col s12 m8 offset-m2 l6">
					<div class="card-panel transparent center-align">
						<!-- <i class="fa fa-history on fa-circle fa-2x grey-text text-lighten-1"></i> -->
						<span class="fa-stack fa-lg">
						  <i class="fa fa-circle fa-stack-2x grey-text text-lighten-1"></i>
						  <i class="fa fa-star fa-stack-1x fa-inverse"></i>
						</span>
					</div>
				</div>
				<div id="userLog"></div>
			</div>
		</div>

		<div class="fixed-action-btn">
			<a id="fetchLog" class="btn-floating btn-large waves-effect waves-light pink accent-2"><i class="fa fa-refresh"></i></a>
		</div>
		<?php require("resources/includes/inclFooter.php"); ?>

		<!-- script -->
		<script type="text/javascript" src="js/home.min.js"></script>
    	
</body>
</html>