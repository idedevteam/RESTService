<?php
  /**
  * (c) 2018
  * Validation.php
  * Created by Annisa Alifiani
  *
  * REST service untuk melakukan validasi input
  */
  class Validate {

    public function __construct(){
    }

    //fungsi untuk mengecek apakah data yang dimasukan numeric atau bukan
    public function isNumeric($data){
      if(is_numeric($data)){
        return true;
      } else {
        return false;
      }
    }

    //fungsi untuk memvalidasi tanggal
    public function dateValidation($data){
      //format tanggal yang benar, cont : 1999-01-01
      if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$data)) {
          return true;
      } else {
          return false;
      }
    }

    //fungsi untuk memvalidasi kode puskesmas
    public function kdPuskesmasValidation($kode){
      if($this->isNumeric($kode)){
        return false;
      } else {
        $length = strlen($kode);
        $first = substr($kode, -$length, 1);
        if($first == "P"){
          return true;
        } else {
          return false;
        }
      }
    }
}

 ?>
