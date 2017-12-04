<?php

	class validation{

		private $conn;

		//--------------------------------------------------------------------------------------------------------------------
		// DB Connect
		//--------------------------------------------------------------------------------------------------------------------

		public function __construct(){
			$classDB = new db();
			$this->conn = $classDB->conn; 
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Email
		//--------------------------------------------------------------------------------------------------------------------
		
		public function validateEmail($email){
			$response = true;
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			    $response = false;
			} 
			return $response;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Name
		//--------------------------------------------------------------------------------------------------------------------

		// TODO: Dem der blot bruger preg_match (og i princippet også de andre afhængigt af hvordan det laves) kunne slåes sammen da de gør præcis det samme og så i stedet lade et ekstra parameter bestemme hvilket tjek det er.
		public function validateName($name){
			$response = true;
			if(!preg_match("/^[- '\p{L}]{2,75}+$/u", $name)){
				$response = false;
			}
			return $response;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Gender
		//--------------------------------------------------------------------------------------------------------------------

		public function validateGender($gender){
			$response = true;

			if($gender != 1 && $gender != 2) { 
				$response = false;
			}
			return $response;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Birthdate
		//--------------------------------------------------------------------------------------------------------------------
		
		public function validateBirthDate($date){
			$response = true;

			// Reformating date
			$date = date("Y-m-d", strtotime($date));
			
			if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)){
		        $response = false;
		    }
			return $response;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Phonenumber
		//--------------------------------------------------------------------------------------------------------------------

		public function validatePhone($phone){
			$response = true;
			if(!preg_match("/^[0-9]{8,12}$/", $phone)) {
				$response = false;
			}
			return $response;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Password
		//--------------------------------------------------------------------------------------------------------------------

		public function validatePassword($password){
			$response = true;
			if(!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,100}$/',$password)) {
				$response = false;
			}
			return $response;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Address
		//--------------------------------------------------------------------------------------------------------------------

		public function validateAddress($address){
			$response = true;
			if(!preg_match("/^[- '\p{L}]{2,150}+$/u", $address)){
				$response = false;
			}
			return $response;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Number (streetnumber)
		//-------------------------------------------------------------------------------------------------------------------- 

		public function validateNumber($addressNumber){
			$response = true;
			if(!preg_match("/^[0-9]{1,4}$/", $addressNumber)) {
				$response = false;
			}
			return $response;
		} 

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Misc (level / apartment)
		//-------------------------------------------------------------------------------------------------------------------- 

		public function validateMisc($addressMisc){
			$response = true;
			if(!preg_match('/^(?=.*\d)[0-9A-Za-z\-_. \/]{1,30}$/',$addressMisc)) {
				$response = false;
			}
			return $response;
		}

		//--------------------------------------------------------------------------------------------------------------------
		// Validate ZipCity ID (City & Postalnumber)
		//--------------------------------------------------------------------------------------------------------------------

		public function validateZip($zcId){
			$response = true;

			$qry = $this->conn->prepare("SELECT COUNT(*) as countZipCity FROM zipcity WHERE zcId = :zcId");
			$qry->bindParam(":zcId", $zcId);
			$qry->execute();

	  		$result = $qry->fetch(PDO::FETCH_ASSOC);
	  		$count = $result['countZipCity'];
			if($count == 0){
				$response = false;
			}
			return $response;
		} 

		//--------------------------------------------------------------------------------------------------------------------
		// Validate Country
		//--------------------------------------------------------------------------------------------------------------------

		public function validateCountry($country){
			$response = true;
			if(!preg_match("/^[a-zA-Z' -]{2,100}+$/",$country)) { 
				$response = false;
			}
			return $response;
		} 

	}

?>