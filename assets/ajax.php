<?php
session_start(); 

include '../assets/func.php';
$air = new kelas_air;
$koneksi = $air->koneksi(); 

$p = $_POST['p'];
$user    = $_SESSION['user'];            
$dt_user = $air->dt_user($user);         
$level   = $dt_user[2];                  

// ==========================================
// LOGIKA CERDAS: TANPA PILIH BULAN (DEFAULT)
// ==========================================
$bulan_post = isset($_POST['t']) ? $_POST['t'] : "";    
if ($bulan_post == "") {
    // Cari bulan dengan data pencatatan paling terakhir di database
    $q_last = mysqli_query($koneksi, "SELECT tgl FROM pemakaian ORDER BY tgl DESC LIMIT 1");
    if(mysqli_num_rows($q_last) > 0){
        $bulan_default = substr(mysqli_fetch_row($q_last)[0], 0, 7); // Format YYYY-MM
    } else {
        $bulan_default = date("Y-m");
    }
} else {
    $bulan_default = $bulan_post;
}

if ($p == "summary") {
    $data = array();
    
    if ($level == "admin" || $level == "petugas") {
        $q1 = mysqli_query($koneksi, "SELECT COUNT(username) AS jml_pelanggan FROM login WHERE level='warga'");
        $d1 = mysqli_fetch_assoc($q1); $data[0] = $d1;

        $like_bulan = $bulan_default . "%";
        $stmt = mysqli_prepare($koneksi, "SELECT SUM(pemakaian) AS total_pemakaian FROM pemakaian WHERE tgl LIKE ?");
        mysqli_stmt_bind_param($stmt, "s", $like_bulan);
        mysqli_stmt_execute($stmt);
        $q2 = mysqli_stmt_get_result($stmt);
        $d2 = mysqli_fetch_assoc($q2);
        $d2['total_pemakaian'] = empty($d2['total_pemakaian']) ? "0" : number_format($d2['total_pemakaian'], 0, ',', '.');
        $data[1] = $d2;

        $stmt = mysqli_prepare($koneksi, "SELECT COUNT(username) AS tercatat FROM pemakaian WHERE tgl LIKE ?");
        mysqli_stmt_bind_param($stmt, "s", $like_bulan);
        mysqli_stmt_execute($stmt);
        $q3 = mysqli_stmt_get_result($stmt);
        $d3 = mysqli_fetch_assoc($q3); $data[2] = $d3;

        $data[3] = array('belum_dicatat' => ($d1['jml_pelanggan'] - $d3['tercatat']));
    }
    elseif ($level == "bendahara") {
        $q1 = mysqli_query($koneksi, "SELECT COUNT(username) AS jml_pelanggan FROM login WHERE level='warga'");
        $data[0] = mysqli_fetch_assoc($q1);

        // PENYELAMATAN KORUPSI DATA: Ambil data ke variabel $d2 sekali saja!
        $like_bulan = $bulan_default . "%";
        $stmt = mysqli_prepare($koneksi, "SELECT SUM(tagihan) AS total_pemasukan FROM pemakaian WHERE tgl LIKE ? AND status='LUNAS'");
        mysqli_stmt_bind_param($stmt, "s", $like_bulan);
        mysqli_stmt_execute($stmt);
        $q2 = mysqli_stmt_get_result($stmt);
        $d2 = mysqli_fetch_assoc($q2);
        $pemasukan = empty($d2['total_pemasukan']) ? 0 : $d2['total_pemasukan'];
        $data[1] = array('total_pemasukan' => "Rp " . number_format($pemasukan, 0, ',', '.'));

        $stmt = mysqli_prepare($koneksi, "SELECT COUNT(username) AS lunas FROM pemakaian WHERE tgl LIKE ? AND status='LUNAS'");
        mysqli_stmt_bind_param($stmt, "s", $like_bulan);
        mysqli_stmt_execute($stmt);
        $q3 = mysqli_stmt_get_result($stmt);
        $data[2] = mysqli_fetch_assoc($q3);

        $stmt = mysqli_prepare($koneksi, "SELECT COUNT(username) AS belum_bayar FROM pemakaian WHERE tgl LIKE ? AND status='BELUM LUNAS'");
        mysqli_stmt_bind_param($stmt, "s", $like_bulan);
        mysqli_stmt_execute($stmt);
        $q4 = mysqli_stmt_get_result($stmt);
        $data[3] = mysqli_fetch_assoc($q4);
    }
    elseif ($level == "warga") {
        if ($bulan_post == "") {
            $stmt = mysqli_prepare($koneksi, "SELECT tgl, waktu, pemakaian, tagihan, status FROM pemakaian WHERE username=? ORDER BY tgl DESC, waktu DESC LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $user);
        } else {
            $like_bulan_post = $bulan_post . "%";
            $stmt = mysqli_prepare($koneksi, "SELECT tgl, waktu, pemakaian, tagihan, status FROM pemakaian WHERE username=? AND tgl LIKE ?");
            mysqli_stmt_bind_param($stmt, "ss", $user, $like_bulan_post);
        }
        mysqli_stmt_execute($stmt);
        $q_warga = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($q_warga) > 0) {
            $d_warga = mysqli_fetch_assoc($q_warga);
            $data[0] = array('info' => $air->tgl_balik($d_warga['tgl']) . " " . $d_warga['waktu']);
            $data[1] = array('info' => number_format($d_warga['pemakaian'], 0, ',', '.') . " m³");
            $data[2] = array('info' => "Rp " . number_format($d_warga['tagihan'], 0, ',', '.'));
            $data[3] = array('info' => $d_warga['status']);
        } else {
            $data[0] = array('info' => "Belum Terdaftar/Ter-input");
            $data[1] = array('info' => "- m³");
            $data[2] = array('info' => "Rp -");
            $data[3] = array('info' => "-");
        }
    }
    echo json_encode($data);
}

// ==========================================
// PELADEN SEMUA CHART SEKALIGUS (LOGIKA POL KONEKSI 12 BULAN UTUH)
// ==========================================
elseif ($p == "semua_chart") {
    $response = array();
    
    if ($level == "admin" || $level == "bendahara" || $level == "petugas") {
        
        // A. Pemakaian Total (Cetakan Kue 12 Bulan)
        $data_pemakaian = array_fill(1, 12, 0); // Isi bulan 1-12 dengan angka 0
        $q_pemakaian = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, SUM(pemakaian) as total FROM pemakaian GROUP BY MONTH(tgl)");
        while($d = mysqli_fetch_assoc($q_pemakaian)){ $data_pemakaian[(int)$d['bln']] = $d['total']; }
        
        $arr_pemakaian = [];
        for($m=1; $m<=12; $m++){ $arr_pemakaian[] = $air->bln($m); $arr_pemakaian[] = $data_pemakaian[$m]; }
        $response['pemakaian'] = $arr_pemakaian;

        // B. Tipe Pelanggan Kos/RT (Pie) - Profil tidak pakai linimasa bulan
        $q_tipe = mysqli_query($koneksi, "SELECT tipe, COUNT(username) as jml FROM login WHERE level='warga' GROUP BY tipe");
        $arr_tipe = [];
        while($d = mysqli_fetch_assoc($q_tipe)){ $arr_tipe[] = $d['tipe']; $arr_tipe[] = $d['jml']; }
        $response['tipe_pelanggan'] = $arr_tipe;

        // C. Tercatat & Belum Tercatat (Cetakan Kue 12 Bulan)
        $tot_warga = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(username) as tot FROM login WHERE level='warga'"))['tot'];
        $data_catat = array_fill(1, 12, 0);
        $q_catat = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, COUNT(username) as jml FROM pemakaian GROUP BY MONTH(tgl)");
        while($d = mysqli_fetch_assoc($q_catat)){ $data_catat[(int)$d['bln']] = $d['jml']; }
        
        $arr_tercatat = []; $arr_belum = [];
        for($m=1; $m<=12; $m++){
            $arr_tercatat[] = $air->bln($m); $arr_tercatat[] = $data_catat[$m];
            $arr_belum[] = $air->bln($m); $arr_belum[] = $tot_warga - $data_catat[$m];
        }
        $response['tercatat'] = $arr_tercatat; $response['belum_tercatat'] = $arr_belum;
        
        if ($level == "admin" || $level == "bendahara") {
            // D. Tagihan Per Bulan
            $data_tagihan = array_fill(1, 12, 0);
            $q_tagihan = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, SUM(tagihan) as total FROM pemakaian GROUP BY MONTH(tgl)");
            while($d = mysqli_fetch_assoc($q_tagihan)){ $data_tagihan[(int)$d['bln']] = $d['total']; }
            $arr_tagihan = [];
            for($m=1; $m<=12; $m++){ $arr_tagihan[] = $air->bln($m); $arr_tagihan[] = $data_tagihan[$m]; }
            $response['tagihan'] = $arr_tagihan;

            // E. Pemasukan Lunas per Bulan
            $data_pemasukan = array_fill(1, 12, 0);
            $q_lunas_sum = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, SUM(tagihan) as total FROM pemakaian WHERE status='LUNAS' GROUP BY MONTH(tgl)");
            while($d = mysqli_fetch_assoc($q_lunas_sum)){ $data_pemasukan[(int)$d['bln']] = $d['total']; }
            $arr_pemasukan = [];
            for($m=1; $m<=12; $m++){ $arr_pemasukan[] = $air->bln($m); $arr_pemasukan[] = $data_pemasukan[$m]; }
            $response['pemasukan'] = $arr_pemasukan;

            // F. Jumlah Warga Sudah Lunas
            $data_sdh = array_fill(1, 12, 0);
            $q_sdh = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, COUNT(username) as jml FROM pemakaian WHERE status='LUNAS' GROUP BY MONTH(tgl)");
            while($d = mysqli_fetch_assoc($q_sdh)){ $data_sdh[(int)$d['bln']] = $d['jml']; }
            $arr_sdh = [];
            for($m=1; $m<=12; $m++){ $arr_sdh[] = $air->bln($m); $arr_sdh[] = $data_sdh[$m]; }
            $response['sdh_lunas'] = $arr_sdh;

            // G. Jumlah Warga Belum Lunas
            $data_blm = array_fill(1, 12, 0);
            $q_blm = mysqli_query($koneksi, "SELECT MONTH(tgl) as bln, COUNT(username) as jml FROM pemakaian WHERE status='BELUM LUNAS' GROUP BY MONTH(tgl)");
            while($d = mysqli_fetch_assoc($q_blm)){ $data_blm[(int)$d['bln']] = $d['jml']; }
            $arr_blm = [];
            for($m=1; $m<=12; $m++){ $arr_blm[] = $air->bln($m); $arr_blm[] = $data_blm[$m]; }
            $response['blm_lunas'] = $arr_blm;
        }
    } 
    elseif ($level == "warga") {
        // H. Cetakan Grafik Khusus Warga Pribadi
        $data_pake_w = array_fill(1, 12, 0);
        $data_tag_w = array_fill(1, 12, 0);
        $stmt = mysqli_prepare($koneksi, "SELECT MONTH(tgl) as bln, pemakaian, tagihan FROM pemakaian WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $user);
        mysqli_stmt_execute($stmt);
        $q_warga_chart = mysqli_stmt_get_result($stmt);
        while($d = mysqli_fetch_assoc($q_warga_chart)){ 
            $data_pake_w[(int)$d['bln']] = $d['pemakaian']; 
            $data_tag_w[(int)$d['bln']] = $d['tagihan']; 
        }
        
        $arr_pemakaian_warga = []; $arr_tagihan_warga = [];
        for($m=1; $m<=12; $m++){
            $bln_nama = $air->bln($m);
            $arr_pemakaian_warga[] = $bln_nama; $arr_pemakaian_warga[] = $data_pake_w[$m];
            $arr_tagihan_warga[] = $bln_nama; $arr_tagihan_warga[] = $data_tag_w[$m];
        }
        $response['pemakaian_warga'] = $arr_pemakaian_warga;
        $response['tagihan_warga'] = $arr_tagihan_warga;
    }

    echo json_encode($response);
}
?>