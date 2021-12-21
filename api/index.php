<?php
require "../start.php";
use Src\User;
use Src\Category;
use Src\Posts;
use Src\Course;
use Src\PodCast;
use Src\Comment;
use Src\Rank;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

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