<?php
	 error_reporting(E_ALL);
	 ini_set("display_errors","On");

	require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
	require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');
	
	// IF autologin cookie is set, then page will login user.
	if (isset($_COOKIE['autoLogin'])) {
		$objCookie = json_decode($_COOKIE['autoLogin']);

		$classUser = new user();
		$loginResponse = json_decode($classUser->login($objCookie->email, $objCookie->password, $autoLogin = 1, 0));

		if ($loginResponse->status == 1) {
			header("Location: home.php");
			exit;
		}
	}

	// If user session is set, system will check if mail have been verified. If not user is sent to verifymail, otherwise user is sent to home (Login).
	if (isset($_SESSION['user'])) {
		$classUser = new user();
		$verifyCode = $classUser->checkVerifyCode($_SESSION['user']['uId']);
		if ($verifyCode == "0") {			
			header("Location: home.php");
			exit;
		} else if ($verifyCode != "0") {	
			header("Location: verifyMail.php");
			exit;			
		}
	}
	
	include("resources/includes/inclToken.php");
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Social Login | Sign In</title>
		<?php require("resources/includes/inclStyles.php");?>
		<link rel="stylesheet" href="css/index.css">

    	<meta name="google-signin-scope" content="profile email">
    	<meta name="google-signin-client_id" content="<?php echo GOOGLE_API; ?>">
	</head>
	<body>
	
		<div id="boxWrap" class="row valign-wrapper">
			<div id="box" class="valign col s12 l4 z-depth-2">
				
				<div id="boxHeader" class="row z-depth-2">
					<div class="centerContent">
						<h5>Social Login</h5>
						<p>Signin or create an account</p>
					</div>
				</div>

				<div id="boxContent" class="row">
					<div class="centerContent">
						<div class="input-field col s12">
				          <input id="loginEmail" type="email" class="validate">
				          <label for="loginEmail">Email</label>
				        </div>
						<div class="input-field col s12">
				          <input id="loginPassword" type="password" class="validate">
				          <label for="loginPassword">Password</label>
				        </div>
						<div class="switch">
							<label>
								<input id="autologin" name="autologin" type="checkbox">
								<span class="lever"></span>
								Remember me
							</label>
						</div>
						<input type="hidden" id="token" name="data[token]" value="<?php echo $token; ?>">
					</div>
				</div>
				<div id="boxAction" class="row">
					<span class="hide-on-med-and-up">
						<a href="create.php?event=new">Create Account</a> | 
						<a class="modal-trigger" href="#modalPassword">Forgot password?</a>
					</span>
					
					<div id="loginButtons" class="fixed-action-btn horizontal click-to-toggle active">
				    <a id="loginButton" class="btn-floating btn-large pink accent-2">
				      <i class="fa fa-sign-in" aria-hidden="true"></i>
				    </a>
				    <ul>
				      <li>
				      	<a id="fb-login" class="btn-floating">
				      		<i class="fa fa-facebook" aria-hidden="true"></i>
				      	</a>
				      </li>
				      <li>
				      	<a id="gl-login" class="btn-floating">
				      		<i class="fa fa-google" aria-hidden="true"></i><div class="g-signin2" data-onsuccess="onSignIn"></div>
				      	</a>
				      </li>
				      <li>
				      	<a id="li-login" class="btn-floating" onclick="linkedinLogin();">
				      		<i class="fa fa-linkedin" aria-hidden="true"></i>
				      	</a>
				      </li>
				      <!-- <li>
				      	<a id="logInButton1" class="btn-floating">
				      		<i class="fa fa-sign-in" aria-hidden="true"></i>
				      	</a>
				      </li> -->
				    </ul>
				  </div>
				</div>
				<div id="boxBottom" class="row hide-on-small-only">
					<div class="centerContent">
						<a href="create.php?event=new">Create Account</a> | 
						<a class="modal-trigger" href="#modalPassword">Forgot Password?</a>
					</div>
				</div>
			</div>
		</div>

		 <!-- Modal Forgot password -->
		  <div id="modalPassword" class="modal">
		    <div class="modal-content">
		      <h4>Forgot Password?</h4>
		      <p><i>This feature is not included in the bachelor project</i></p>
		    </div>
		    <div class="modal-footer">
		    	<a href="#!" class=" modal-action modal-close waves-effect waves-light btn-flat">Ok</a>
		    </div>
		  </div>

		  <!-- Modal Update Email -->
			<div id="modalUpdateEmail" class="modal">
				<div class="modal-content">
					<h4>Primary email update</h4>
					<p>You're current primary email doesn't match this one: <b><span id="updateEmailSpan"></span></b>, do you want to update it?</p>
					</p>
				</div>
				<div class="modal-footer">
					<a href="home.php" class="modal-action modal-close waves-effect waves-red btn-flat">Cancel</a>
					<a id="modalUpdateEmailButton" href="#!" class="modal-action waves-effect waves-light btn-flat">Update</a>
				</div>
			</div>

		<?php require("resources/includes/inclFooter.php"); ?>
		
		<script type="text/javascript" src="js/facebook.min.js"></script>
		<script type="text/javascript" src="js/google.min.js"></script>
		<script type="text/javascript" src="js/linkedin.min.js"></script>

    	<script src="https://apis.google.com/js/platform.js" async defer></script>
    	
	</body>
</html>