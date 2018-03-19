<?php

  /**
   *
   */
  class Parser {

    public function __construct()
    {}

    public function getHeader($access_token){
      $jwt_values = explode('.', $access_token);
      return $jwt_values[0];
    }

    public function getPayload($access_token){
      $jwt_values = explode('.', $access_token);
      return $jwt_values[1];
    }

    public function getSignature($access_token){
      $jwt_values = explode('.', $access_token);
      return $jwt_values[2];
    }

  }


 ?>
