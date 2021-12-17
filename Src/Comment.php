<?php
namespace Src;

class Comment {
  private $db;
  private $requestMethod;
  private $cmtId;

  public function __construct($db, $requestMethod, $cmtId)
  {
    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->cmtId = $cmtId;
  }

  public function processRequest()
  {
    switch ($this->requestMethod) {
      case 'GET':
        if ($this->cmtId) {
          $response = $this->getComment($this->cmtId);
        } else {
          $response = $this->getAllComment();
        };
        break;
      case 'POST':
        $response = $this->createComment();
        break;
      case 'PUT':
        $response = $this->updateComment($this->cmtId);
        break;
      case 'DELETE':
        $response = $this->deleteComment($this->cmtId);
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

  private function getAllComment()
  {
    $query = "
      SELECT
        *
      FROM
      comment;
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

  private function getComment($id)
  {
    $result = $this->find($id);
    if (! $result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function createComment()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (! $this->validateComment($input)) {
      return $this->unprocessableEntityResponse();
    }

    $query = "
      INSERT INTO 
      comment
        (id_user, comment)
      VALUES
      (:id_user, :comment)
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array(
        'id_user' => $input['id_user'],
       'comment' => $input['comment'],
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }

    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = json_encode(array('message' => 'Post Created'));
    return $response;
  }

  private function updateComment($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $input = (array) json_decode(file_get_contents('php://input'), TRUE);

    if (! $this->validateComment($input)) {
      return $this->unprocessableEntityResponse();
    }

    $statement = "
      UPDATE 
      comment
      SET
      id_user = :id_user,
      comment = :comment,
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
        'id' => (int) $id,
        'id_user' => $input['id_user'],
        'comment' => $input['comment'],
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => '
    comment Updated!'));
    return $response;
  }

  private function deleteComment($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $query = "
      DELETE FROM 
      comment
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
        
      comment
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

  private function validateComment($input)
  {
    // if (! isset($input['id_'])) {
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