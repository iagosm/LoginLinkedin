<?php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'uy');//dn name
define('DB_USER_TBL', 'users');

define('LIN_CLIENT_ID', '77uk8ahx0sxkn3');
define('LIN_CLIENT_SECRET', 'ui0I0q1WWmzDvvaZ');
define('LIN_REDIRECT_URL', 'http://localhost/loginlinkedin/index.php');
define('LIN_SCOPE', 'r_liteprofile r_emailaddress');
if(!session_id()){
    session_start();
}
require_once 'linkedin-oauth-client-php/http.php';
require_once 'linkedin-oauth-client-php/oauth_client.php';
?>