<?php
	
	// include config and class'
	require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
	require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');

	$event = $_POST['event'];

	if ($event == "updateLog") {		
		$classUser = new user();

		$objUserLog = json_encode($classUser->getUserLog());

		$objUserLogTime = '{"Time":'.time().',"userLog":'.$objUserLog.'}';
		
		echo $objUserLogTime;
	}

?>