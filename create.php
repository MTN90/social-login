<?php
	require('config.php');
	require("resources/includes/inclHead.php");

	$objUser = "";
	if(isset($_SESSION["tempUser"])){
		$objUser = json_decode($_SESSION["tempUser"]);
	} 
	if(isset($_GET["event"])){
		if($_GET["event"] == "new"){
			unset($_SESSION['tempUser']);
		}
	}
	include("resources/includes/inclToken.php");
?>

<!DOCTYPE html>
<html>
	<head>	
		<meta charset="UTF-8">
		<title>Social Login | Sign Up</title>
		<?php require("resources/includes/inclStyles.php");?>
		<link rel="stylesheet" href="css/create.css">

		<!-- Google API -->
	    <meta name="google-signin-scope" content="profile email">
	    <meta name="google-signin-client_id" content="<?php echo GOOGLE_API; ?>">
	</head>
	<body>

		<div id="boxWrap" class="row valign-wrapper">
			<div id="box" class="valign col s12 l4 z-depth-2">
				
				<div id="boxHeader" class="row z-depth-2">
					<div class="centerContent">
						<h5>Sign Up</h5>
						<p>Fill out form, to create an account</p>
							<?php 
								if(isset($_SESSION["tempUser"]) && $objUser->imagePath != ""){
									echo '<img class="z-depth-2" src="'.$objUser->imagePath.'">';
								} 
								else {
									echo '<div class="z-depth-2" id="boxHeaderPP"><i class="fa fa-user" aria-hidden="true"></i></div>';
								}
							?>
					</div>
				</div>
				<div id="boxContent" class="row">
					<div class="centerContent">
						<form id="signupBox">				
							
							<input type="hidden" name="data[uppImagePath]" value="<?php if(isset($_SESSION["tempUser"])){echo $objUser->imagePath;} ?>">
							<input type="hidden" name="data[uppSocialMediaId]" value="<?php if(isset($_SESSION["tempUser"])){echo $objUser->socialMediaId;} ?>">

							<div class="input-field col s12">
					          <input type="email" id="signupEmail" class="validate" name="data[uEmail]"  value="<?php if(isset($_SESSION["tempUser"])){echo $objUser->email;} ?>">
					          <label for="signupEmail">Email</label>
					        </div>

							<div class="input-field col s12">
					          <input type="text" id="signupFirstname" name="data[uFirstname]" class="validate" value="<?php if(isset($_SESSION["tempUser"])){echo $objUser->firstName;} ?>">
					          <label for="signupFirstname">Firstname</label>
					        </div>

					        <div class="input-field col s12">
					          <input type="text" id="signupLastname" name="data[uLastname]" class="validate" value="<?php if(isset($_SESSION["tempUser"])){echo $objUser->lastName;} ?>">
					          <label for="signupLastname">Lastname</label>
					        </div>
							
							<div class="input-field col s12">
								<select id="signupGender" name="data[uGender]">
								<?php 
									$male = ""; $female = ""; 
									if($objUser->gender == "male"){$male = "selected";} else if($objUser->gender == "female"){$female = "selected";} 
								?>
									<!-- <option value="1" disabled selected>Gender</option> -->
									<option value="1" <?php echo $male; ?>>Male</option>
									<option value="2" <?php echo $female; ?>>Female</option>
								</select>
								<label for="signupGender">Gender</label>
							</div>

							<div class="input-field col s12">
								<label for="signupBirthdate">Birthdate</label>
						  		<input type="date" id="signupBirthdate" name="data[uBirthDate]" class="datepicker">
							</div>

							<div class="input-field col s12">
					          <input type="password" id="signupPassword" name="data[uPassword]" class="validate tooltipped" data-position="right" data-delay="50" data-tooltip="Min. 8 Chars. (123 + abc + ABC)">
					          <label for="signupPassword">Password</label>
					        </div>

					        <div class="input-field col s12">
					          <input type="password" id="signupConfirm" name="data[uConfirm]" class="validate tooltipped" data-position="right" data-delay="50" data-tooltip="Retype Password">
					          <label for="signupConfirm">Confirm Password</label>
					        </div>

							<div class="switch">
								<label>
									<input id="signupAgree" name="data[uAgree]" type="checkbox">
									<span class="lever"></span>
									I agree to the <a class="modal-trigger licenseLink" href="#modalAgreement">License Agreements</a>
								</label>
							</div>
							<?php 
								if(isset($_SESSION["tempUser"]) && $objUser->imagePath != ""){
								echo '
								<br>
								<div class="switch">
									<label>
										<input id="signUpActive" value="1" name="data[uppActive]" type="checkbox" checked>
										<span class="lever"></span>
										Use profile picture
									</label>
								</div>';
								}
							?>
							<input type="hidden" id="token" name="data[token]" value="<?php echo $token; ?>">
						</form>
					</div>
				</div>
				<div id="boxAction" class="row">
					<a id="signupButton" class="btn-floating btn-large waves-effect waves-light pink accent-2">
					 	<i class="fa fa-user-plus" aria-hidden="true"></i>
					</a>
				</div>
				<div id="boxBottom">
					<div class="centerContent">
						<?php 
							if (isset($objUser->socialMediaId)){
								if ($objUser->socialMediaId == 1) {
									echo '<a href="#!" onclick="logoutFacebook();">Login</a> |';
								} else if ($objUser->socialMediaId == 2) {
									echo '<a href="#!" onclick="signOut();">Login</a> |';
								} else if ($objUser->socialMediaId == 3) {
									echo '<a href="#!" onclick="linkedInLogout();">Login</a> |';
								} 
							} 
							else {
								echo '<a href="logout.php">Login</a> |';
							}
							require("js/facebookLogout.php");
							require("js/googleLogout.php");
							require("js/linkedinLogout.php");
						?>
						<a class="modal-trigger" href="#modalAgreement">License Agreements</a>
					</div>
				</div>
			</div>
		</div>

		 <!-- Modal Policy, Terms -->
		  <div id="modalAgreement" class="modal">
		    <div class="modal-content">
				<h5>Privacy Policy</h5>
				<p>Last updated: December 16, 2016</p>
				<p>Social Login we operates the www.login.coffee.build website (the "Service").</p>
				<p>This page informs you of our policies regarding the collection, use and disclosure of Personal Information when you use our Service.</p>
				<p>We will not use or share your information with anyone except as described in this Privacy Policy.</p>
				<p>We use your Personal Information for providing and improving the Service. By using the Service, you agree to the collection and use of information in accordance with this policy. Unless otherwise defined in this Privacy Policy, terms used in this Privacy Policy have the same meanings as in our Terms and Conditions, accessible at www.login.coffee.build</p>
				<p><strong>Information Collection And Use</strong></p>
				<p>While using our Service, we may ask you to provide us with certain personally identifiable information that can be used to contact or identify you. Personally identifiable information may include, but is not limited to, your name, phone number, postal address ("Personal Information").</p>
				<p><strong>Log Data</strong></p>
				<p>We collect information that your browser sends whenever you visit our Service ("Log Data"). This Log Data may include information such as your computer's Internet Protocol ("IP") address, browser type, browser version, the pages of our Service that you visit, the time and date of your visit, the time spent on those pages and other statistics.</p>
				<p><strong>Cookies</strong></p>
				<p>Cookies are files with small amount of data, which may include an anonymous unique identifier. Cookies are sent to your browser from a web site and stored on your computer's hard drive.</p>
				<p>We use "cookies" to collect information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our Service.</p>
				<p><strong>Service Providers</strong></p>
				<p>We may employ third party companies and individuals to facilitate our Service, to provide the Service on our behalf, to perform Service-related services or to assist us in analyzing how our Service is used.</p>
				<p>These third parties have access to your Personal Information only to perform these tasks on our behalf and are obligated not to disclose or use it for any other purpose.</p>
				<p><strong>Security</strong></p>
				<p>The security of your Personal Information is important to us, but remember that no method of transmission over the Internet, or method of electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your Personal Information, we cannot guarantee its absolute security.</p>
				<p><strong>Links To Other Sites</strong></p>
				<p>Our Service may contain links to other sites that are not operated by us. If you click on a third party link, you will be directed to that third party's site. We strongly advise you to review the Privacy Policy of every site you visit.</p>
				<p>We have no control over, and assume no responsibility for the content, privacy policies or practices of any third party sites or services.</p>
				<p><strong>Children's Privacy</strong></p>
				<p>Our Service does not address anyone under the age of 13 ("Children").</p>
				<p>We do not knowingly collect personally identifiable information from children under 13. If you are a parent or guardian and you are aware that your Children has provided us with Personal Information, please contact us. If we discover that a Children under 13 has provided us with Personal Information, we will delete such information from our servers immediately.</p>
				<p><strong>Changes To This Privacy Policy</strong></p>
				<p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.</p>
				<p>You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>
				<p><strong>Contact Us</strong></p>
				<p>If you have any questions about this Privacy Policy, please contact us.</p>
		    </div>
		    <div class="modal-footer">
		      <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Close</a>
		    </div>
		  </div>

			<!-- Modal Merge -->
			<div id="modalMerge" class="modal">
				<div class="modal-content">
					<h4>Merge accounts</h4>
					<p>This email is already attached to another account. If this is your account, you can merge it with this social media. If you do not wish to merge, simply click cancel and choose another valid email.</p>
					<p>If this isn't your account, please contact support by sending a mail to: <a href="hello@coffee.build">hello@coffee.build</a>.</p>
				</div>
				<div class="modal-footer">
					<a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat ">Cancel</a>
					<a id="modalMergeButton" href="#!" class="modal-action waves-effect waves-light btn-flat ">Merge</a>
				</div>
			</div>

		<?php require("resources/includes/inclFooter.php"); ?>
	</body>
</html>