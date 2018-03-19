<?php
  /**
   *
   */
  class InsertDb {
    private $conn;

    function __construct(){
      require_once '../DbConnect.php';

      $db = new DbConnect();
      $this->conn = $db->connect();
    }

    public function insertData($column_index, $json_data, $db_name){
      //Mengambil nama kolom pada tabel pelayanan
      $sql = "SELECT * FROM ". $db_name;
      $result = $this->conn->query($sql);
      $column_info = mysqli_fetch_fields($result);
      $column = array();
      $i = 0;

      foreach ($column_info as $val){
        $column[$i] = $val->name;
        $i++;
      }

      $column_name = array();

      if(sizeof($column_index) == 0){
        for($i=0; $i<sizeof($column); $i++){
          $column_name[$i] = $column[$i];
        }
      } else {
        for($i=0; $i<sizeof($column_index); $i++){
          $index = $column_index[$i];
          $column_name[$i] = $column[$index];
        }
      }

      //Input medrec dinamik ke tabel pelayanan
      $sql = "INSERT INTO `".$db_name."` (";
      for($i=0; $i<sizeof($column_name); $i++){
        $sql .= "$column_name[$i]";
        if($i!=sizeof($column_name)-1){
          $sql .= ",";
        }
      }
      $sql .= ") VALUES (";
      for($i=0; $i<sizeof($json_data); $i++){
        $sql .= "'$json_data[$i]'";
        if($i!=sizeof($json_data)-1){
          $sql .= ",";
        }
      }
      $sql .= ");";
      $qur = $this->conn->query($sql);
      if($qur){
        //Response jika input data berhasil
        return 1;
      }else{
        //Error jika input data gagal
        return 0;
      }
    }
  }

 ?>
