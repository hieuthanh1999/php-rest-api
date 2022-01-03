<?php
namespace Src;

class UpdateRank {
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
          $response = $this->getUser($this->userId); //get data id đó
        } else {
          $response = $this->getAllUsers(); // get data tất cả
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
      DISTINCT id_user, id_blog
      FROM
        updaterank ORDER BY id DESC;
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
      INSERT INTO updaterank
        (id_blog, id_user)
      VALUES
        (:id_blog, :id_user);
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array(
        'id_blog' => $input['id_blog'],
        'id_user'  => $input['id_user']
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
      UPDATE updaterank
      SET
        id_blog = :id_blog, id_user = :id_user, 
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
        'id' => (int) $id,
        'id_blog' => $input['id_blog'],
        'id_user'  => $input['id_user']
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
      DELETE FROM updaterank
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
       *
      FROM
      updaterank
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
    // if (! isset($input['email'])) {
    //   return false;
    // }
    // if (! isset($input['password'])) {
    //   return false;
    // }
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