<?php

	require('config.php');
	require("resources/includes/inclHead.php");

	// Gets hashed code from url (Users will be sent to this page with a link that contains hashed code.)
	if (isset($_GET['code'])) {
		$hash = $_GET['code'];
		$classUser = new user();
		$response = $classUser->mailChangeDetails($hash);
		if($response == 0){
			//header("Location: home.php");
		}
	}
	else{
		header("Location: home.php");
	}
	
	include("resources/includes/inclToken.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Social Login | Change Email</title>
	<?php require("resources/includes/inclStyles.php");?>
	<link rel="stylesheet" href="css/mailChange.css">
</head>
	<body>
		
		<?php 
			if (isset($_SESSION['user'])) {
				include("resources/includes/navigation.php"); 
			}
		?>

		<div id="boxWrap" class="row valign-wrapper">
			<div id="box" class="valign col s12 l4 z-depth-2">
				
				<div id="boxHeader" class="row z-depth-2">
					<div class="centerContent">
						<h5>Confirm change</h5>
						<p>Your primary email is about to be changed</p>
					</div>
				</div>

				<div id="boxEmailChange" class="row">
					<div class="centerContent">
						<div class="col s5">

							<div class="right"><div class="z-depth-2" id="boxEmailChange-email"><i class="fa fa-envelope" aria-hidden="true"></i></div></div>

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
								echo '<div class="left"><div class="z-depth-2" id="boxEmailChange-user"><i class="fa fa-user" aria-hidden="true"></i></div></div>';
							} 
							else {
								echo '<div class="left"><img src="'.$_SESSION['user']["uppImagePath"].'" class="z-depth-2 circle responsive-img"></div>';
							}
						?>
						</div>
					</div>
				</div>

				<div id="boxContent" class="row">
					<div class="centerContent">
						<div class="input-field col s12">
				          <input id="changeEmailOld" type="email" value="<?php echo $response['mcOldMail']; ?>" disabled>
				          <label for="changeEmailOld" class="active">From</label>
				        </div>
						<div class="input-field col s12">
				          <input id="changeEmailNew" type="email" value="<?php echo $response['mcNewMail']; ?>" disabled>
				          <label for="changeEmailNew" class="active">To</label>
				        </div>
				        <input id="emailChangeHash" type="hidden" value="<?php echo $hash ?>">
				        <input type="hidden" id="token" name="data[token]" value="<?php echo $token; ?>">
					</div>
				</div>
				<div id="boxAction" class="row">
					
				   <a id="emailChangeButton" class="btn-floating btn-large waves-effect waves-light pink accent-2">
					 	<i class="fa fa-check" aria-hidden="true"></i>
					</a>

				</div>
				<div id="boxBottom" class="row">
					<div class="centerContent">
						<a href="index.php">Dismiss</a> | 
						<a id="emailChangeCancel">Cancel Changes</a>
					</div>
				</div>
			</div>
		</div>

		<?php require("resources/includes/inclFooter.php"); ?>
		<script type="text/javascript" src="js/mailChange.min.js"></script>

	</body>
</html>