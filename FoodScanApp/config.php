<?php
/**
 * Created by PhpStorm.
 * User: c119
 * Date: 04/03/15
 * Time: 12:10 PM
 */

include_once 'Logger.php';

//ini_set('display_errors', 1);

$logger = new Logger();

date_default_timezone_set('UTC');

//$server = "192.168.1.201";
//$server = "clientapp.narola.online";
//$user = "flierapp";
//$password = "HCODRduoXlAc6Fl";
//$dbname = "flierapp";

$server = "192.168.1.11";
//$server = "clientapp.narola.online";
$user = "food_scan_app";
$password = "RWMrKFst9URHh3JQ";
$dbname = "food_scan_app";


global $con;
$con = mysqli_connect($server, $user, $password,$dbname);

if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
//else
//{
  // echo "connected successfully";
//}

?>