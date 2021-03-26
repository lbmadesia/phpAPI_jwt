<?php
  header("Access-Control-Allow-Origin: http://studentwap.com");
  header("Content-Type: application/json");
  require("vendor/autoload.php");
  use \Firebase\JWT\JWT;



  if(isset($_GET['access_token']) && !empty($_GET['access_token']))
  {
    $data = JWT::decode($_GET['access_token'],1234,['HS256']);
    if($data->iss == "http://api.studentwap.com/finduser")
    {
      $i;
      $code = 0;
      for($i=1;$i<6;$i++)
      {
        $code .= rand(0,9);
      }

      $username = $data->data->username;
      sendmail($username,$code);

    }

    else{
      http_response_code(401);
      exit;
    }

  }

  else{
    http_response_code(401);
    exit;
  }

  function sendmail($username,$code)
  {
    $email = mail($username,"Email verification code","Your verification code is ".$code);
    if($email)
    {
        
        http_response_code(200);
        $payload = array(
          "iss" => "http://api.studentwap.com/email",
          "data" => array(
            "code" => $code
          )
        );

        $token = JWT::encode($payload,1234);
        $response = array(
          "message" => "Success",
          "access_token" => $token
        );

        echo json_encode($response);
    }

  }

?>
