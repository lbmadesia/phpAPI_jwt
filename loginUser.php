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
        new login();
      }
      else{
        http_response_code(401);
        exit;
      }
    }

  }

  class login extends config{
    protected $username,$password,$sql,$response,$token,$data,$payload,$api_key;
    function __construct(){
      $this->conn();
      $this->username = $_GET['username'];
      $this->password = md5($_GET['password']);
      $this->sql = "SELECT * FROM users WHERE username='$this->username' AND password='$this->password'";
      $this->response = $this->db->query($this->sql);
      if($this->response->num_rows != 0)
      {
        http_response_code(200);
        $this->data = $this->response->fetch_assoc();
        $this->payload = array(
          "iss" => "http://api.studentwap.com/login",
          "data" => array(
            "firstname" => $this->data['firstname'],
            "lastname" => $this->data['lastname'],
            "username" => $this->data['username'],
          )
        );

        $this->token = JWT::encode($this->payload,1234);
        $this->response = array(
          "message" => "Login success !",
          "access_token" => $this->token
        );
        $this->api_key = $_SESSION['api_key'];
        $this->sql = "UPDATE authKey SET auth = true WHERE api_key='$this->api_key'";

        if($this->db->query($this->sql))
        {
          echo json_encode($this->response);
        }

      }
      else{
        http_response_code(401);
        $this->response = array(
          "message" => "Incorrect username or password",
          "username" => $this->username
        );
        echo json_encode($this->response);
      }
    }

  }
  new checkAuthKey();

?>
