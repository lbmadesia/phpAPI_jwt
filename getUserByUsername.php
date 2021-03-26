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
    if(!isset($_GET['auth_key']) || empty($_GET['auth_key']))
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
    $this->token = JWT::decode($_GET['auth_key'],1234,['HS256']);
    $this->api_key = $this->token->data->api_key;
    $_SESSION['api_key'] = $this->api_key;
    $this->sql = "SELECT * FROM authKey where api_key = '$this->api_key' AND auth = false";
    $this->response = $this->db->query($this->sql);
    if($this->response->num_rows != 0)
    {
      new findUser();
    }
    else{
      http_response_code(401);
      exit;
    }
  }

}

class findUser extends config{
  protected $sql,$response,$username;
  function __construct(){
    $this->conn();
    $this->username = $_GET['username'];
    $this->sql = "SELECT * FROM users WHERE username='$this->username'";
    $this->response = $this->db->query($this->sql);
    if($this->response->num_rows != 0)
    {
      $payload = array(
        "iss" => "http://api.studentwap.com/finduser",
        "data" => array(
          "username" => $this->username
        )
      );

      $token = JWT::encode($payload,1234);
      header("Location: email.php?access_token=".$token);
    }

    else{
      http_response_code(404);
      $this->response = array(
        "message" => "User not found !"
      );
      echo json_encode($this->response);
    }
  }

}

new checkAuthKey();

?>
