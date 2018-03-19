<?php
  /**
  * index.php
  * Created by Annisa Alifiani
  *
  * Controller untuk mengakses REST Service JWT
  */
  require_once 'token/Builder.php';
  require_once 'token/Parser.php';
  require_once 'token/Verifier.php';

  if($_SERVER['REQUEST_METHOD'] == "POST"){
    //Mengambil data pada http request
    $request_body = file_get_contents('php://input');
    $json = json_decode($request_body, true);

    $builder = new Builder();
    $verifier = new Verifier();

    if(count($json) == 1){
      $header = $verifier->checkHeader($json['0']);
      if($header){
        $payload = $verifier->checkPayload($json['0']);
        if($payload == 1){
          $signature = $verifier->checkSignature($json['0']);
          if($signature){
            $response = array("code" => 113, "status" => "Token not expired");
          } else {
            $response = array("code" => 104, "status" => "Token not verified");
          }
        } else if($payload == 2){
          $response = array("code" => 106, "status" => "Payload not valid");
        } else if($payload == 3){
          $response = array("code" => 103, "status" => "Token expired");
        }
      } else {
        $response = array("code" => 104, "status" => "Token not verified");
      }
    } else if(count($json) == 2){ //Request JWT baru
      $username = $json['0'];
      $password = $json['1'];

      $code = $builder->createCodeAuth($username, $password);
      if($code == 101){
        //Error jika autentikasi gagal
        $response = array("code" => 101, "status" => "Autentikasi gagal");
      } else {
        //Response jika autentikasi berhasil dan melakukan generate JWT
        $response = array("code" => 111, "token" => $builder->generateToken($code));
      }
    } else if(count($json) == 3){ //Request refresh JWT
      $username = $json['0'];
      $password = $json['1'];
      $old_token = $json['2'];

      $code = $builder->createCodeAuth($username, $password);

      if($code == 101){
        //Error jika autentikasi gagal
        $response = array("code" => 101, "status" => "Autentikasi gagal");
      } else {
        $auth_code = $verifier->checkAuthCode($code, $old_token);
        if(!$auth_code){
          $response = array("code" => 101, "status" => "Auth code tidak sama x ".$code);
        } else {
          $response = $builder->refreshToken($code, $old_token);
          if($response == 4){
            $response = array("code" => 104, "status" => "Signture salah");
          } else {
            $response = array("code" => 111, "token" => $builder->refreshToken($code, $old_token));
          }
        }
      }
    } else {
      //Error jika input yang dimasukan tidak sesuai
      $response = array("code" => 102, "status" => "Input yang dimasukan salah");
    }
  } else {
    $response = array("code" => 105, "status" => "Method not allowed");
  }

  //Menampilkan response JWT
  header('Content-type: application/json');
  echo json_encode($response);
?>
