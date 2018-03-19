<?php
  /**
   *
   */
  class Kode {

    function __construct(){
      require_once '../DbConnect.php';

      $db = new DbConnect();
      $this->conn = $db->connect();
    }


    public function getKdPasien($nik){
      $sql    = "SELECT KD_PASIEN FROM pasien WHERE NO_PENGENAL = '".$nik."';";
      $result = $this->conn->query($sql);
      if($result->num_rows > 0){
        $row        = $result->fetch_assoc();
        $kd_pasien  = $row["KD_PASIEN"];
        return $row["KD_PASIEN"];
      }
    }

    public function getKdUnit($poli){
      if($poli == 0){
        return 213;
      } else if($poli == 1){
        return 210;
      }
    }
  }
 ?>
