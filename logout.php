<?php
if(!session_id()){
    session_start();
}

unset($_SESSION['oauth_status']);
unset($_SESSION['userData']);
session_destroy();
header("Location:index.php");
exit;
?>