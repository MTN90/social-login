<?php

    // include configurationfile and class'
    require($_SERVER['DOCUMENT_ROOT'].'/login/config.php');
    require($_SERVER['DOCUMENT_ROOT'].'/login/resources/includes/inclHead.php');

    if (isset($_GET['code'])) {
        $code = $_GET['code']; 

        // The url where we get the access_token. We use a get to store the authorization code in a variable, and then send it to a new url to get the access_token.
        $url = "https://www.linkedin.com/oauth/v2/accessToken?grant_type=authorization_code&code=".$code."&redirect_uri=".LINKEDIN_REDIRECT_URI."&client_id=".LINKEDIN_API."&client_secret=".LINKEDIN_SECRET;

        $classSocialLogin = new socialLogin();

        $objAccessToken = $classSocialLogin->getJsonFromFile($url); 

        $accessToken = $objAccessToken->access_token;

        // Here we call or socialLogin method, and use it to create tempUser session and check if user is in our db or not.
        $objResponse = json_decode($classSocialLogin->socialLogin($accessToken, 3));

        // Redirect according to the response from socialLogin method.
        // header('Location: '.$objResponse->redirectPage);

        header('Location: ../../'.$objResponse->redirectPage);

        exit;
    } else if(isset($_GET['error'])) {
        header('Location: ../../index.php');
        exit;
    }

?>