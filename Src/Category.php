<?php
namespace Src;

class Category {
  private $db;
  private $requestMethod;
  private $categoryId;

  public function __construct($db, $requestMethod, $categoryId)
  {
    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->categoryId = $categoryId;
  }

  public function processRequest()
  {
    switch ($this->requestMethod) {
      case 'GET':
        if ($this->categoryId) {
          $response = $this->getCategory($this->categoryId);
        } else {
          $response = $this->getAllCategorys();
        };
        break;
      case 'POST':
        $response = $this->createCategory();
        break;
      case 'PUT':
        $response = $this->updateCategory($this->categoryId);
        break;
      case 'DELETE':
        $response = $this->deleteCategory($this->categoryId);
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

  private function getAllCategorys()
  {
    $query = "
      SELECT
        *
      FROM
        category ORDER BY id DESC;
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

  private function getCategory($id)
  {
    $result = $this->find($id);
    if (! $result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function createCategory()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (! $this->validateCategory($input)) {
      return $this->unprocessableEntityResponse();
    }

    $query = "
      INSERT INTO category
        (name, 	type_category, description)
      VALUES
        (:name, :type_category, :description);
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array(
        'name' => $input['name'],
        'type_category' => $input['type_category'],
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

  private function updateCategory($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $input = (array) json_decode(file_get_contents('php://input'), TRUE);

    if (! $this->validateCategory($input)) {
      return $this->unprocessableEntityResponse();
    }

    $statement = "
      UPDATE category
      SET
      name = :name,
      type_category = :type_category,
      description = :description
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
        'id' => (int) $id,
        'name' => $input['name'],
        'type_category' => $input['type_category'],
        'description' => $input['description']
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'Category Updated!'));
    return $response;
  }

  private function deleteCategory($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $query = "
      DELETE FROM category
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
        category
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

  private function validateCategory($input)
  {
    if (! isset($input['name'])) {
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