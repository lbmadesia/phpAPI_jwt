<?php
header("Access-Control-Allow-Origin: http://studentwap.com");
header("Content-Type: application/json");
session_start();
  require("db.php");
  require("vendor/autoload.php");
  use \Firebase\JWT\JWT;

  class chekAuthKey{
    function __construct(){
      if(!isset($_POST['auth_key']) || empty($_POST['auth_key']))
      {
        http_response_code(401);
        exit;
      }
      else{
        new verifyAuthKey();
      }
    }
  }

  class verifyAuthKey{
    protected $token,$db,$api_key,$sql,$response;
    function __construct(){
      $this->token = JWT::decode($_POST['auth_key'],1234,['HS256']);
      $this->db = new db();
      $this->db = $this->db->__construct();
      $this->api_key = $this->token->data->api_key;
      $_SESSION['api_key'] = $this->api_key;
      $this->sql = "SELECT * FROM authKey where api_key = '$this->api_key' AND auth = false";
      $this->response = $this->db->query($this->sql);
      if($this->response->num_rows != 0)
      {
        new signup();
      }
      else{
        http_response_code(401);
        exit;
      }
    }
  }

  class signup{
    protected $required_keys = ['auth_key','firstname','lastname','username','password'];
    protected $temp = [],$response,$sql,$db,$firstname,$lastname,$username,$password,$api_key,$payload,$token;

    function __construct(){
      foreach($this->required_keys as $keyname)
      {
        if(!array_key_exists($keyname,$_POST))
        {
          array_push($this->temp,$keyname);
        }
      }

      if(count($this->temp) > 0)
      {
        http_response_code(404);
        /*$this->response = array(
          "message" => "Keys not found !",
          "keys_info" => $this->temp
        );
        print_r($this->response);
        */
      }

      else{
        $this->db = new db();
        $this->db = $this->db->__construct();
        $this->firstname = $_POST['firstname'];
        $this->lastname = $_POST['lastname'];
        $this->username = $_POST['username'];
        $this->password = md5($_POST['password']);

        $this->sql = "INSERT INTO users(firstname,lastname,username,password) VALUES('$this->firstname','$this->lastname','$this->username','$this->password')";

        if($this->db->query($this->sql))
        {
          $this->api_key = $_SESSION['api_key'];
          $this->sql = "UPDATE authKey SET auth = true WHERE api_key = '$this->api_key'";
          $this->response = $this->db->query($this->sql);
          if($this->response)
          {
            http_response_code(200);
            $this->payload = array(
              "iss" => "http://api.studentwap.com/signup",
              "data" => array(
                "firstname" => $this->firstname,
                "lastname" => $this->lastname,
                "username" => $this->username,
              )
            );

            $this->token = JWT::encode($this->payload,1234);

            $this->response = array(
              "message" => "Signup success !",
              "access_token" => $this->token
            );

            echo json_encode($this->response);
          }
        }

        else{
          http_response_code(409);
          $this->response = array(
            "message" => "Username already exist !",
            "username" => $this->username
          );
          echo json_encode($this->response);
        }
      }

    }
  }

  new chekAuthKey();

?>
