<?php
namespace Src;

class User {
  private $db;
  private $requestMethod;
  private $userId;

  public function __construct($db, $requestMethod, $userId)
  {
    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->userId = $userId;
  }

  public function processRequest()
  {
    switch ($this->requestMethod) {
      case 'GET':
        if ($this->userId) {
          $response = $this->getUser($this->userId);
        } else {
          $response = $this->getAllUsers();
        };
        break;
      case 'POST':
        $response = $this->createUser();
        break;
      case 'PUT':
        $response = $this->updateUser($this->userId);
        break;
      case 'DELETE':
        $response = $this->deleteUser($this->userId);
        break;
      default:
        $response = $this->notFoundResponse();
        break;
    }
    header($response['status_code_header']);
    if ($response['body']) {
      echo $response['body'];
    }
  }

  private function getAllUsers()
  {
    $query = "
      SELECT
        id, name, email, password, phone, avatar, type, ranker, reg_date
      FROM
        user;
    ";

    try {
      $statement = $this->db->query($query);
      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }

    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function getUser($id)
  {
    $result = $this->find($id);
    if (! $result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function createUser()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (! $this->validateUser($input)) {
      return $this->unprocessableEntityResponse();
    }

    $query = "
      INSERT INTO user
        (name, email, password, phone, avatar, type, ranker)
      VALUES
        (:name, :email, :password, :phone, :avatar, :type, :ranker);
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array(
        'name' => $input['name'],
        'email'  => $input['email'],
        'password' => $input['password'],
        'phone' => $input['phone'],
        'avatar' => $input['avatar'],
        'type' => $input['type'],
        'ranker' => $input['ranker'],
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }

    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = json_encode(array('message' => 'Post Created'));
    return $response;
  }

  private function updateUser($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $input = (array) json_decode(file_get_contents('php://input'), TRUE);

    if (! $this->validateUser($input)) {
      return $this->unprocessableEntityResponse();
    }

    $statement = "
      UPDATE user
      SET
      name = :name, email = :email, password = :password, phone = :phone, avatar = :avatar, type = :type, ranker = :ranker
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
        'id' => (int) $id,
        'name' => $input['name'],
        'email'  => $input['email'],
        'password' => $input['password'],
        'phone' => $input['phone'],
        'avatar' => $input['avatar'],
        'type' => $input['type'],
        'ranker' => $input['ranker'],
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'User Updated!'));
    return $response;
  }

  private function deleteUser($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $query = "
      DELETE FROM user
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array('id' => $id));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'Post Deleted!'));
    return $response;
  }

  public function find($id)
  {
    $query = "
      SELECT
        id, name, email, password, phone, avatar, type, ranker, reg_date
      FROM
        user
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array('id' => $id));
      $result = $statement->fetch(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }

  private function validateUser($input)
  {
    if (! isset($input['email'])) {
      return false;
    }
    if (! isset($input['password'])) {
      return false;
    }
    return true;
  }

  private function unprocessableEntityResponse()
  {
    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = json_encode([
      'error' => 'Invalid input'
    ]);
    return $response;
  }

  private function notFoundResponse()
  {
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    return $response;
  }
}