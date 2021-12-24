<?php
require "../start.php";
use Src\User;
use Src\Category;
use Src\Posts;
use Src\Course;
use Src\PodCast;
use Src\Comment;
use Src\Rank;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = explode( '/', $uri );
$id = null;
if (isset($uri[2])) {
    $id = (int) $uri[2];
}
$requestMethod = $_SERVER["REQUEST_METHOD"]; 
if($requestMethod == "OPTIONS") {
  die();
}
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
  case 'course':
      $controller = new Course($dbConnection, $requestMethod, $id);
      break;
  case 'podcast':
      $controller = new PodCast($dbConnection, $requestMethod, $id);
      break;
  case 'comment':
    $controller = new Comment($dbConnection, $requestMethod, $id);
    break;
  case 'rank':
    $controller = new Rank($dbConnection, $requestMethod, $id);
    break;
  default:
      header("HTTP/1.1 404 Not Found");
      exit();
      break;
}
$controller->processRequest();