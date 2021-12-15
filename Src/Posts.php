<?php
namespace Src;

class Posts {
  private $db;
  private $requestMethod;
  private $postId;

  public function __construct($db, $requestMethod, $postId)
  {
    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->postId = $postId;
  }

  public function processRequest()
  {
    switch ($this->requestMethod) {
      case 'GET':
        if ($this->postId) {
          $response = $this->getPost($this->postId);
        } else {
          $response = $this->getAllPosts();
        };
        break;
      case 'POST':
        $response = $this->createPost();
        break;
      case 'PUT':
        $response = $this->updatePost($this->postId);
        break;
      case 'DELETE':
        $response = $this->deletePost($this->postId);
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

  private function getAllPosts()
  {
    $query = "
      SELECT
       *
      FROM
        posts;
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

  private function getPost($id)
  {
    $result = $this->find($id);
    if (! $result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function createPost()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (! $this->validatePost($input)) {
      return $this->unprocessableEntityResponse();
    }

    $query = "
      INSERT INTO posts
       (id_user, id_category, user,	title,	image,	content,	numview,	numcomment,	numlove,	islove,	isgroup,	idgroup,	ranker)
      VALUES
      (:id_user, :id_category, :user,	:title,	:image,	:content,	:numview,	:numcomment,	:numlove,	:islove,	:isgroup,	:idgroup,	:ranker);
    ";

    try {
      $statement = $this->db->prepare($query);
      $statement->execute(array(
        'id_user'	=> $input['id_user'],
        'id_category'	=> $input['id_category'],
        'user'	=> $input['user'],
        'title'	=> $input['title'],
        'image'	=> $input['image'],
        'content'	=> $input['content'],
        'numview'	=> $input['numview'],
        'numcomment'	=> $input['numcomment'],
        'numlove'  => $input['numlove'],
        'islove' => $input['islove'],
        'isgroup'  => $input['isgroup'],
        'idgroup' => $input['idgroup'],
        'ranker'  => $input['ranker']
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }

    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = json_encode(array('message' => 'Post Created'));
    return $response;
  }

  private function updatePost($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $input = (array) json_decode(file_get_contents('php://input'), TRUE);

    if (! $this->validatePost($input)) {
      return $this->unprocessableEntityResponse();
    }

    $statement = "
      UPDATE posts
      SET
        id_user = :id_user,
        id_category  = :id_category,
        user = :user,
        title = :title,
        image = :image,
        content = :content,
        numview = :numview,
        numcomment = :numcomment,
        numlove  = :numlove,
        islove = :islove,
        isgroup = :isgroup,
        idgroup = :idgroup,
        ranker  = :ranker
      WHERE id = :id;
    ";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array(
        'id' => (int) $id,
        'id_user'	=> $input['id_user'],
        'id_category'	=> $input['id_category'],
        'user'	=> $input['user'],
        'title'	=> $input['title'],
        'image'	=> $input['image'],
        'content'	=> $input['content'],
        'numview'	=> $input['numview'],
        'numcomment'	=> $input['numcomment'],
        'numlove'  => $input['numlove'],
        'islove' => $input['islove'],
        'isgroup'  => $input['isgroup'],
        'idgroup' => $input['idgroup'],
        'ranker'  => $input['ranker']
      ));
      $statement->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(array('message' => 'Post Updated!'));
    return $response;
  }

  private function deletePost($id)
  {
    $result = $this->find($id);

    if (! $result) {
      return $this->notFoundResponse();
    }

    $query = "
      DELETE FROM posts
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
        posts
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

  private function validatePost($input)
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