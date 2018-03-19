<?php

  /**
   *
   */

  class BiodataPasien {
    private $conn;

    function __construct(){
      require_once '../DbConnect.php';

      $db         = new DbConnect();
      $this->conn = $db->connect();
    }

    public function NIKExist($nik){
      $sql    = "SELECT KD_PASIEN FROM pasien WHERE NO_PENGENAL = '".$nik."' ;";
      $result = $this->conn->query($sql);
      if($result->num_rows > 0){
        return 1;
      } else {
        return 0;
      }
    }

    public function registerKdPasien($nik){
      $kd_puskesmas = "P3273020203";
      $sql = "SELECT MAX(SUBSTR(KD_PASIEN,-7)) AS total FROM pasien WHERE KD_PUSKESMAS = '".$kd_puskesmas."';";
      $result = $this->conn->query($sql);
      $row = $result->fetch_array();
      $kd_pasien = $kd_puskesmas.sprintf("%07d", $row["total"]+1);

      $sql = "INSERT INTO pasien (KD_PUSKESMAS, KD_PASIEN, NO_PENGENAL) VALUES ('".$kd_puskesmas."','".$kd_pasien."','".$nik."');";
      $result = $this->conn->query($sql);
      if($result){
        return 1;
      } else{
        return 0;
      }
    }
  }

 ?>
