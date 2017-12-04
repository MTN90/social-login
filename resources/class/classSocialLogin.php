<?php

	class socialLogin{
		
		private $conn;
		private $socialUserId;
		private $socialMediaId;

		//--------------------------------------------------------------------------------------------------------------------
		// DB Connect
		//--------------------------------------------------------------------------------------------------------------------

		public function __construct(){
			$classDB = new db();
			$this->conn = $classDB->conn; 
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Social Login Check
		//--------------------------------------------------------------------------------------------------------------------
		
		// $media comes from js (facebook.js, google.js), ajax call. 1 = Facebook, 2 = Google, 3 = Linkedin. Via socialLogin.php (Handler)
		public function socialLogin($token, $media){			

			$this->socialMediaId = $media;

			$verified = $this->verifyToken($token);
			if ($verified != "Error") {

				// Add Social userId to existing user session
				$objUser = json_decode($_SESSION["tempUser"]);
				$objUser->socialUserId = $this->socialUserId;

				// Update socialUserId in session
				$_SESSION["tempUser"] = json_encode($objUser);

				// Check that social Email exist in usersocial table for the user. 
				$SocialEmailStatus = $this->checkSocialEmail($this->socialUserId, $this->socialMediaId, $objUser->email);

				// Check that social Email exist in user table. 
				$emailStatus = $this->checkEmail($objUser->email);

				// Check that social Id exist in usersocial table. 
				$socialIdStatus = $this->checkSocialId();

				// Check that social imagePath exist in userProfilePicture table. 
				$imageStatus = $this->checkSocialImagePath($objUser->imagePath);
				
				// Create new User Object
				$classUser = new user();

				//Action obj to return back to user. 
				$returnObj = "";			

				// User has signedIn social before
				if($socialIdStatus == 1){

					$classUser->createUserObject($this->socialUserId);
					$returnObj = '{"redirectPage":"home.php"}';

					// if Users SocialEmail doesnt exist in usersocial table. 
					if($SocialEmailStatus == 0){
						$this->updateSocialEmail($this->socialUserId, $this->socialMediaId, $objUser->email);

						// If social email doesnt exist in usersocial AND user table
						if($emailStatus == 0){
							$returnObj = '{"redirectPage":"home.php", "afterAction":"triggerUpdateEmail(\"'.$objUser->email.'\")"}';
						} 
					}

					// if social image path doesnt exist, insert new in db. 
					if($imageStatus == 0){

						$fileName = $classUser->getSocialImage($objUser->imagePath);
						$classUser->saveSocialImage($_SESSION["user"]["uId"], $this->socialMediaId, $fileName, $objUser->imagePath);
					}

					// Update usLastLoginTimestamp field in usersocial table
					$this->updateSocialLastLogin($this->socialUserId, $this->socialMediaId, $objUser->email);

					// Update uLastLoginTimestamp field in user table
					$classUser->updateLastLogin($_SESSION["user"]["uId"]);

					// Update loginCounter in user table
					$classUser->updateLoginCount($_SESSION["user"]["uId"]);
				} 

				// User has NOT signedIn social before
				else if($socialIdStatus == 0){

					// Users socialEmail is connected to another profile
					if($emailStatus == 1){
						$uId = $classUser->getUserIdByEmail($objUser->email);

							// Add Social userId to existing user session
							$objUser = json_decode($_SESSION["tempUser"]);
							$objUser->userId = $uId;
							// Update socialUserId in session
							$_SESSION["tempUser"] = json_encode($objUser);

						$returnObj = '{"redirectPage":"merge.php"}';
					} 

					// Users socialEmail is NOT connected to another profile
					else if($emailStatus == 0){
						$returnObj = '{"redirectPage":"create.php"}';
					}
				}

				// ERROR - Multiple accounts found under same primary socialEmail
				else if($socialIdStatus == 2){
					//Send email
					$returnObj = '{"redirectPage":"error.php"}';
				}

				// Return Action Obj
				return $returnObj;
			}
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Verify Access Token
		//--------------------------------------------------------------------------------------------------------------------

		// Verify the tokens from social media by sending it in a GET to a https. (Links are defined in config.php).
		private function verifyToken($access_token){
			$media = $this->socialMediaId;

			// Media 1 = Facebook, url + the name of the id key is added to variable.
			// Media 2 = Google.
			// Media 3 = Linkedin
			if($media == 1){
				$url = FACEBOOK_ACCESS_TOKEN_URL;
				$key = FACEBOOK_SOCIALID;
			} else if($media == 2){
				$url = GOOGLE_ACCESS_TOKEN_URL;
				$key = GOOGLE_SOCIALID;
			} else if($media == 3){
				$url = LINKEDIN_ACCESS_TOKEN_URL;
				$key = LINKEDIN_SOCIALID;
			}
				// Building full url. Using social medias link + our access_token.
				$urlUserInfo = $url . $access_token;
				// The response from the url is a json object, here we call a method to get the data and save it as an object.
				$objUserInfo = $this->getJsonFromFile($urlUserInfo);

			//Send Request
			$urlUserInfo = $url . $access_token;
			$objUserInfo = $this->getJsonFromFile($urlUserInfo);

			// Validate Response
			if (isset($objUserInfo->$key)) {
				// If linked in is the media, we have to create a temporary object here. Facebook and google does it earlier due to a simpler process. Linkedin doesnt give us user information before this point.
				if ($media == 3) {
					// Creating temporary object with social information from linkedin, beeing saved in a SESSION['tempUser']
					$this->linkedinTempUserObject($objUserInfo);
				}
				// Saving the social id from the user in our class variable.
				$this->socialUserId = $objUserInfo->$key;
				return "Good";
			}
			else {
				return "Error";
			}
		}
		
		//--------------------------------------------------------------------------------------------------------------------
		// Extract result from verify response
		//--------------------------------------------------------------------------------------------------------------------
	  
	  	// Because linkedin doesnt give us any user data before we verify the access_token, then we need to create a temporary session here.
	  	// $obj is sent from verifyToken with all the information we get when verifying the linkedin access_token.
		public function linkedinTempUserObject($obj){
		    $email = $obj->emailAddress;
		    $firstname = $obj->firstName;
		    $lastname = $obj->lastName;
		    $imagePath = $obj->pictureUrls->values[0];

		    // creating json object to save in session.
			$objUser = '{"email":"'.$email.'","firstName":"'.$firstname.'","lastName":"'.$lastname.'","gender":"unknown","imagePath":"'.$imagePath.'","socialMediaId":3}';

			$_SESSION["tempUser"] = $objUser;
	  	}

		//--------------------------------------------------------------------------------------------------------------------
		// Extract result from verify response
		//--------------------------------------------------------------------------------------------------------------------
	  
	  	// 
		public function getJsonFromFile($url){	
			// Creates json object from url.
			$strUserInfo = file_get_contents($url);
			return json_decode($strUserInfo);  
	  	}

	  	
	  	// TODO: I princippet kunne understående check slåes sammen da de gør det samme blot med forskellige parametre. (Kim)
	  	// TODO: i teorien kan vi vel slå alle de metoder som select count sammen. checkEmail, checkSocialId, checkSocialEmail, checkSocialImagePath. (Daniel)
	  	//--------------------------------------------------------------------------------------------------------------------
		// Check EMail in DB
		//--------------------------------------------------------------------------------------------------------------------

	  	// Checking if email is existing in the database.
  	  	public function checkEmail($email){
	  		$qry = $this->conn->prepare("SELECT COUNT(*) as countUserEmail FROM user WHERE uEmail = :uEmail AND uDeleted = 0");
			$qry->bindParam(":uEmail", $email);
			$qry->execute();

	  		$result = $qry->fetch(PDO::FETCH_ASSOC);
	  		$count = $result['countUserEmail'];

			if($count > 1){
				$status = 2;
			} else if($count == 1){
				$status = 1;
			} else if($count == 0){
				$status = 0;
			} 
			return $status;
	  	}
	  	//--------------------------------------------------------------------------------------------------------------------
		// Check Social Email in DB
		//--------------------------------------------------------------------------------------------------------------------

	  	// Checking if social email is existing in the database.
  	  	public function checkSocialEmailExist($email, $userId = 0, $checkOtherUsers = 0){
  	  		if ($userId == 0) {
  	  			$qry = $this->conn->prepare("SELECT COUNT(*) as countUserEmail FROM usersocial WHERE usEmail = :usEmail");
  	  			$qry->bindParam(":usEmail", $email);
  	  		} else if ($checkOtherUsers == 1) {
  	  			$qry = $this->conn->prepare("SELECT COUNT(*) as countUserEmail FROM usersocial WHERE usEmail = :usEmail AND usUserId != :usUserId");
  	  			$qry->bindParam(":usEmail", $email);
  	  			$qry->bindParam(":usUserId", $userId);  	  			
  	  		} else {
  	  			$qry = $this->conn->prepare("SELECT COUNT(*) as countUserEmail FROM usersocial WHERE usEmail = :usEmail AND usUserId = :usUserId");
  	  			$qry->bindParam(":usEmail", $email);
  	  			$qry->bindParam(":usUserId", $userId);
  	  		}
	  		
			$qry->execute();

	  		$result = $qry->fetch(PDO::FETCH_ASSOC);
	  		$count = $result['countUserEmail'];

			if($count > 1){
				$status = 2;
			} else if($count == 1){
				$status = 1;
			} else if($count == 0){
				$status = 0;
			} 
			return $status;
	  	}

	  	//--------------------------------------------------------------------------------------------------------------------
		// Check Social ID in DB
		//--------------------------------------------------------------------------------------------------------------------

	  	// Checking if the socialId is existing in our database.
	  	public function checkSocialId(){

	  		$qry = $this->conn->prepare("SELECT COUNT(*) as countSocialUserId FROM usersocial WHERE usMediaId = :usMediaId AND usKey = :usKey");
	  		$qry->bindParam(":usMediaId", $this->socialMediaId);
	  		$qry->bindParam(":usKey", $this->socialUserId);
	  		$qry->execute();

	  		$result = $qry->fetch(PDO::FETCH_ASSOC);
	  		$count = $result['countSocialUserId'];

	  		// var_dump($result);

	  		if($count > 1){
				$status = 2;
			} else if($count == 1){
				$status = 1;
			} else if($count == 0){
				$status = 0;
			} 
			return $status;
	  	}

	  	//--------------------------------------------------------------------------------------------------------------------
		// check that Social Email is upto date 
		//--------------------------------------------------------------------------------------------------------------------

	  	// Checking if the social email is the same as earlier.
		public function checkSocialEmail($usKey, $usMediaId, $usEmail){

			$qry = $this->conn->prepare("SELECT COUNT(*) as countUserEmail FROM usersocial WHERE usKey = :usKey AND usMediaId = :usMediaId AND usEmail = :usEmail");
			$qry->bindParam(":usKey", $usKey);
			$qry->bindParam(":usMediaId", $usMediaId);
			$qry->bindParam(":usEmail", $usEmail);
			$qry->execute();

	  		$result = $qry->fetch(PDO::FETCH_ASSOC);
	  		$count = $result['countUserEmail'];

			if($count > 1){
				$status = 2;
			} else if($count == 1){
				$status = 1;
			} else if($count == 0){
				$status = 0;
			} 
			return $status;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Check that users profilepicture at social media is different from upp table
		//--------------------------------------------------------------------------------------------------------------------

		// Checks if the image path we get from social media is present in our database, if it isnt then its getting added. 
		public function checkSocialImagePath($socialImage){

			$qry = $this->conn->prepare("SELECT COUNT(*) as countSocialImage FROM userprofilepicture WHERE uppSocialImagePath = :socialImage");
			$qry->bindParam(":socialImage", $socialImage);
			$qry->execute();

	  		$result = $qry->fetch(PDO::FETCH_ASSOC);
	  		$count = $result['countSocialImage'];

			if($count > 1){
				$status = 2;
			} else if($count == 1){
				$status = 1;
			} else if($count == 0){
				$status = 0;
			} 
			return $status;
		}

		
		// TODO: Nedenstående 2 update (update usersocial) funktioner kunner i princippet også blive til en da det også er stort set samme kode gentaget. Ved at gøre det vil den samme kunne bruges til at opdaterer andre ting alt efter hvordan den skrives
		//--------------------------------------------------------------------------------------------------------------------
		// Update users social email in usersocial table
		//--------------------------------------------------------------------------------------------------------------------

		// If email have been changed on google, facebook or linkedin - then we update it in our database here.
		public function updateSocialEmail($usKey, $usMediaId, $usEmail){
			$qry = $this->conn->prepare("UPDATE usersocial SET usEmail = :usEmail WHERE usKey = :usKey AND usMediaId = :usMediaId");
			$qry->bindParam(":usKey", $usKey);
			$qry->bindParam(":usMediaId", $usMediaId);
			$qry->bindParam(":usEmail", $usEmail);
			$qry->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Update users last social login in usersocial table
		//--------------------------------------------------------------------------------------------------------------------

		public function updateSocialLastLogin($usKey, $usMediaId, $usEmail){
			$qry = $this->conn->prepare("UPDATE usersocial SET usLastLoginTimestamp = UNIX_TIMESTAMP() WHERE usKey = :usKey AND usMediaId = :usMediaId AND usEmail = :usEmail");
			$qry->bindParam(":usKey", $usKey);
			$qry->bindParam(":usMediaId", $usMediaId);
			$qry->bindParam(":usEmail", $usEmail);
			$qry->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Update uLastLoginTimestamp in user table by id
		//--------------------------------------------------------------------------------------------------------------------

		public function updateLastLogin($uId){
			$qry = $this->conn->prepare("UPDATE user SET uLastLoginTimestamp = UNIX_TIMESTAMP() WHERE uId = :uId");
			$qry->bindParam(":uId", $uId);
			$qry->execute();
		}
	}

?>