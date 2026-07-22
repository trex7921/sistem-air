<?php
class kelas_air {
    
    // 1. Buat brankas penyimpan status koneksi (Nilai awalnya kosong)
    public $kon = null; 

    function koneksi() {
        // 2. Jika brankas masih kosong (belum ada koneksi), BUKA KONEKSI BARU
        if ($this->kon == null) {
            // Kredensial dari environment variable kalau di-set, fallback ke nilai lama kalau belum
            // (gak maksa ubah setup server yang udah jalan, tapi buka jalan migrasi ke env var).
            $db_host = getenv('DB_HOST') ?: "127.0.0.1";
            $db_user = getenv('DB_USER') ?: "root";
            $db_pass = getenv('DB_PASS') ?: "";
            $db_name = getenv('DB_NAME') ?: "tkbmyid_db_kel01";

            try {
                $this->kon = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
            } catch (mysqli_sql_exception $ex) {
                $this->kon = false;
                error_log("Koneksi DB gagal: " . $ex->getMessage());
            }

            // JARING PENGAMAN: kalau DB mati, jangan biarin error mentah (isi path server, detail koneksi) nyampe ke browser publik.
            if (!$this->kon) {
                http_response_code(503);
                die("Sistem sedang gangguan. Coba lagi beberapa saat lagi.");
            }

            // Opsional tapi penting untuk keamanan charset
            mysqli_set_charset($this->kon, "utf8mb4");
        }
        
        // 3. Jika brankas sudah ada isinya, KEMBALIKAN KONEKSI YANG SAMA
        return $this->kon;
    }

    function dt_user($sesi_user) {
        $stmt = mysqli_prepare($this->koneksi(), "SELECT nama,kota,level FROM login WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $sesi_user);
        mysqli_stmt_execute($stmt);
        $q = mysqli_stmt_get_result($stmt);
        $d = mysqli_fetch_row($q);
        // JARING PENGAMAN: username sesi gak ketemu di DB (misal akun dihapus pas sesi masih aktif)
        // -> return array kosong 3 slot, biar $dt_user[2] gak Fatal Error, level jadi null (otomatis gagal semua cek role)
        if (!$d) return array(null, null, null);
        return $d;
    }

    function user_to_idtarif($username) {
        $stmt = mysqli_prepare($this->koneksi(), "SELECT tipe FROM login WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $q = mysqli_stmt_get_result($stmt);
        $d = mysqli_fetch_row($q);
        // JARING PENGAMAN
        if (!$d) return 0;
        $tipe = $d[0];

        $kd_tarif = $this->tipe_to_kdtarif($tipe);
        return $kd_tarif;
    }

    function tipe_to_kdtarif($tipe) {
        $stmt = mysqli_prepare($this->koneksi(), "SELECT id_tarif, tarif FROM tarif WHERE tipe_tarif=? AND status='AKTIF'");
        mysqli_stmt_bind_param($stmt, "s", $tipe);
        mysqli_stmt_execute($stmt);
        $q = mysqli_stmt_get_result($stmt);
        $d = mysqli_fetch_row($q);
        // JARING PENGAMAN: tipe tarif gak ketemu/gak aktif -> 0, bukan crash
        if (!$d) return 0;
        return $d[0];
    }

    function id_tarif_to_tarif($id_tarif) {
        $stmt = mysqli_prepare($this->koneksi(), "SELECT tarif FROM tarif WHERE id_tarif=? AND status='AKTIF'");
        mysqli_stmt_bind_param($stmt, "s", $id_tarif);
        mysqli_stmt_execute($stmt);
        $q = mysqli_stmt_get_result($stmt);
        $d = mysqli_fetch_row($q);
        // JARING PENGAMAN
        if (!$d) return 0;
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
        $d = mysqli_fetch_row($q);
        // JARING PENGAMAN: nomor meter gak ketemu -> null, caller wajib cek ini
        if (!$d) return null;
        return $d[0];
    }

    // FITUR BARU: rekam jejak. Satu tabel serbaguna buat semua aksi tulis (bukan tabel per fitur),
    // dipanggil dari tiap handler yang INSERT/UPDATE/DELETE data penting. $actor & $actor_level
    // WAJIB dari $_SESSION, jangan pernah dari input form -- kalau dari form, orang bisa palsuin "siapa yang ubah".
    function catat_log($actor, $actor_level, $aksi, $tabel_target, $id_target, $detail) {
        $stmt = mysqli_prepare($this->koneksi(), "INSERT INTO audit_log (actor_username, actor_level, aksi, tabel_target, id_target, detail, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "ssssss", $actor, $actor_level, $aksi, $tabel_target, $id_target, $detail);
        mysqli_stmt_execute($stmt);
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
