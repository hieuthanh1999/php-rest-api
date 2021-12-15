<?php
namespace Src;

class PodCast {
  private $db;
  private $requestMethod;
  private $podCastId;

  public function __construct($db, $requestMethod, $podCastId)
  {
    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->podCastId = $podCastId;
  }

  public function processRequest()
  {
    switch ($this->requestMethod) {
      case 'GET':
        if ($this->podCastId) {
          $response = $this->getPodCast($this->podCastId);
        } else {
          $response = $this->getAllPodCasts();
        };
        break;
      case 'POST':
        $response = $this->createPodCast();
        break;
      case 'PUT':
        $response = $this->updatePodCast($this->podCastId);
        break;
      case 'DELETE':
        $response = $this->deletePodCast($this->podCastId);
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

  private function getAllPodCasts()
  {
    $query = "
      SELECT
        *
      FROM
        podcast;
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

  private function getPodCast($id)
  {
    $result = $this->find($id);
    if (! $result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function createPodCast()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (! $this->validatePodCast($input)) {
      return $this->unprocessableEntityResponse();
    }

    $query = "
      INSERT INTO podcast
       (id_user, id_category , id_course,	title,	image,	audio,	content)
      VALUES
        (:id_user, :id_category, :id_course,	:title,	:image,	:audio,	:content);
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array(
        'id_user'	=> $input['id_user'],
        'id_category'	=> $input['id_category'],
        'id_course'	=> $input['id_course'],
        'title'	=> $input['title'],
        'image'	=> $input['image'],
        'audio'	=> $input['audio'],
        'content'	=> $input['content']
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }

    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = json_encode(array('message' => 'podCast Created'));
    return $response;
  }

  private function updatePodCast($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $input = (array) json_decode(file_get_contents('php://input'), TRUE);

    if (! $this->validatePodCast($input)) {
      return $this->unprocessableEntityResponse();
    }

    $statement = "
      UPDATE podcast
      SET
        id_user = :id_user,
        id_course = :id_course,
        title = :title,
        image = :image,
        audio  = :audio,
        content = :content
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
        'id' => (int) $id,
        'id_user'	=> $input['id_user'],
        'id_course'	=> $input['id_course'],
        'title'	=> $input['title'],
        'image'	=> $input['image'],
        'audio'	=> $input['audio'],
        'content'	=> $input['content']
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'podCast Updated!'));
    return $response;
  }

  private function deletePodCast($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $query = "
      DELETE FROM podcast
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
    $response['body'] = json_encode(array('message' => 'podCast Deleted!'));
    return $response;
  }

  public function find($id)
  {
    $query = "
      SELECT
        *
      FROM
        podcast
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

  private function validatePodCast($input)
  {
      // if (! isset($input['title'])) {
      //   return false;
      // }
      // if (! isset($input['body'])) {
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