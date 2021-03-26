<?php
header("Access-Control-Allow-Origin: http://studentwap.com");
header("Content-Type: application/json");
session_start();
require("db.php");
require("vendor/autoload.php");
use \Firebase\JWT\JWT;

class config{
  protected $db;
  function conn(){
    $this->db = new db();
    $this->db = $this->db->__construct();
  }
}

class checkAuthKey{
  function __construct(){
    if(
      !isset($_POST['auth_key']) || empty($_POST['auth_key']) && !isset($_POST['username']) || empty($_POST['username']) &&
      !isset($_POST['password']) || empty($_POST['password'])
      )
    {
      http_response_code(401);
      exit;
    }
    else{
      new verifyAuthKey();
    }
  }
}

class verifyAuthKey extends config{
  protected $token,$db,$api_key,$sql,$response;
  function __construct(){
    $this->conn();
    $this->token = JWT::decode($_POST['auth_key'],1234,['HS256']);
    $this->api_key = $this->token->data->api_key;
    $_SESSION['api_key'] = $this->api_key;
    $this->sql = "SELECT * FROM authKey where api_key = '$this->api_key' AND auth = false";
    $this->response = $this->db->query($this->sql);
    if($this->response->num_rows != 0)
    {
      new updatePassword();
    }
    else{
      http_response_code(401);
      exit;
    }
  }

}

class updatePassword extends config{
  protected $username,$password,$response,$sql,$api_key;

  function __construct(){
    $this->conn();
    $this->username = $_POST['username'];
    $this->password = md5($_POST['password']);
    $this->sql = "SELECT * FROM users WHERE username='$this->username'";
    $this->response = $this->db->query($this->sql);
    if($this->response->num_rows != 0)
    {
      $this->sql = "UPDATE users SET password='$this->password' WHERE username='$this->username'";
      $this->response = $this->db->query($this->sql);
      if($this->response)
      {
        $this->api_key = $_SESSION['api_key'];
        $this->sql = "UPDATE authKey SET auth=true WHERE api_key='$this->api_key'";
        $this->response = $this->db->query($this->sql);
        if($this->response)
        {
          http_response_code(201);
          $this->response = array(
            "message" => "Password changed !"
          );
          echo json_encode($this->response);
        }
      }
    }
    else{
      http_response_code(401);
      exit;
    }
  }
}
new checkAuthKey();

?>
