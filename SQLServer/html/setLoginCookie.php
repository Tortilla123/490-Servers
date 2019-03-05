<?php


date_default_timezone_set("America/New_York");
session_set_cookie_params(0, "/var/www/html", "localhost");
session_start();


include("Function.php");


#echo "<br> User: "; getdata("user", $user);
#echo "<br> Pass: "; getdata("password", $pass);

$user = $_POST["user"];
$pass = $_POST["password"];

$_SESSION["user"] = $user;
$_SESSION["password"] = $pass;


#echo $_SESSION["user"];
#echo $_SESSION["password"];

##############################################
####### RABBITMQ CODE ########################
##############################################

#session_start();

$user = $_SESSION["user"];
$pass = $_SESSION["password"];

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient('testRabbitMQ.ini', 'testServer');

$msg = "Do your own work sometimes";

$request['type'] = "Login";
$request['username'] = $user;
$request['password'] = $pass;
$request['message'] = $msg;

$response = $client->send_request($request);

$_SESSION["result"] = $response["result"];

echo $_SESSION["result"];

?>

