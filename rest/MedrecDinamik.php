<?php
  /**
  * MedrecDinamik.php
  * Created by Annisa Alifiani
  *
  * REST service untuk melakukan input medical record dinamik
  * ke tabel pelayanan
  */

  require_once '../DbConnect.php';
  require_once '../jwt/token/Verifier.php';
  require_once 'BiodataPasien.php';
  require_once 'utils/Kode.php';
  require_once 'utils/InsertDb.php';

  if($_SERVER['REQUEST_METHOD'] == "POST"){
    //Header checking
  	$headers = apache_request_headers();
  	$token = $headers['Authorization'];

    if(empty($token)){
      //Error karena tidak ada header token
  		$response = array("code" => 201, "status" => "Tidak terautorisasi");
  	} else {
      $verifier = new Verifier();
      $header = $verifier->checkHeader($token);
      if($header){
        $payload = $verifier->checkPayload($token);
        if($payload == 1){
          $signature = $verifier->checkSignature($token);
          if($signature){
            $db = new DbConnect();
            $conn = $db->connect();

            $biodata_pasien = new BiodataPasien();

            $generate_kode = new Kode();

            //Mengambil data pada http request
            $request_body = file_get_contents('php://input');
            $json_data = json_decode($request_body, true);

            $nik = $json_data['nik'];

            $kd_pasien_exist = $biodata_pasien->NIKExist($nik);

            if($kd_pasien_exist == 1){
              //NIK exist
              $kd_pasien = $generate_kode->getKdPasien($nik);
            } else {
              //NIK not exist
              if($biodata_pasien->registerKdPasien($nik)){
                $kd_pasien = $generate_kode->getKdPasien($nik);
              }
            }

            $kd_unit = $generate_kode->getKdUnit($json_data['poli']);
            $kd_puskesmas = $json_data['kd_puskesmas'];

            /**
            * Koneksi ke tabel pelayanan
            */
            $data = array();

            $sql = "SELECT MAX(SUBSTR(KD_PELAYANAN,-7)) AS total FROM pelayanan WHERE KD_UNIT = '".$kd_unit."';";
            $result = $conn->query($sql);
            $row = $result->fetch_array();
            $kd_pelayanan = $kd_unit.sprintf("%07d", $row["total"]+1);

            $sql = "SELECT MAX(URUT_MASUK) AS total FROM pelayanan WHERE KD_PUSKESMAS = '".$kd_puskesmas."' AND TGL_PELAYANAN = '".$json_data['date']."';";
            $result = $conn->query($sql);
            $row = $result->fetch_array();
            $urut_masuk = $row["total"]+1;

            if(empty($json_data['rujukan'])){
              $status = "DILAYANI";
            } else {
              $status = "DIRUJUK";
            }

            $data[0] = $kd_pelayanan;
            $data[1] = $kd_puskesmas;
            $data[2] = $json_data['date'];
            $data[3] = "RJ";
            $data[4] = $kd_pasien;
            $data[5] = $kd_unit;
            $data[6] = $urut_masuk;
            $data[7] = $json_data['anamnesa'];
            $data[8] = $json_data['pelayanan'][0][0];
            $data[9] = $json_data['username'];
            $data[10] = $json_data['nama_dokter'];
            $data[11] = $status;
            $data[12] = 1;
            $data[13] = $json_data['nama_dokter'];
            $data[14] = $json_data['datetime'];
            for($i=15; $i<count($json_data['pelayanan'][0])+14; $i++){
              $data[$i] = $json_data['pelayanan'][0][$i-14];
            }

            $column_index = array(0,1,2,3,4,5,6,13,15,20,24,25,26,27,28,33,36,37,38,39);
            $insert_db = new InsertDb();
            $pelayanan = $insert_db->insertData($column_index, $data, "pelayanan");

            /**
            * Koneksi ke tabel kunjungan
            */
            $sql = "SELECT MAX(URUT_MASUK) AS total FROM kunjungan WHERE KD_PUSKESMAS = '".$kd_puskesmas."' AND KD_UNIT = '".$kd_unit."' AND TGL_MASUK = '".$json_data['date']."';";
            $result = $conn->query($sql);
            $row = $result->fetch_array();
            $urut_masuk = $row["total"]+1;
            $kd_kunjungan = $json_data['date']."-".$kd_unit."-".$urut_masuk;

            $data = array();
            $data[0] = $kd_kunjungan;
            $data[1] = $kd_pasien;
            $data[2] = $json_data['kd_puskesmas'];
            $data[3] = "PUSKESMAS";
            $data[4] = $kd_unit;
            $data[5] = $json_data['date'];
            $data[6] = $urut_masuk;
            $data[7] = $json_data['username'];
            $data[8] = "RJ";
            $data[9] = 1;
            $data[10] = $kd_pelayanan;

            $column_index = array(0,1,2,3,4,5,6,7,8,30,31);
            $kunjungan = $insert_db->insertData($column_index, $data, "kunjungan");

            /**
            * Koneksi ke tabel pelayanan_ket_tambahan
            */
            $data = array();
            $data[0] = $kd_pelayanan;
            for($i=1; $i<=count($json_data['pelayanan_ket_tambahan'][0]);$i++){
              $data[$i] = $json_data['pelayanan_ket_tambahan'][0][$i-1];
            }
            $data[27] = $json_data['nama_dokter'];
            $data[28] = $json_data['datetime'];

            $column_index = array();
            $pelayanan_ket_tambahan = $insert_db->insertData($column_index, $data, "pelayanan_ket_tambahan");

            if($kunjungan ==1 && $pelayanan == 1 && $pelayanan_ket_tambahan == 1){
              $response = array("code" => 216, "status" => "Update data berhasil");
            } else {
              $response = array("code" => 206, "status" => $kunjungan.$pelayanan.$pelayanan_ket_tambahan);
            }
          } else {
            $response = array("code" => 204, "status" => "Token not verified [sign]");
          }
        } else if($payload == 2){
          $response = array("code" => 206, "status" => "Payload not valid");
        } else if($payload == 3){
          $response = array("code" => 203, "status" => "Token expired");
        }
      } else {
        $response = array("code" => 104, "status" => "Token not verified [header]");
      }
    }
  } else {
    //Error jika request method yang digunakan bukan POST
    $response = array("code" => 205, "status" => "Metode yang digunakan tidak diizinkan");
  }

  //Menampilkan hasil REST service
  header('Content-type: application/json');
  echo json_encode($response);
?>
