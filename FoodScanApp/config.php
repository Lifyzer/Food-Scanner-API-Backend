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
$server = "192.168.1.11";
$user = "food_scan_app";
$password = "RWMrKFst9URHh3JQ";
$dbname = "food_scan_app";

global $con;

try {
    # MS SQL Server and Sybase with PDO_DBLIB
    # MySQL with PDO_MYSQL
    $con = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8mb4", $user, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    file_put_contents('LogFile/error.txt', $con);
    $con->exec("set names utf8");

    if (!$con) {
//         echo "not connected";
    }
}
catch(PDOException $e) {
    echo $e->getMessage();
}
?>
