<?php

  /**
   *
   */

  require_once 'Builder.php';
  require_once 'Parser.php';

  class Verifier
  {
    private $parser;

    public function __construct(){
      $this->parser = new Parser();
    }

    //fungsi untuk mengecek token sudah expired atau belum
    public function expiredToken($exp){

      //mengambil waktu sekarang untuk dibandingkan dengan date expire
      $now = date("Y/m/d h:i:s");

      if($now < $exp){
        return false;
      } else {
        return true;
      }
    }

    public function differentTime($nbf, $exp){
      $datetime1 = date_create($nbf);
      $datetime2 = date_create($exp);
      $interval = date_diff($datetime1, $datetime2);
      if($interval->format('%a:%H:%I:%S') == '0:00:15:00'){
        return 1;
      } else {
        return 0;
      }
    }

    public function checkHeader($token){
      $header = $this->parser->getHeader($token);
      $header = base64_decode($header);
      $headerData = json_decode($header, true);
      $alg = $headerData['alg'];
      if($alg == "HS256"){
        return true;
      } else {
        return false;
      }
    }

    public function checkPayload($token){
      $payload = $this->parser->getPayload($token);
      $payload = base64_decode($payload);
      $payloadData = json_decode($payload, true);
      $nbf = $payloadData['nbf'];
      $exp = $payloadData['exp'];

      if(!$this->differentTime($nbf, $exp)){
        return 2;
      } else {
        if($this->expiredToken($exp)){
          return 3;
        } else {
          return 1;
        }
      }
    }

    public function checkSignature($token){
      $header = $this->parser->getHeader($token);
      $payload = $this->parser->getPayload($token);
      $signature = $this->parser->getSignature($token);

      $builder = new Builder();
      $resultedsignature = $builder->createSignature($header, $payload);

      if($resultedsignature == $signature){
        return true;
      } else {
        return false;
      }
    }

    public function checkAuthCode($code, $token){
      $payload = $this->parser->getPayload($token);
      $payload = base64_decode($payload);
      $payloadData = json_decode($payload, true);
      $code_old = $payloadData['code'];

      if($code == $code_old){
        return true;
      } else {
        return false;
      }
    }

  }

 ?>
