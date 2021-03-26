<?php
  header("Access-Control-Allow-Origin: http://studentwap.com");
  /*if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
  {
    http_response_code(401);
    exit;
  }*/
  header("Content-Type: application/json");
  require("vendor/autoload.php");
  use \Firebase\JWT\JWT;
  class ApiKey{
    protected $db,
    $sql,
    $apiKey,
    $token,
    $payload,
    $response;

  function __construct(){
    $this->db = new mysqli("localhost","adse","adse@1234","educationwap");
    if(!$this->db->connect_error)
    {
      $this->apiKey = md5(uniqid());
      $this->sql = "INSERT INTO authKey(api_key) VALUES('$this->apiKey')";
      $this->response = $this->db->query($this->sql);
      if($this->response)
      {
        $this->payload = array(
          "iss" => "http://api.studentwap.com/authkey",
          "data" => array(
            "api_key" => $this->apiKey,
          )
        );
        $this->token = JWT::encode($this->payload,1234);
        http_response_code(200);

        $this->response = array(
          "message" => "Auth key generated !",
          "auth_key" => $this->token
        );
        echo json_encode($this->response);
      }
      else{
        http_response_code(500);
        $this->response = array(
          "message" => "Unable to generate auth key"
        );
        echo json_encode($this->response);
      }
    }
  }


  }

  new ApiKey();
?>
