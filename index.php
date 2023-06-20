<?php
require_once 'config.php';
require_once 'User.class.php';

$authUrl = $output = ''; 
if(isset($_SESSION['oauth_status']) && $_SESSION['oauth_status'] == 'verified' && !empty($_SESSION['userData'])){
	$userData = $_SESSION['userData'];
	if(!empty($userData)){
		$output  = '<h2>LinkedIn Profile Details</h2>';
		$output .= '<div class="ac-data">';
		$output .= '<img src="'.$userData['picture'].'"/>';
        $output .= '<p><b>LinkedIn ID:</b> '.$userData['oauth_uid'].'</p>';
        $output .= '<p><b>Name:</b> '.$userData['first_name'].' '.$userData['last_name'].'</p>';
        $output .= '<p><b>Email:</b> '.$userData['email'].'</p>';
        $output .= '<p><b>Logout from</b> <a href="logout.php">LinkedIn</a></p>';
		$output .= '</div>';
		print_r($userData); exit;
	}
}elseif((isset($_GET["oauth_init"]) && $_GET["oauth_init"] == 1) || (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])) || (isset($_GET['code']) && isset($_GET['state']))){
	$client = new oauth_client_class;
	
	$client->client_id = LIN_CLIENT_ID;
	$client->client_secret = LIN_CLIENT_SECRET;
	$client->redirect_uri = LIN_REDIRECT_URL;
	$client->scope = LIN_SCOPE;
	$client->debug = 1;
	$client->debug_http = 1;
	$application_line = __LINE__;
	
	if(strlen($client->client_id) == 0 || strlen($client->client_secret) == 0){
		echo "Problem";
		die();
	}
	
	// If authentication returns success
	if($success = $client->Initialize()){
		if(($success = $client->Process())){
			if(strlen($client->authorization_error)){
				$client->error = $client->authorization_error;
				$success = false;
			}elseif(strlen($client->access_token)){
				$success = $client->CallAPI(
					'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))', 
					'GET', array(
						'format'=>'json'
					), array('FailOnAccessError'=>true), $userInfo);
				$emailRes = $client->CallAPI(
					'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))', 
					'GET', array(
						'format'=>'json'
					), array('FailOnAccessError'=>true), $userEmail);
			}
		}
		$success = $client->Finalize($success);
	}
	
	if($client->exit) exit;
	
	if(strlen($client->authorization_error)){
		$client->error = $client->authorization_error;
		$success = false;
	}
	
	if($success){
		$user = new User();
		$inUserData = array();
		$inUserData['oauth_uid']  = !empty($userInfo->id)?$userInfo->id:'';
		$inUserData['first_name'] = !empty($userInfo->firstName->localized->en_US)?$userInfo->firstName->localized->en_US:'';
		$inUserData['last_name']  = !empty($userInfo->lastName->localized->en_US)?$userInfo->lastName->localized->en_US:'';
		$inUserData['email']      = !empty($userEmail->elements[0]->{'handle~'}->emailAddress)?$userEmail->elements[0]->{'handle~'}->emailAddress:'';
		$inUserData['picture']    = !empty($userInfo->profilePicture->{'displayImage~'}->elements[0]->identifiers[0]->identifier)?$userInfo->profilePicture->{'displayImage~'}->elements[0]->identifiers[0]->identifier:'';
		$inUserData['link']       = 'https://www.linkedin.com/';

		$inUserData['oauth_provider'] = 'linkedin';
		$userData = $user->checkUser($inUserData);
		
		
		$_SESSION['userData'] = $userData;
		$_SESSION['oauth_status'] = 'verified';
		print_r($userData); exit;
		
		header('Location: ./');
	}else{
		 $output = 'error'.HtmlSpecialChars($client->error);
	}
}elseif(isset($_GET["oauth_problem"]) && $_GET["oauth_problem"] <> ""){
	$output = $_GET["oauth_problem"];
}else{
	$authUrl = '?oauth_init=1';
	
	$output = '<a href="?oauth_init=1"><img src="images/linkedin-sign-in-btn.png"></a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
</head>
<body>
<div class="container">
    <div class="in-box">        
        <?php echo $output; ?>
    </div>
</div>
</body>
</html>