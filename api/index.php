<?php
require "../start.php";
use Src\User;
use Src\Category;
use Src\Posts;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = explode( '/', $uri );
$id = null;
if (isset($uri[2])) {
    $id = (int) $uri[2];
}
$requestMethod = $_SERVER["REQUEST_METHOD"]; 
switch ($uri[1]){
  case 'user':
    $controller = new User($dbConnection, $requestMethod, $id);
    break;
  case 'category':
    $controller = new Category($dbConnection, $requestMethod, $id);
    break;
  case 'post':
      $controller = new Posts($dbConnection, $requestMethod, $id);
      break;
}
$controller->processRequest();