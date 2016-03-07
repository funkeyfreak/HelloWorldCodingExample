<?php
ob_start();
session_start();

//set timezone
date_default_timezone_set('America/Los_Angeles');

//database credentials
#define('DBHOST','dalinwilliams_c.mysql');
define('DBUSER','dalinwilliams_c');
define('DBPASS','m12591259');
define('DBNAME','dalinwilliams_c');


define('DBHOST','localhost');
//application address
define('DIR','https://www.dalinwilliams.com/helloworld/client/');
define('SITEEMAIL','info@dalinwilliams.com');

try {
	$con=mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
	//create PDO connection
	$db = new PDO("mysql:host=".DBHOST.";port=3306;dbname=".DBNAME, DBUSER, DBPASS);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
	//show error
    echo '<p class="bg-danger">'.$e->getMessage().'</p>';
    exit;
}


//include the user class, pass in the database connection
include('classes/user.php');
$user = new User($db);
?>
