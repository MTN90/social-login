<?php

	class mail {

		//--------------------------------------------------------------------------------------------------------------------
		// Constructor / Destructor
		//--------------------------------------------------------------------------------------------------------------------

		public function __construct(){
		}

		public function __destruct(){
		}


		//--------------------------------------------------------------------------------------------------------------------
		// Change mail on clicked link.
		//--------------------------------------------------------------------------------------------------------------------

		public function sendMail($reciever, $subject, $content){

			if (TEST_MODE == 1) {
				$_SESSION['email'] = $reciever."<br>".$subject."<br>".$content;
			} else {				
				
				// TODO: Her kunne tilføjes mere for at undgå det ryger i spam. Se kommentar herunder
				
				$headers  = "From: Social Login < hello@coffee.build >\n";
				//$headers .= "Cc: Social Login < hello@coffee.build >\n";
				$headers .= "X-Sender: Social Login < hello@coffee.build >\n";
				$headers .= 'X-Mailer: PHP/' . phpversion();
				$headers .= "X-Priority: 1\n"; // Urgent message!
				$headers .= "Return-Path: hello@coffee.build\n"; // Return path for errors
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=UTF-8\n";
				
				// sends email
				@mail($reciever, $subject, $content, $headers);
			}

			
		}

		public function createContent(){

		    // write email 
			// $emailContent = "";
			// $emailContent .= "Emne: ".$subject."\n";
		 //    $emailContent .= "Navn: ".$name."\n";
		 //    $emailContent .= "Email: ".$email."\n";
		 //    $emailContent .= "Besked: ".$message."\n";

		}



	}

?>
