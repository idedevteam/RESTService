<?php
  /**
  * (c) 2018
  * DbConnect.php
  * Created by Annisa Alifiani
  *
  * File untuk membuka koneksi ke database
  */
  class DbConnect {

    function __construct(){}

    public function connect(){
      $servername = "localhost";
      $username = "root";
      $password = "";
      $dbname = "sikda_puskesmas";

      //Membuat koneksi
      $conn = new mysqli($servername, $username, $password, $dbname);

      //Pengecekan koneksi
      if ($conn->connect_error) {
        //Error jika koneksi gagal
        die("Connection failed: " . $conn->connect_error);
      }

      return $conn;
    }
  }
?>
