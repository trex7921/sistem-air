<?php
class kelas_air {
    
    // 1. Buat brankas penyimpan status koneksi (Nilai awalnya kosong)
    public $kon = null; 

    function koneksi() {
        // 2. Jika brankas masih kosong (belum ada koneksi), BUKA KONEKSI BARU
        if ($this->kon == null) {
            $this->kon = mysqli_connect("127.0.0.1", "root", "", "tkbmyid_db_kel01");
            
            // Opsional tapi penting untuk keamanan charset
            mysqli_set_charset($this->kon, "utf8mb4");
        }
        
        // 3. Jika brankas sudah ada isinya, KEMBALIKAN KONEKSI YANG SAMA
        return $this->kon;
    }

    // ... (Fungsi dt_user, dt_tarif, tgl_balik, dll BIARKAN SAMA PERSIS, JANGAN DIUBAH)

    function dt_user($sesi_user) {
        $stmt = mysqli_prepare($this->koneksi(), "SELECT nama,kota,level FROM login WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $sesi_user);
        mysqli_stmt_execute($stmt);
        $q = mysqli_stmt_get_result($stmt);
        $d=mysqli_fetch_row($q);
        return $d;
    }

    function user_to_idtarif($username) {
        $stmt = mysqli_prepare($this->koneksi(), "SELECT tipe FROM login WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $q = mysqli_stmt_get_result($stmt);
        $d=mysqli_fetch_row($q);
        $tipe = $d[0];
    
        $kd_tarif=$this->tipe_to_kdtarif($tipe);
        return $kd_tarif;
    }

    function tipe_to_kdtarif($tipe) {
        $stmt = mysqli_prepare($this->koneksi(), "SELECT id_tarif, tarif FROM tarif WHERE tipe_tarif=? AND status='AKTIF'");
        mysqli_stmt_bind_param($stmt, "s", $tipe);
        mysqli_stmt_execute($stmt);
        $q = mysqli_stmt_get_result($stmt);
        $d=mysqli_fetch_row($q);
        return $d[0];
       
    }
    function id_tarif_to_tarif ($id_tarif) {
        $stmt = mysqli_prepare($this->koneksi(), "SELECT tarif FROM tarif WHERE id_tarif=? AND status='AKTIF'");
        mysqli_stmt_bind_param($stmt, "s", $id_tarif);
        mysqli_stmt_execute($stmt);
        $q = mysqli_stmt_get_result($stmt);
        $d=mysqli_fetch_row($q);
        return $d[0];
    }
    function tgl_balik($tgl) {
        $e=explode("-",$tgl);
        $tgl_balik=$e[2]."-".$e[1]."-".$e[0];
        return $tgl_balik;
  }
  function no_to_user($no) {
    $stmt = mysqli_prepare($this->koneksi(), "SELECT username FROM pemakaian WHERE no=?");
    mysqli_stmt_bind_param($stmt, "i", $no);
    mysqli_stmt_execute($stmt);
    $q = mysqli_stmt_get_result($stmt);
    $d=mysqli_fetch_row($q);
    return $d[0];
  }
  function bln($no) {
    if($no==1) $bln="Januari";
    elseif($no==2) $bln="Februari";
    elseif($no==3) $bln="Maret";
    elseif($no==4) $bln="April";
    elseif($no==5) $bln="Mei";
    elseif($no==6) $bln="Juni";
    elseif($no==7) $bln="Juli";
    elseif($no==8) $bln="Agustus";
    elseif($no==9) $bln="September";
    elseif($no==10) $bln="Oktober";
    elseif($no==11) $bln="November";
    else
        $bln="Desember";
    return $bln;
  }
}
?>