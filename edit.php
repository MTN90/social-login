<?php
	require('config.php');
	require("resources/includes/inclHead.php");
	unset($_SESSION["tempUser"]);

	if (!isset($_SESSION['user'])) {
		header("Location: index.php");
	} else {
		$classUser = new user();
		$sUser = $classUser->fetchUserInformation($_SESSION["user"]["uId"]);
		$objUser = json_decode($sUser);
	}

	include("resources/includes/inclToken.php");
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Social Login | Edit</title>
		<?php require("resources/includes/inclStyles.php");?>
		<link rel="stylesheet" href="css/edit.css">
	</head>
		<body>
			<?php 
				include("resources/includes/navigation.php");

			// TODO: Slet denne del når mail virker.
				if (TEST_MODE == 1) {
					if (isset($_SESSION['email'])) {
						var_dump($_SESSION['email']);
					}
				}
			?>

			<div id="boxWrap" class="row valign-wrapper">
				<div id="box" class="valign col s12 l8 z-depth-2">
				
					<div id="boxHeader" class="row z-depth-2">
						<div class="centerContent">
							<h5>Edit</h5>
							<p>Edit your credentials</p>
							<?php 
								if($objUser->uppImagePath != NULL){
									echo '<img class="z-depth-2" src="'.$objUser->uppImagePath.'">';
								} 
								else {
									echo '<div class="z-depth-2" id="boxHeaderPP"><i class="fa fa-user" aria-hidden="true"></i></div>';
								}
							?>
						</div>
					</div>
					<div id="boxContent" class="row">
						<div class="centerContent">
							<form id="editBox">				

								<div class="input-field col s12 m6">
						          <input type="text" name="data[uFirstname]" class="validate" value="<?php echo $objUser->uFirstname;?>">
						          <label <?php if($objUser->uFirstname != ""){echo 'class="active"';}?>>Firstname *</label>
						        </div>

						        <div class="input-field col s12 m6">
						          <input type="text" name="data[uLastname]" class="validate" value="<?php echo $objUser->uLastname;?>">
						          <label <?php if($objUser->uLastname != ""){echo 'class="active"';}?>>Lastname *</label>
						        </div>

						        <div class="input-field col s12 m6">
						          <input type="email" class="validate" name="data[uEmail]" value="<?php echo $objUser->uEmail;?>">
						          <label <?php if($objUser->uEmail != ""){echo 'class="active"';}?>>Email *</label>
						        </div>

						        <div class="input-field col s12 m6">
						          <input type="tel" class="validate" name="data[uPhone]" value="<?php echo $objUser->uPhone; ?>">
						          <label <?php if($objUser->uPhone != ""){echo 'class="active"';}?>>Phone</label>
						        </div>

								<div class="input-field col s12 m6">
									<label <?php if($objUser->uBirthDate != ""){echo 'class="active"';}?>>Birthdate *</label>
							  		<input type="date" name="data[uBirthDate]" class="datepicker" value="<?php echo $objUser->uBirthDate; ?>">
								</div>

								<div class="input-field col s12 m6">
									<label class="active">Gender *</label>
									<select name="data[uGender]">
									<?php 
										$male = ""; $female = ""; 
										if($objUser->uGender == 1){$male = "selected";} else if($objUser->uGender == 2){$female = "selected";} 
									?>
										<option value="1" <?php echo $male; ?>>Male</option>
										<option value="2" <?php echo $female; ?>>Female</option>
									</select>
								</div>

						        <div class="input-field col s12">
						          <input type="text" id="autocomplete-country" class="validate autocomplete" name="data[uAddressCountry]" value="<?php echo $objUser->uAddressCountry; ?>">
						          <label <?php if($objUser->uAddressCountry != ""){echo 'class="active" ';}?> for="autocomplete-input">Country</label>
						        </div>

						        <div class="input-field col s12 m6">
						          <input type="text" class="validate" name="data[uAddress]" value="<?php echo $objUser->uAddress; ?>">
						          <label <?php if($objUser->uAddress != ""){echo 'class="active"';}?>>Address</label>
						        </div>

						        <div class="input-field col s6 m3">
						          <input type="number" class="validate" name="data[uAddressNumber]" value="<?php echo $objUser->uAddressNumber; ?>">
						          <label <?php if($objUser->uAddressNumber != ""){echo 'class="active"';}?>>Address Number</label>
						        </div>

						        <div class="input-field col s6 m3">
						          <input type="text" class="validate" name="data[uAddressMisc]" value="<?php echo htmlspecialchars($objUser->uAddressMisc); ?>">
						          <label <?php if($objUser->uAddressMisc != ""){echo 'class="active"';}?>>Level / Apartment</label>
						        </div>

						         <div class="input-field col s12 m6">
						          <input type="text" id="uAddressZip" class="validate" name="data[uAddressZip]" value="<?php echo $objUser->zcZip; ?>">
						          <label <?php if($objUser->zcZip != ""){echo 'class="active"';}?>>Zipcode</label>
						        </div>

						         <div class="input-field col s12 m6">
						          <input type="text" id="uAddressCity" class="validate" name="data[uAddressCity]" value="<?php echo $objUser->zcCity; ?>">
						          <label <?php if($objUser->zcCity != ""){echo 'class="active"';}?>>City</label>
						        </div>
								
						        <input type="hidden" id="uAddressZipCityId" name="data[uAddressZipCityId]" class="editFields" value="<?php echo $objUser->uAddressZipCityId;?>"> 

								<div class="input-field col s12 m6">
						          <input type="password" id="signUpPassword" name="data[uPassword]" class="validate tooltipped" data-position="down" data-delay="50" data-tooltip="Min. 8 Chars. (123 + abc + ABC)">
						          <label>New Password</label>
						        </div>

						        <div class="input-field col s12 m6">
						          <input type="password" id="signUpConfirm" name="data[uConfirm]" class="validate tooltipped" data-position="down" data-delay="50" data-tooltip="Retype Password">
						          <label>Confirm New Password</label>
						        </div>

						        <span>All fields with * needs to be specified.</span> 

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
						<a id="editButton" class="btn-floating btn-large waves-effect waves-light pink accent-2">
						 	<i class="fa fa-floppy-o" aria-hidden="true"></i>
						</a>
					</div>
					<div id="boxBottom">
						<div class="centerContent">
							<a href="home.php">Back</a> | 
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

		<?php require("resources/includes/inclFooter.php"); ?>			
    	
	</body>
</html>