<?php
	require('config.php');
	require("resources/includes/inclHead.php");

	// Creating object from tempUser session.
	$objUser = json_decode($_SESSION['tempUser']);
	//$objUser->imagePath = "";

	// Initializing user class.
	$classUser = new user();
	// Calls method to get the users current image.
	$imagePath = $classUser->fetchUserImage($objUser->userId);

	// If beeing sent to merge directly from index. Then email is used from tempUser object. But if beeing sent to merge from create (where you added a mail that allready exixst), then mail is used from get.
	if (isset($_GET['email'])) {
		$email = $_GET['email'];
	} else {
		$email = $objUser->email;
	}

	include("resources/includes/inclToken.php");
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Social Login | Merge</title>
		<?php require("resources/includes/inclStyles.php");?>
		<link rel="stylesheet" href="css/merge.css">

		<!-- Google API -->
	    <meta name="google-signin-scope" content="profile email">
	    <meta name="google-signin-client_id" content="<?php echo GOOGLE_API; ?>">
	</head>
	<body>

		<div id="boxWrap" class="row valign-wrapper">
			<div id="box" class="valign col s12 l4 z-depth-2">
				
				<div id="boxHeader" class="row z-depth-2">
					<div class="centerContent">
						<h5>Merge</h5>
						<p>Already got an account? - let us merge them.</p>
					</div>
				</div>

				<div id="boxMerge" class="row">
					<div class="centerContent">
						<div class="col s5">
						<?php 
							if(isset($_SESSION["tempUser"]) && $objUser->imagePath != ""){
								echo '<div class="right"><img src="'.$objUser->imagePath.'" class="z-depth-2 circle responsive-img"></div>';
							} 
							else {
								echo '<div class="right"><div class="z-depth-2" id="boxMerge-male"><i class="fa fa-user" aria-hidden="true"></i></div></div>';
							}
						?>
						</div>	
						<div class="col s2 center-align">
							<span class="fa-stack fa-lg">
							  <i class="fa fa-circle fa-stack-2x grey-text text-lighten-3"></i>
							  <i class="fa fa-exchange fa-stack-1x fa-inverse grey-text text-lighten-1"></i>
							</span>
						</div>
						<div class="col s5">
						<?php 
							if($imagePath == "none"){
								echo '<div class="left"><div class="z-depth-2" id="boxMerge-female"><i class="fa fa-user" aria-hidden="true"></i></div></div>';
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
				          <input id="mergeEmail" type="email" value="<?php echo $email; ?>" disabled>
				          <label for="mergeEmail" class="active">Email</label>
				        </div>
						<div class="input-field col s12">
				          <input id="mergePassword" type="password" class="validate">
				          <label for="mergePassword">Password</label>
				        </div>
				        <input type="hidden" id="token" name="data[token]" value="<?php echo $token; ?>">
					</div>
				</div>
				<div id="boxAction" class="row">
					
				<a id="mergeButton" class="btn-floating btn-large waves-effect waves-light pink accent-2">
					<i class="fa fa-exchange" aria-hidden="true"></i>
				</a>

				</div>
				<div id="boxBottom" class="row">
					<div class="centerContent">
						<?php 
							if (isset($objUser->socialMediaId)){
								if ($objUser->socialMediaId == 1) {
									echo '<a href="#!" onclick="logoutFacebook();">Dismiss</a> |';
								} else if ($objUser->socialMediaId == 2) {
									echo '<a href="#!" onclick="signOut();">Dismiss</a> |';
								} else if ($objUser->socialMediaId == 3) {
									echo '<a href="#!" onclick="linkedInLogout();">Dismiss</a> |';
								} 
							} 
							else {
								echo '<a href="logout.php">Dismiss</a> |';
							}
							require("js/facebookLogout.php");
							require("js/googleLogout.php");
							require("js/linkedinLogout.php");
						?>
						<a class="modal-trigger" href="#modalAbuse">Report Abuse</a>
					</div>
				</div>
			</div>
		</div>

		 <!-- Modal Report abuse -->
		  <div id="modalAbuse" class="modal">
		    <div class="modal-content">
		      <h4>Account abuse report</h4>
		      <p><i>This feature is not included in the bachelor project</i></p>
		    </div>
		    <div class="modal-footer">
		      <a href="#!" class=" modal-action modal-close waves-effect waves-light btn-flat">Ok</a>
		    </div>
		  </div>
		
		<?php require("resources/includes/inclFooter.php"); ?>
		<script src="js/merge.min.js"></script>	
	</body>
</html>