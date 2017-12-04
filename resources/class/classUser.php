<?php

	class user{
			
		private $conn;
		private $root;

		//--------------------------------------------------------------------------------------------------------------------
		// DB Connect
		//--------------------------------------------------------------------------------------------------------------------

		public function __construct(){
			$classDB = new db();
			$this->conn = $classDB->conn; 

			$this->root = '../../';
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Login
		//--------------------------------------------------------------------------------------------------------------------

		public function login($email, $password, $autoLogin = 0, $notHashed = 1, $log = 1){

			if ($notHashed == 1) {

				// Create saltyHash from retrieved salt
				$salt = $this->getSalt($email);
				$hash = $this->hash($password);
				$saltyHash = $this->hash($hash.$salt);

			} else {

				// Create saltyHash from retrieved salt
				$saltyHash = $password;
			}

			// check for match in db
			$qry = $this->conn->prepare("	SELECT uId, uFirstname, uLastname, uEmail, uppImagePath 
											FROM user 
											LEFT JOIN userprofilepicture
											ON uId = uppUserId AND uppActive = 1
											WHERE uEmail = :email AND uPassword = :saltyHash
										");
			$qry->bindParam(':email', $email);
			$qry->bindParam(':saltyHash', $saltyHash);
			$qry->execute();

			if($qry->rowCount() == 1){

				// Fetch user from result
				$uUser = $qry->fetch(PDO::FETCH_ASSOC);

				// Save ass. array in user session
				$_SESSION["user"] = $uUser;
				$_SESSION["user"]["usMediaId"] = "0";

				if($log == 1){
					// Log attempt logInLog(id, success(1)/fail(0));
					$this->logInLog($uUser['uId'], '1');
				}
				
				// Update Last Login in user table
				$this->updateLastLogin($uUser['uId']);

				// Update loginCounter in user table
				$this->updateLoginCount($uUser['uId']);

				// Set cookie for autologin
				if ($autoLogin == 1) {
					$cookieName = 'autoLogin';
					$cookieValue = '{"email":"'.$email.'","password":"'.$saltyHash.'"}';
					setcookie($cookieName, $cookieValue, time() + (86400 * 30), "/", "", false, true);
				}

				// Check that users account has been validated
				$verifyCode = $this->checkVerifyCode($uUser['uId']);

				if ($verifyCode == "0") {	

					// Users account has been validated				
					return '{"status":1,"message":"Login success","userid":"'.$uUser['uId'].'"}';
				} else {

					// Users account is NOT validated
					return '{"status":3,"message":"You need to Verify","userid":"'.$uUser['uId'].'"}';
				}

			} else {

				// Login fail - Wrong Email or password
				$this->logInLog();
				return '{"status":0,"message":"Wrong Email or Password"}';
			}
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Check Verify Code
		//--------------------------------------------------------------------------------------------------------------------

		public function checkVerifyCode($uId){
			$qry = $this->conn->prepare("SELECT uVerifyCode FROM user WHERE uId = :uId LIMIT 1");
			$qry->bindParam(":uId", $uId);
			$qry->execute();

			$result = $qry->fetch(PDO::FETCH_ASSOC);
			return $result['uVerifyCode'];
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Verify Code
		//--------------------------------------------------------------------------------------------------------------------

		// TODO: Slå verifyMail og verifyUserMail sammen til 1 funktion.
		public function verifyMail($uVerifyCode){
			$qry = $this->conn->prepare("UPDATE user SET uVerifyCode = 0 WHERE uId = :uId AND uVerifyCode = :uVerifyCode");
			$qry->bindParam(":uId", $_SESSION['user']['uId']);
			$qry->bindParam(":uVerifyCode", $uVerifyCode);
			$qry->execute();

			$count = $qry->rowCount();
			return $count;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Verify User Mail
		//--------------------------------------------------------------------------------------------------------------------

		public function verifyUserMail($email){
			$qry = $this->conn->prepare("UPDATE user SET uVerifyCode = 0 WHERE uEmail = :uEmail");
			$qry->bindParam(":uEmail", $email);
			$qry->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Create User
		//--------------------------------------------------------------------------------------------------------------------

		public function create($data){

			// Fetch random created salt
			$salt = $this->newSalt();

			// Generate hash from password
			$hash = $this->hash($data["uPassword"]);

			// Generate salty hash from hashed password and salt
			$saltyHash = $this->hash($hash.$salt);

			//Generate random account verificationcode
			$verifyCode = $this->verifyCode();

			//Insert user in user table
			$createUser = $this->conn->prepare("INSERT INTO user(uEmail, uPassword, uSalt, uFirstname, uLastname, uGender, uBirthDate, uCreatedTimestamp, uVerifyCode, uBanned, uDeleted) VALUES (:email, :password, :salt, :firstname, :lastname, :gender, :birthDate, UNIX_TIMESTAMP(), :verifyCode, '0', '0')"); 
			$createUser->bindParam(':email', $data["uEmail"]); 
			$createUser->bindParam(':password', $saltyHash); 
			$createUser->bindParam(':salt', $salt); 
			$createUser->bindParam(':firstname', $data["uFirstname"]);
			$createUser->bindParam(':lastname', $data["uLastname"]);
			$createUser->bindParam(':gender', $data["uGender"]);
			$createUser->bindParam(':birthDate', $data["uBirthDate"]);
			$createUser->bindParam(':verifyCode', $verifyCode); 
			$createUser->execute();

			// Run if Creation made by Social Media
			if($data["uppImagePath"] != ""){

				// Get new created users id
				$userId = $this->conn->lastInsertId();

				// Fetch profile image from social media and save local
				$fileName = $this->getSocialImage($data["uppImagePath"]);

				// Save social profile image in userprofilepicture table
				$this->saveSocialImage($userId, $data["uppSocialMediaId"], $fileName, $data["uppImagePath"], $data["uppActive"]);

				// Save users socialkey in usersocial table
				$this->createUserSocial($userId);
			}
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Create UserSocial
		//--------------------------------------------------------------------------------------------------------------------

		public function createUserSocial($id){

			// Save information for social signedIn user in usersocial table
			$objUser = json_decode($_SESSION['tempUser']);

			$qry = $this->conn->prepare("INSERT INTO usersocial(usUserId, usMediaId, usKey, usEmail, usLastLoginTimestamp, usCreatedTimestamp) VALUES (:usUserId, :usMediaId, :usKey, :usEmail, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
			$qry->bindParam(":usUserId", $id);
			$qry->bindParam(":usMediaId", $objUser->socialMediaId);
			$qry->bindParam(":usKey", $objUser->socialUserId);
			$qry->bindParam(":usEmail", $objUser->email);
			$qry->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Create User Object
		//--------------------------------------------------------------------------------------------------------------------

		public function createUserObject($socialUserId){

			// fetch userinformation based on a users id
			$qry = $this->conn->prepare("	SELECT uId, uFirstname, uLastname, uEmail, uppImagePath, usMediaId
											FROM user
											LEFT JOIN userprofilepicture
											ON uId = uppUserId AND uppActive = 1
											INNER JOIN usersocial
											ON usUserId = uId
											WHERE usKey = :socialId
										");
			$qry->bindParam(':socialId', $socialUserId);
			$qry->execute();

			//create session user containing all basic user informations 
			if($qry->rowCount() == 1){
				$uUser = $qry->fetch(PDO::FETCH_ASSOC);
				$_SESSION["user"] = $uUser;
				$this->logInLog($uUser["uId"], 1, $uUser["usMediaId"]);
			}
			else {
				// Error
			}
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Insert External profile image
		//--------------------------------------------------------------------------------------------------------------------

		public function saveSocialImage($userId, $socialMediaId, $fileName, $socialImage, $active = 0){

			// if a newly fetched profilepicture is set to active, set current profilepicture status to 0 (not selected)
			if($active == 1){
				$this->resetProfileImage($userId);
			}

			// Insert profilepicure in profilepicture table
			$createUser = $this->conn->prepare("INSERT INTO userprofilepicture(uppUserId, uppSocialMediaId, uppImagePath, uppSocialImagePath, uppActive) VALUES (:userId, :socialMediaId, :fileName, :socialImage, :active)");
			$createUser->bindParam(':userId', $userId); 
			$createUser->bindParam(':socialMediaId', $socialMediaId); 
			$createUser->bindParam(':fileName', $fileName); 
			$createUser->bindParam(':socialImage', $socialImage);
			$createUser->bindParam(':active', $active);
			$createUser->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Fetch and save external profile image
		//--------------------------------------------------------------------------------------------------------------------

		public function getSocialImage($url){

			// Fetch image by path to online social profile picture
			$data = file_get_contents($url);

			// Create path and save local
			$path = "userFiles/images/social";
			$fileName = time().uniqid().".jpg";

			// Check that directory exist - if NOT, create it.
			if (!is_dir($this->root.$path)){
				mkdir($this->root.$path, 0777, true);
			}
			    
			//Insert file in directory
			$file = fopen($this->root.$path."/".$fileName, "w+");
			fputs($file, $data);
			fclose($file);

			// Return pathname for insertion in db
			return $path."/".$fileName;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Reset users current profile picture (uppActive = 0)
		//--------------------------------------------------------------------------------------------------------------------

		public function resetProfileImage($userId){
			$createUser = $this->conn->prepare("UPDATE userprofilepicture SET uppActive = 0 WHERE uppUserId = :userId");
			$createUser->bindParam(':userId', $userId); 
			$createUser->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// GetSalt by Email
		//--------------------------------------------------------------------------------------------------------------------

		public function getSalt($email){

			// fetch user autogenerated salt by his email address
			$getSalt = $this->conn->prepare("SELECT uSalt FROM user WHERE uEmail = :email");
			$getSalt->bindParam(':email', $email);
			$getSalt->execute();
			$salt = $getSalt->fetch(PDO::FETCH_ASSOC);
			return $salt["uSalt"]; 
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Generate Salt
		//--------------------------------------------------------------------------------------------------------------------

		public function newSalt(){

			// Generates 64 character long hash based on a random generated string. 
			return hash('sha256', uniqid(mt_rand(), false)); 
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Hash a string
		//--------------------------------------------------------------------------------------------------------------------

		public function hash($str){

			// Generates a 64 character long hash based on a given string. 
			return hash('sha256', $str);
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Generate Verification Code
		//--------------------------------------------------------------------------------------------------------------------

		public function verifyCode(){

			// Generates a 6 character long random generated string
			return substr(md5(uniqid(rand(), true)), 0, 6);
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Update verifyCode
		//--------------------------------------------------------------------------------------------------------------------

		public function updateVerifyCode($uId){

			$verifyCode = $this->verifyCode();

			$qry = $this->conn->prepare("UPDATE user SET uVerifyCode = :uVerifyCode WHERE uId = :uId");
			$qry->bindParam(":uVerifyCode", $verifyCode);
			$qry->bindParam(":uId", $uId);
			$qry->execute();

			return $verifyCode;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Get Clients IP
		//--------------------------------------------------------------------------------------------------------------------

		public function getClientIp(){

			// Fetch user ip address with plenty of fallbacks. 
		    $ipaddress = '';
		    if (getenv('HTTP_CLIENT_IP'))
		        $ipaddress = getenv('HTTP_CLIENT_IP');
		    else if(getenv('HTTP_X_FORWARDED_FOR'))
		        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		    else if(getenv('HTTP_X_FORWARDED'))
		        $ipaddress = getenv('HTTP_X_FORWARDED');
		    else if(getenv('HTTP_FORWARDED_FOR'))
		        $ipaddress = getenv('HTTP_FORWARDED_FOR');
		    else if(getenv('HTTP_FORWARDED'))
		       $ipaddress = getenv('HTTP_FORWARDED');
		    else if(getenv('REMOTE_ADDR'))
		        $ipaddress = getenv('REMOTE_ADDR');
		    else
		        $ipaddress = 'UNKNOWN';
		    return $ipaddress;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Login Log
		//--------------------------------------------------------------------------------------------------------------------

		public function logInLog($userID = 0, $success = 0, $type = 0){

			/* Inserts a row in db everytime a users signIn to his account or his attempt to do it. Status ($success) 
			is depending on a login has been successfully or not. Type ($type) depends on what media that has been used */
			$ip = $this->getClientIp();
			$query = $this->conn->prepare("INSERT INTO userlog(ulUserId, ulType, ulSuccess, ulTimestamp, ulIp) VALUES (:userId, :type, :success, UNIX_TIMESTAMP(), :ip)"); 
			$query->bindParam(':userId', $userID);
			$query->bindParam(':type', $type);
			$query->bindParam(':success', $success);
			$query->bindParam(':ip', $ip);        
			$query->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Check login attempts
		//--------------------------------------------------------------------------------------------------------------------

		public function logInAttempts($minutes = 5){

			// checks how many logins (with status 0 - signin fail) user has the last 5 minutes.

			// Users IP 
			$ip = $this->getClientIp();

			// Current timestamp
			$timestamp = time();

			// Time now
			$timeNow = $timestamp;

			// Time for 5 minutes ago (5 or other value in $minutes parapeter)
			$timeBefore = $timestamp -= ($minutes * 60);

			$queryAttempts = $this->conn->prepare("SELECT COUNT(*) as countLogInAttempts FROM userlog WHERE ulIp = :ip AND ulSuccess = 0 AND ulTimestamp BETWEEN :timeBefore AND :timeNow"); 
			$queryAttempts->bindParam(':ip', $ip);
			$queryAttempts->bindParam(':timeNow', $timeNow);
			$queryAttempts->bindParam(':timeBefore', $timeBefore);
			$queryAttempts->execute();

			// Return result
			$result = $queryAttempts->fetch(PDO::FETCH_ASSOC);
	  		return $result['countLogInAttempts'];
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Update email in user table
		//--------------------------------------------------------------------------------------------------------------------

		public function updateEmail($usKey, $usMediaId, $usEmail){
			$qry = $this->conn->prepare("UPDATE user SET uEmail = :uEmail WHERE uId = (SELECT usUserId FROM usersocial WHERE usKey = :usKey AND usMediaId = :usMediaId)");
			$qry->bindParam(":usKey", $usKey);
			$qry->bindParam(":usMediaId", $usMediaId);
			$qry->bindParam(":uEmail", $usEmail);
			$qry->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Get a users ID by the social key (socail id)
		//--------------------------------------------------------------------------------------------------------------------

		public function getUserIdByKey($usKey, $usMediaId, $usEmail){
			$qry = $this->conn->prepare("SELECT usUserId FROM usersocial WHERE usKey = :usKey AND usMediaId = :usMediaId AND usEmail = :usEmail");
			$qry->bindParam(":usKey", $usKey);
			$qry->bindParam(":usMediaId", $usMediaId);
			$qry->bindParam(":usEmail", $usEmail);
			$qry->execute();

			$user = $qry->fetch(PDO::FETCH_ASSOC);
			return $user["usUserId"];
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Get a users ID by email.
		//--------------------------------------------------------------------------------------------------------------------

		public function getUserIdByEmail($uEmail){
			$qry = $this->conn->prepare("SELECT uId FROM user WHERE uEmail = :uEmail");
			$qry->bindParam(":uEmail", $uEmail);
			$qry->execute();

			$user = $qry->fetch(PDO::FETCH_ASSOC);
			return $user["uId"];

		}

		//--------------------------------------------------------------------------------------------------------------------
		// Update uLastLoginTimestamp in user table by id
		//--------------------------------------------------------------------------------------------------------------------

		public function updateLastLogin($uId){
			$qry = $this->conn->prepare("UPDATE user SET uLastLoginTimestamp = UNIX_TIMESTAMP() WHERE uId = :uId");
			$qry->bindParam(":uId", $uId);
			$qry->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Update loginCount in user table by id
		//--------------------------------------------------------------------------------------------------------------------

		public function updateLoginCount($uId){
			$qry = $this->conn->prepare("UPDATE user SET uLoginCount = (uLoginCount + 1) WHERE uId = :uId");
			$qry->bindParam(":uId", $uId);
			$qry->execute();
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Get userinformation by id
		//--------------------------------------------------------------------------------------------------------------------

		public function fetchUserInformation($uId){

			// fetch a users information and returns it as a json object
			$qry = $this->conn->prepare("	SELECT uId, uFirstname, uLastname, uEmail, uPhone, uAddress, uAddressNumber, uAddressMisc, uAddressZipCityId, zcCity, zcZip, uAddressCountry, uGender, uBirthDate, uppImagePath
											FROM user
											LEFT JOIN userprofilepicture
											ON uId = uppUserId AND uppActive = 1
											LEFT JOIN zipcity
											ON uAddressZipCityId = zcId 
											WHERE uId = :uId
										");

			$qry->bindParam(':uId', $uId);
			$qry->execute();

			if($qry->rowCount() == 1){
				$uUser = $qry->fetch(PDO::FETCH_ASSOC);
				return json_encode($uUser);
			}
			else {
				// Error
			}
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Check Email owner in user table
		//--------------------------------------------------------------------------------------------------------------------
	  	
  	  	public function checkEmailOwner($uId, $uEmail){

  	  		// Check that a email belongs to a specific user (id)
	  		$qry = $this->conn->prepare("SELECT COUNT(*) as countUserEmail FROM user WHERE uId = :uId AND uEmail = :uEmail AND uDeleted = 0");
			$qry->bindParam(":uId", $uId);
			$qry->bindParam(":uEmail", $uEmail);
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
		// Edit User
		//--------------------------------------------------------------------------------------------------------------------

		public function edit($uId, $data){

			// New password, and password confirmation have to match
			if($data["uPassword"] == $data["uConfirm"]){

				// New password may not by empty (dont update if none given)
				if($data["uPassword"] != ""){

					//Update users password seperately if a new password is wanted
					$this->updatePassword($uId, $data["uPassword"], $_SESSION["user"]["uEmail"]);
				}
			}

			// Insert updated informations in user table
			$editUser = $this->conn->prepare("UPDATE user SET uPhone = :uPhone, uFirstname = :uFirstname, uLastname = :uLastname, uAddress = :uAddress, uAddressNumber = :uAddressNumber, uAddressMisc = :uAddressMisc, uAddressZipCityId = :uAddressZipCityId, uAddressCountry = :uAddressCountry, uGender = :uGender, uBirthDate = :uBirthDate WHERE uId = :uId"); 
			$editUser->bindParam(':uId', $uId);
			$editUser->bindParam(':uPhone', $data["uPhone"]); 
			$editUser->bindParam(':uFirstname', $data["uFirstname"]);
			$editUser->bindParam(':uLastname', $data["uLastname"]);
			$editUser->bindParam(':uAddress', $data["uAddress"]); 
			$editUser->bindParam(':uAddressNumber', $data["uAddressNumber"]);
			$editUser->bindParam(':uAddressMisc', $data["uAddressMisc"]);
			$editUser->bindParam(':uAddressZipCityId', $data["uAddressZipCityId"]);
			$editUser->bindParam(':uAddressCountry', $data["uAddressCountry"]);
			$editUser->bindParam(':uGender', $data["uGender"]);
			$editUser->bindParam(':uBirthDate', $data["uBirthDate"]);
			$editUser->execute();

			// Update values in current user session
			// $_SESSION["user"]["uEmail"] = $data["uEmail"];
			$_SESSION["user"]["uFirstname"] = $data["uFirstname"];
			$_SESSION["user"]["uLastname"] = $data["uLastname"];

			// Update profile image (not in this version)
			//$_SESSION["user"]["uppImagePath"] = $data["uppImagePath"];
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Create mail change Row in db.
		//--------------------------------------------------------------------------------------------------------------------

		public function mailChangeAddRow($newMail){

			// Generate new hash for email validation code
			$hashedCode = $this->hash(uniqid());

			// Insert mail in mailchange table for further validation
			$qry = $this->conn->prepare("INSERT INTO mailchange(mcUserId, mcOldMail, mcNewMail, mcHashedCode, mcDone, mcCreatedTimestamp) VALUES (:mcUserId, :mcOldMail, :mcNewMail, :mcHashedCode, 0, UNIX_TIMESTAMP())");
			$qry->bindParam(":mcUserId", $_SESSION['user']['uId']);
			$qry->bindParam(":mcOldMail", $_SESSION['user']['uEmail']);
			$qry->bindParam(":mcNewMail", $newMail);
			$qry->bindParam(":mcHashedCode", $hashedCode);
			$qry->execute();

			// Return hashcode for use in email method
			return $hashedCode;
		}


		//--------------------------------------------------------------------------------------------------------------------
		// Change mail on clicked link.
		//--------------------------------------------------------------------------------------------------------------------

		public function mailChangeDetails($hashedCode){

			// return new email informations from mailchange table by hash
			$qry = $this->conn->prepare("SELECT COUNT(*) as status, mcUserId, mcOldMail, mcNewMail FROM mailchange WHERE mcHashedCode = :mcHashedCode AND mcDone = 0 LIMIT 1");
			$qry->bindParam(":mcHashedCode", $hashedCode);
			$qry->execute();

			$result = $qry->fetch(PDO::FETCH_ASSOC);
			$status = $result['status'];

			if($status > 0){
				// Create changeEmail session
				$_SESSION['changeMail'] = $result;

				// Insert hash key
				$_SESSION['changeMail']['hash'] = $hashedCode;

				//return information results
				return $result;

			} else {
				return 0;
			}

		}


		//--------------------------------------------------------------------------------------------------------------------
		// Change mail on clicked link.
		//--------------------------------------------------------------------------------------------------------------------

		public function mailChangeFinal(){

			// Fetch informations from session
			$result = $_SESSION['changeMail'];

			// Update primary email in usertable after a successfully validation
			$qry = $this->conn->prepare("UPDATE user SET uEmail = :uNewEmail WHERE uId = :uId AND uEmail = :uOldEmail");
			$qry->bindParam(":uNewEmail", $result['mcNewMail']);
			$qry->bindParam(":uId", $result['mcUserId']);
			$qry->bindParam(":uOldEmail", $result['mcOldMail']);
			$qry->execute();

			// Flag validation as done in mailchange table
			$qry = $this->conn->prepare("UPDATE mailchange SET mcDone = 1 WHERE mcHashedCode = :mcHashedCode");
			$qry->bindParam(":mcHashedCode", $result['hash']);
			$qry->execute();

			// Remove other mail change requests
			$qry = $this->conn->prepare("DELETE FROM mailchange WHERE mcUserId = :uId AND mcHashedCode != :mcHashedCode AND mcDone != 1");
			$qry->bindParam(":uId", $result['mcUserId']);
			$qry->bindParam(":mcHashedCode", $result['hash']);
			$qry->execute();

			// remove sessions
			unset($_SESSION["changeMail"]);
			unset($_SESSION['email']);

			return "Done";
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Cancel mail change (remove row from table: mailchange)
		//--------------------------------------------------------------------------------------------------------------------

		public function mailChangeCancel(){

			// Fetch informations from session
			$result = $_SESSION['changeMail'];

			// Cancel validation as done in mailchange table
			$qry = $this->conn->prepare("DELETE FROM mailchange WHERE mcUserId = :uId AND mcDone != 1");
			$qry->bindParam(":uId", $result['mcUserId']);
			$qry->execute();

			// remove sessions
			unset($_SESSION["changeMail"]);
			unset($_SESSION['email']);

			return 1;
		}


		//--------------------------------------------------------------------------------------------------------------------
		// Update password in user table
		//--------------------------------------------------------------------------------------------------------------------

		public function updatePassword($uId, $password, $email){

			// create saltyHash from retrieved salt
			$salt = $this->getSalt($email);
			$hash = $this->hash($password);
			$saltyHash = $this->hash($hash.$salt);

			//Update password in user table
			$updatePassword = $this->conn->prepare("UPDATE user SET uPassword = :saltyHash WHERE uId = :uId"); 
			$updatePassword->bindParam(':uId', $uId);
			$updatePassword->bindParam(':saltyHash', $saltyHash);
			$updatePassword->execute();
		}
		
		//--------------------------------------------------------------------------------------------------------------------
		// Fetch User Image
		//--------------------------------------------------------------------------------------------------------------------

		public function fetchUserImage($uId){

			// Fetch imagePath from userprofilepicture table
			$qry = $this->conn->prepare("	SELECT uppImagePath
											FROM userprofilepicture
											WHERE uppUserId = :uppUserId
											AND uppActive = 1
										");
			$qry->bindParam(':uppUserId', $uId);
			$qry->execute();

			// fetch imagepath if any found
			$imagePath = $qry->fetch(PDO::FETCH_ASSOC);
			if (!$imagePath) {

				// If no imagepath found in table return "no image found" picture
				//return "https://upload.wikimedia.org/wikipedia/commons/a/ac/No_image_available.svg";
				return "none";
			}
			else {
				// If image found return image path
				return $imagePath['uppImagePath'];
			}
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Get City from Zip
		//--------------------------------------------------------------------------------------------------------------------

		public function getCityFromZip($zcZip){
			
			// Fetch id and city by a given zip
			$qry = $this->conn->prepare("SELECT zcId, zcCity FROM zipcity WHERE zcZip = :zcZip");
			$qry->bindParam(':zcZip', $zcZip);
			$qry->execute();

			if($qry->rowCount() == 1){

				//If a city was found return object with results
				$zipCity = $qry->fetch(PDO::FETCH_ASSOC);
				return '{"status":"1","id":"'.$zipCity['zcId'].'","name":"'.$zipCity['zcCity'].'"}';
			}
			else {
				// If no city was found with given zip, return status 0 (error)
				return '{"status":"0"}';
			}
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Get Zip from City
		//--------------------------------------------------------------------------------------------------------------------

		public function getZipFromCity($zcCity){
			
			// Fetch id and zip by a given city
			$qry = $this->conn->prepare("SELECT zcId, zcZip FROM zipcity WHERE zcCity = :zcCity");
			$qry->bindParam(':zcCity', $zcCity);
			$qry->execute();

			if($qry->rowCount() == 1){

				//If a zip was found return object with results
				$zipCity = $qry->fetch(PDO::FETCH_ASSOC);
				return '{"status":"1","id":"'.$zipCity['zcId'].'","name":"'.$zipCity['zcZip'].'"}';
			}
			else {

				// If no zip was found with given zip, return status 0 (error)
				return '{"status":"0"}';
			}
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Get Logs for the user logged in.
		//--------------------------------------------------------------------------------------------------------------------

		public function getUserLog() {
			// Fetch 30 latest logs from user
			$qry = $this->conn->prepare("	SELECT ulTimestamp, uFirstname, uLastname, uppImagePath
											FROM userlog
											INNER JOIN user
											ON uId = ulUserId
											LEFT JOIN userprofilepicture
											ON uppUserId = ulUserId
											AND uppActive = 1
											WHERE ulSuccess = 1
											AND uBanned = 0
											AND uVerifyCode = '0'
											AND uDeleted = 0
											ORDER BY ulTimestamp DESC
											LIMIT 30
										");
			$qry->execute();
			$userLog = $qry->fetchAll(PDO::FETCH_ASSOC);
			return $userLog;

		}
	}
?>