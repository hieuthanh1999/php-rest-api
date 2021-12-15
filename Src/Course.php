<?php
namespace Src;

class Course {
  private $db;
  private $requestMethod;
  private $courseId;

  public function __construct($db, $requestMethod, $courseId)
  {
    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->courseId = $courseId;
  }

  public function processRequest()
  {
    switch ($this->requestMethod) {
      case 'GET':
        if ($this->courseId) {
          $response = $this->getCourse($this->courseId);
        } else {
          $response = $this->getAllCourses();
        };
        break;
      case 'POST':
        $response = $this->createCourse();
        break;
      case 'PUT':
        $response = $this->updateCourse($this->courseId);
        break;
      case 'DELETE':
        $response = $this->deleteCourse($this->courseId);
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

  private function getAllCourses()
  {
    $query = "
      SELECT
        id, name, totalUser, image, description, reg_date
      FROM
        course;
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

  private function getCourse($id)
  {
    $result = $this->find($id);
    if (! $result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function createCourse()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (! $this->validatecourse($input)) {
      return $this->unprocessableEntityResponse();
    }

    $query = "
      INSERT INTO course
        (name, totalUser, image, description)
      VALUES
        (:name, :totalUser, :image, :description);
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array(
        'name' => $input['name'],
        'totalUser' => $input['totalUser'],
        'image' => $input['image'],
        'description' => $input['description']
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }

    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = json_encode(array('message' => 'Post Created'));
    return $response;
  }

  private function updateCourse($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $input = (array) json_decode(file_get_contents('php://input'), TRUE);

    if (! $this->validatecourse($input)) {
      return $this->unprocessableEntityResponse();
    }

    $statement = "
      UPDATE course
      SET
      name = :name, totalUser = :totalUser, image = :image, description = :description
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
        'id' => (int) $id,
        'name' => $input['name'],
        'totalUser' => $input['totalUser'],
        'image' => $input['image'],
        'description' => $input['description']
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'course Updated!'));
    return $response;
  }

  private function deleteCourse($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $query = "
      DELETE FROM course
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
      SELECT * FROM
        course
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

  private function validateCourse($input)
  {
    if (! isset($input['name'])) {
      return false;
    }
    if (! isset($input['image'])) {
    return false;
    }
    if (! isset($input['description'])) {
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