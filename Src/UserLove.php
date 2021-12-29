<?php
namespace Src;

class UserLove {
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
          $response = $this->getSetting($this->userId); //get data id đó
        } else {
          $response = $this->getAllSetting(); // get data tất cả
        };
        break;
      case 'POST':
        $response = $this->createSetting();
        break;
      case 'PUT':
        $response = $this->updateSetting($this->userId);
        break;
      case 'DELETE':
        $response = $this->deleteSetting($this->userId);
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

  private function getAllSetting()
  {
    $query = "
      SELECT
        *
      FROM
        userlove;
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

  private function getSetting($id)
  {
    $result = $this->find($id);
    if (! $result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function createSetting()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    // if (! $this->validateUser($input)) {
    //   return $this->unprocessableEntityResponse();
    // }

    $query = "
      INSERT INTO userlove
        (id_user, id_blog, status)
      VALUES
        (:id_user, :id_blog, :status);
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array(
        'id_user' => $input['id_user'],
        'id_blog' => $input['id_blog'],
        'status'  => $input['status'],
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }

    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = json_encode(array('message' => 'Post Created'));
    return $response;
  }

  private function updateSetting($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $input = (array) json_decode(file_get_contents('php://input'), TRUE);

    if (! $this->validateSetting($input)) {
      return $this->unprocessableEntityResponse();
    }

    $statement = "
      UPDATE userlove
      SET
      id_user = :id_user, id_blog = :id_blog,  status = :status
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
        'id' => (int) $id,
        'id_user' => $input['id_user'],
        'id_blog' => $input['id_blog'],
        'status'  => $input['status'],
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'User Updated!'));
    return $response;
  }

  private function deleteSetting($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $query = "
      DELETE FROM userlove
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
      userlove
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

  private function validateSetting($input)
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