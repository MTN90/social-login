<? 
	if(!isset($_SESSION["user"])){
		header("Location: ../../index.php");
	}
?>

<head>	
    <meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="<?php echo GOOGLE_API; ?>">

    <!-- Navigation Sidemenu -->
	<link rel="stylesheet" href="css/navigation.css">

</head>
	<!-- Top navigation -->

	<div class="navbar-fixed hide-on-large-only">
		<nav class="blue">
			<div class="nav-wrapper">
				<ul id="nav-mobile" >
					<li class="right"><a href="#" data-activates="slide-out" class="button-collapse"><i class="fa fa-bars"></i></a></li>
					<li class="left "><a href="home.php">Social Login</a></li>
				</ul>
			</div>
		</nav>
	</div>

	<!-- Side navigation -->
	<ul id="slide-out" class="side-nav fixed">
    	<li>
    		<div class="userView">
				<div class="background">
				<!-- <img src="img/sideNav.jpg"> -->
				</div>
				<a href="edit.php">
				<?php
					if($_SESSION['user']['uppImagePath'] != ""){
						echo '<img class="circle" src="'.$_SESSION['user']['uppImagePath'].'">';
					} 
					else {
						echo '<div class="ppCircle circle z-depth-2"><i class="ppUser fa fa-user"></i></div>'; 
					}
				?>
				</a>
				<a href="edit.php">
					<span class="white-text name truncate">
						<?php echo $_SESSION['user']['uFirstname']." ".$_SESSION['user']['uLastname']; ?>
					</span>
				</a>
				<a href="edit.php">
					<span class="white-text email truncate">
						<?php echo $_SESSION['user']['uEmail']; ?>
					</span>
				</a>
    		</div>
    	</li>
	    <li><a href="home.php" class="waves-effect"><i class="fa fa-home"></i>Home</a></li>
	    <li><div class="divider"></div></li>
	    <li><a href="edit.php" class="waves-effect"><i class="fa fa-pencil"></i>Edit Profile</a></li>
    
		<?php if (isset($_SESSION['user'])) {
				$objSessionUser = $_SESSION['user'];
				//var_dump($objSessionUser);
				// If user object contains usMediaId, then page will show logout for the right media. Else it will show logout for our own login.
				if (isset($objSessionUser['usMediaId'])) {
					if ($objSessionUser['usMediaId'] == 1) {
						echo '<li><a href="#!" onclick="logoutFacebook();" class="waves-effect"><i class="fa fa-sign-out"></i>Sign Out</a></li>';
					} else if ($objSessionUser['usMediaId'] == 2) {
						echo '<li><a href="#!" onclick="signOut();" class="waves-effect"><i class="fa fa-sign-out"></i>Sign Out</a></li>';
					}else if ($objSessionUser['usMediaId'] == 3) {
						echo '<li><a href="#!" onclick="linkedInLogout();" class="waves-effect"><i class="fa fa-sign-out"></i>Sign Out</a></li>';
					}else if ($objSessionUser['usMediaId'] == 0) {		
						echo '<li><a href="logout.php" class="waves-effect"><i class="fa fa-sign-out"></i>Sign Out</a></li>';
					}
				}
			} 
		?>

	</ul>

	<!-- 	
	<div class="fixed-action-btn hide-on-large-only">
		<a href="#" data-activates="slide-out" class="button-collapse btn-floating btn-large waves-effect waves-light pink accent-2">
			<i class="fa fa-bars"></i>
		</a>
	</div>
	--> 

	<!--
	if (isset($_COOKIE['autoLogin'])) {
		echo $_COOKIE['autoLogin'];
	} else {
		echo "Cookie not set!";
	} 
	-->

<?php
	require("js/facebookLogout.php");
	require("js/googleLogout.php");
	require("js/linkedinLogout.php");
?>