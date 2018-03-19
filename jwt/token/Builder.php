<?php

  /**
  * (c) 2018
  * Validation.php
  * Created by Annisa Alifiani
  *
  * REST service untuk membuat token
  */

  require_once '../DbConnect.php';
  require_once 'Parser.php';
  require_once 'Verifier.php';

  class Builder {

    private $parser, $verifier;

    public function __construct(){
      $this->parser = new Parser();
      $this->verifier = new Verifier();
    }

    //fungsi untuk membuat token baru
    public function generateToken($code){
      //membuat header
      $header = [
        'typ' => 'JWT',
        'alg' => 'HS256'
      ];
      $header = json_encode($header);
      $header = base64_encode($header);

      //membuat date expire
      $exp = date("Y/m/d h:i:s", strtotime("+15 minutes"));

      $this_time = date("Y/m/d h:i:s");

      //membuat payload
      $payload = [
        "nbf" => $this_time,
        "exp" => $exp,
        "code" => $code
      ];
      $payload_array = json_encode($payload);
      $payload = base64_encode($payload_array);

      $signature = $this->createSignature($header, $payload);
      $response = "$header.$payload.$signature";
      return $response;
    }

    //fungsi untuk membuat refresh token
    public function refreshToken($code, $old_token){
      $header = $this->verifier->checkHeader($old_token);
      if($header){
        $payload = $this->verifier->checkPayload($old_token);
        if($payload != 2){
          $signature = $this->verifier->checkSignature($old_token);
          if($signature){
            $response = $this->generateToken($code);
          } else {
            $response = 4;
          }
        }
      }
      return $response;
    }

    //fungsi untuk membuat siganutre
    public function createSignature($header, $payload){
      //secret key untuk signature
      $key = 'Secretkey';

      //membuat signature dengan HMAC method
      $signature = hash_hmac('sha256',"$header.$payload", $key, true);
      $signature = base64_encode($signature);

      return $signature;
    }

    //fungsi untuk membuat kode authentikasi
    public function createCodeAuth($username, $password){
      $hashed_password = hash('sha512', $password);

      $db = new DbConnect();
      $conn = $db->connect();

      $sql = "SELECT * FROM users WHERE USER_NAME='$username' AND USER_PASSWORD='$password';";
      $result = $conn->query($sql);

      if($result->num_rows > 0){
        $code = base64_encode($username.$password);
        return $code;
      }else{
      	return 101;
      }
    }

  }


 ?>
