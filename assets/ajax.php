<?php
session_start(); // KUNCI MUTLAK: Ini yang kamu lupakan! Harus ada agar ajax.php bisa membaca $_SESSION

// koneksi ke database
include '../assets/func.php';
$air = new kelas_air;
$koneksi = $air->koneksi(); // KUNCI MUTLAK: Ini yang kamu hilangkan!

if (isset($_POST['p'])) {
    $p = $_POST['p'];
   
    $user    = $_SESSION['user'];            // 1. Tangkap username dari brankas sesi
    $dt_user = $air->dt_user($user);         // 2. Minta func.php mencari profil lengkap user ini di database
    $level   = $dt_user[2];                  // 3. Ekstrak 'level' jabatannya dari array database

    if ($p == "summary") {
        $data = array();
        $bulan = $_POST['t'];

        // ==========================================
        // JALUR 1: ADMIN & PETUGAS
        // ==========================================
        if ($level == "admin" || $level == "petugas") {
            // 1. Total Pelanggan
            $q1 = mysqli_query($koneksi, "SELECT COUNT(username) AS jml_pelanggan FROM login WHERE level='warga'");
            $d1 = mysqli_fetch_assoc($q1);
            $data[0] = $d1;

            // 2. Total Pemakaian Air
            $q2 = mysqli_query($koneksi, "SELECT SUM(pemakaian) AS total_pemakaian FROM pemakaian WHERE tgl LIKE '$bulan%'");
            $d2 = mysqli_fetch_assoc($q2);
            // Format angka dengan titik (ribuan)
            $d2['total_pemakaian'] = empty($d2['total_pemakaian']) ? "0" : number_format($d2['total_pemakaian'], 0, ',', '.');
            $data[1] = $d2;

            // 3. Warga Sudah Dicatat
            $q3 = mysqli_query($koneksi, "SELECT COUNT(username) AS tercatat FROM pemakaian WHERE tgl LIKE '$bulan%'");
            $d3 = mysqli_fetch_assoc($q3);
            $data[2] = $d3;

            // 4. Warga Belum Dicatat
            $belum_dicatat = $d1['jml_pelanggan'] - $d3['tercatat'];
            $data[3] = array('belum_dicatat' => $belum_dicatat);
        }

        // ==========================================
        // JALUR 2: BENDAHARA
        // ==========================================
        elseif ($level == "bendahara") {
            // 1. Total Pelanggan (Sama dengan admin)
            $q1 = mysqli_query($koneksi, "SELECT COUNT(username) AS jml_pelanggan FROM login WHERE level='warga'");
            $d1 = mysqli_fetch_assoc($q1);
            $data[0] = $d1;

            // 2. Total Pemasukan (Tagihan Lunas)
            $q2 = mysqli_query($koneksi, "SELECT SUM(tagihan) AS total_pemasukan FROM pemakaian WHERE tgl LIKE '$bulan%' AND status='LUNAS'");
            $d2 = mysqli_fetch_assoc($q2);
            $pemasukan = empty($d2['total_pemasukan']) ? 0 : $d2['total_pemasukan'];
            $data[1] = array('total_pemasukan' => "Rp " . number_format($pemasukan, 0, ',', '.'));

            // 3. Warga Sudah Lunas
            $q3 = mysqli_query($koneksi, "SELECT COUNT(username) AS lunas FROM pemakaian WHERE tgl LIKE '$bulan%' AND status='LUNAS'");
            $d3 = mysqli_fetch_assoc($q3);
            $data[2] = $d3;

            // 4. Warga Belum Bayar
            $q4 = mysqli_query($koneksi, "SELECT COUNT(username) AS belum_bayar FROM pemakaian WHERE tgl LIKE '$bulan%' AND status='BELUM LUNAS'");
            $d4 = mysqli_fetch_assoc($q4);
            $data[3] = $d4;
        }

        // ==========================================
        // JALUR 3: WARGA
        // ==========================================
        elseif ($level == "warga") {
            // Karantina Data Mutlak: Ambil hanya milik user yang login pada bulan yang dipilih
            $q_warga = mysqli_query($koneksi, "SELECT tgl, waktu, pemakaian, tagihan, status FROM pemakaian WHERE username='$user' AND tgl LIKE '$bulan%'");
            
            if (mysqli_num_rows($q_warga) > 0) {
                $d_warga = mysqli_fetch_assoc($q_warga);
                $data[0] = array('info' => $air->tgl_balik($d_warga['tgl']) . " " . $d_warga['waktu']);
                $data[1] = array('info' => number_format($d_warga['pemakaian'], 0, ',', '.') . " m³");
                $data[2] = array('info' => "Rp " . number_format($d_warga['tagihan'], 0, ',', '.'));
                $data[3] = array('info' => $d_warga['status']);
            } else {
                // Jika bulan tersebut belum ada data
                $data[0] = array('info' => "Belum Terdaftar/Ter-input");
                $data[1] = array('info' => "- m³");
                $data[2] = array('info' => "Rp -");
                $data[3] = array('info' => "-");
            }
        }

        // Kirim semua array $data ke Javascript dalam bentuk JSON
        echo json_encode($data);
    }
 
        elseif ($p=="chart_bar") {
            $yuser=$_POST['y'];
            $response = array(); // Inisialisasi awal
            
            
        $q = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, pemakaian FROM pemakaian WHERE username='$yuser'");
            while ($d=mysqli_fetch_assoc($q)) {
                $response[]=$air->bln($d['bln']);   
                $response[]=$d['pemakaian'];

            }
            echo json_encode($response);

    }   elseif ($p=="chart_line") {
            $yuser=$_POST['y'];
            $response = array(); // Inisialisasi awal
            
            
        $q = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, tagihan FROM pemakaian WHERE username='$yuser'");
            while ($d=mysqli_fetch_assoc($q)) {
                $response[]=$air->bln($d['bln']);   
                $response[]=$d['tagihan'];

            }
            echo json_encode($response);
}
}
?>