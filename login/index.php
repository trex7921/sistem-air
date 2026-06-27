<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
if(empty($_SESSION['user']) && empty($_SESSION['pass'])) {
    echo "<script>window.location.replace('../index.php')</script>";
}

// koneksi ke database
include '../assets/func.php';
$air = new kelas_air;
$koneksi = $air->koneksi();
$koneksi = $air->koneksi();
$dt_user=$air->dt_user($_SESSION['user']);
$level=$dt_user[2];

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
        
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="../js/air.js"></script>
    </head>
    </head>
    <script>
        var level_user = "<?php echo $level; ?>";
        var user = "<?php echo $_SESSION['user']; ?>";    
    </script>
    <body class="sb-nav-fixed">
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.php" style="font-family: 'Poppins', sans-serif; font-size: 1.2rem; letter-spacing: 1px;">Sistem Air Kel.01</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Settings</a></li>
                        <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Core</div>
                            <a class="nav-link" href="index.php?p=dashboard">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt fa-spin text-danger"></i></div>
                                Dashboard
                            </a>

                            <?php
                            if($level=="admin") {
                                ?>
                            <a class="nav-link" href="index.php?p=user">
                            <div class="sb-nav-link-icon"><i class="fa-regular fa-user fa-flip text-info"></i></div>
                                Manajemen User
                            </a>
                             <a class="nav-link" href="index.php?p=catat_edit_meter">
                             <div class="sb-nav-link-icon"><i class="fa-solid  fa-file-invoice  fa-shake text-warning"></i></div>
                                Pemakaian Warga
                            </a>
                            </a>
                             <a class="nav-link" href="index.php?p=manajemen_tarif_air">
                             <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-wave fa-beat text-success"></i></div>
                                Tarif Air Warga
                            </a>
                            </a>
                             <!-- <a class="nav-link" href="index.php?p=infografis_warga">
                             <div class="sb-nav-link-icon"><i class="fa-regular fa-chart-bar fa-fade text-danger"></i></div>
                                Infografis Warga
                            </a> -->
                            <?php 
                            } elseif($level=="bendahara") {
                                ?>
                             <a class="nav-link" href="index.php?p=catat_edit_meter">
                             <div class="sb-nav-link-icon"><i class="fa-solid  fa-file-invoice  fa-shake text-warning"></i></div>
                                Pemakaian Warga
                            </a>
                            <!-- </a>
                             <a class="nav-link" href="index.php?p=tagihan_warga">
                             <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-wave fa-beat text-success"></i></div>
                                Tagihan Warga
                            </a> -->
                            </a>
                              <a class="nav-link" href="index.php?p=manajemen_tarif_air">
                             <div class="sb-nav-link-icon"><i class="fa-solid fa-hand fa-shake text-success"></i></div>
                                Manajemen Tarif Air
                            </a>
                            </a>
                             <!-- <a class="nav-link" href="index.php?p=infografis_tagihan_warga">
                             <div class="sb-nav-link-icon"><i class="fa-solid fa-eye fa-beat-fade text-info"></i></div>
                                Infografis Tagihan Warga
                            </a> -->
                            </a>
                           
                            <?php 
                            } elseif($level == "petugas") { 
                                ?>
                                <a class="nav-link" href="index.php?p=catat_edit_meter">
                                    <div class="sb-nav-link-icon"><i class="fas fa-keyboard fa-bounce  text-warning"></i></div>
                                    Catat & Edit Meter
                                </a>
                                <!-- <a class="nav-link" href="index.php?p=info_pelanggan">
                                    <div class="sb-nav-link-icon"><i class="fas fa-users fa-flip text-info"></i></div>
                                    Total Pelanggan
                                </a>
                                <a class="nav-link" href="index.php?p=infografis_pemakaian">
                                    <div class="sb-nav-link-icon"><i class="fas fa-chart-bar fa-beat text-success"></i></div>
                                    Infografis Pemakaian Warga
                                </a> -->
                                <?php 
                                } elseif($level == "warga") { 
                                    ?>
                            <a class="nav-link" href="index.php?p=pantau_pemakaian">
                                <div class="sb-nav-link-icon"><i class="fas fa-tint fa-bounce text-primary"></i></div>
                                Pantau Pemakaian Air
                            </a>
                            <!-- <a class="nav-link" href="index.php?p=tagihan_saya">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-invoice fa-shake text-success"></i></div>
                                Tagihan Saya
                            </a> -->
                            <!-- <a class="nav-link" href="index.php?p=infografis_warga">
                                <div class="sb-nav-link-icon"><i class="fas fa-chart-area fa-beat text-info"></i></div>
                                Infografis Saya
                            </a> -->
                            <?php
                            }
                            ?>
                            
                            
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                         <div class="small"><i class="fa-regular fa-user fa-flip text-danger"></i> Logged in as : <?php echo $dt_user[2]?></div>
                        <?php echo $dt_user [0]. ' (' .$dt_user [1]. ')'?>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <?php
                       // echo $_SERVER['REQUEST_URI'];
                        $e=explode("=", $_SERVER['REQUEST_URI']);
                        // echo "<BR>[0]: $e[0] --> [1]: $e[1]";
                        
                        if(!empty($e[1])) {
                            // --- BLOK ADMIN & GLOBAL ---
                            if($e[1]=="dashboard") {
                                $h1="Dashboard Si-Air";
                                $li="Selamat Datang di Dashboard Si-Air Kel 01";
                            }
                            elseif($e[1]=="user" || $e[1]=="user_edit&user") {
                                $h1="Manajemen User";
                                $li="Menu untuk CRUD User";
                            }
                            elseif($e[1]=="pemakaian_warga") {
                                $h1="Pemakaian Warga";
                                $li="Data Pemakaian Air Warga";
                            }
                            elseif($e[1]=="manajemen_tarif_air" || $e[1]=="tarif_edit&id_tarif") {
                                $h1="Tarif Warga";
                                $li="Lihat dan Edit Tarif Air Seluruh Warga";
                            }
                            elseif($e[1]=="infografis_warga") {
                                $h1="Infografis Warga";
                                $li="Visualisasi Grafik Pemakaian dan Tagihan Air Warga";
                            }
                            elseif($e[1]=="manajemen_tarif_air") {
                                $h1="Manajemen Tarif Air";
                                $li="Pengaturan Harga Tarif Air per Kubik";
                            }

                            // --- BLOK BENDAHARA ---
                            elseif($e[1]=="ubah_datameter_warga") {
                                $h1="Ubah Data Meter Warga";
                                $li="Halaman untuk mengubah data meter warga";
                            }
                            elseif($e[1]=="infografis_tagihan_warga") {
                                $h1="Infografis Tagihan Warga";
                                $li="Visualisasi Grafik Tagihan Air Warga";
                            }
                            elseif($e[1]=="tagihan_warga") {
                                $h1="Tagihan Warga";
                                $li="Tagihan Air Bulanan Warga";
                            }

                            // --- BLOK PETUGAS ---
                            elseif($e[1]=="catat_edit_meter" || $e[1]=="meter_edit&no") {
                                $h1="Catat & Edit Data Meter";
                                $li="Masukkan Angka Meteran Bulan Ini";
                            }
                            // elseif($e[1]=="info_pelanggan") {
                            //     $h1="Total Pelanggan";
                            //     $li="Informasi Jumlah Pelanggan dan Pemakaian";
                            // }
                            // elseif($e[1]=="infografis_pemakaian") {
                            //     $h1="Infografis Pemakaian";
                            //     $li="Grafik Total Pemakaian Air Warga";
                            // }

                            // --- BLOK WARGA ---
                            elseif($e[1]=="pantau_pemakaian") {
                                $h1="Pantau Pemakaian";
                                $li="Riwayat Pemakaian Air Anda";
                            }
                            elseif($e[1]=="tagihan_saya") {
                                $h1="Tagihan Saya";
                                $li="Rincian Tagihan Air Bulanan Anda";
                            }
                            elseif($e[1]=="infografis_warga") {
                                $h1="Infografis Saya";
                                $li="Grafik Pemakaian dan Tagihan Air Anda";
                            }
                            
                            // --- JARING PENGAMAN (PENTING) ---
                            else {
                                $h1="Halaman Tidak Ditemukan";
                                $li="Menu yang Anda cari tidak ada.";
                            }
                        }
                        else{
                            $h1="Dashboard Si-Air";
                            $li="Selamat Datang di Dashboard Si-Air Kel 01";
                        }
                        ?>
                        <h1 class="mt-4"><?php echo $h1 ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active"><?php echo $li ?></li>
                        </ol>
                         <?php
                            if(isset($_POST['tombol'])) {
                                $t=$_POST['tombol'];

                                // echo "<div class='alert alert-warning text-center fs-4' style='z-index:9999;'>TOMBOL YANG DITERIMA PHP ADALAH: <b>" . htmlspecialchars($t) . "</b></div>";
                                if($t=="user_add") {

                                    $user=$_POST['yuser'];
                                    $pass=password_hash($_POST['passwet'], PASSWORD_DEFAULT);
                                    $pass2=$_POST['passwet'];
                                    $nama=$_POST['nama'];
                                    $alamat=$_POST['alamat'];
                                    $kota=$_POST['kota'];
                                    $tlp=$_POST['tlp'];
                                    $level=$_POST['level'];
                                    $tipe=$_POST['tipe'];
                                    $status=$_POST['status'];

                                    //cek sudah ada atau belum di tabel user
                                    $qc=mysqli_query($koneksi,"SELECT username FROM login WHERE username='$user'");
                                    $qj=mysqli_num_rows($qc);
                                    // echo "hasil cek user: $qj";
                                    if (empty($qj)) {
                                        mysqli_query($koneksi, "INSERT INTO login (username, password, nama, alamat, kota, tlp, level, tipe, status) VALUES ('$user','$pass',\"$nama\",'$alamat','$kota','$tlp','$level','$tipe','$status')");
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            echo "<div class='alert alert-success alert-dismissible fade show'>
                                                    <button type=button class=btn-close data-bs-dismiss=alert></button>
                                                    <strong>Data</strong> BERHASIL masuk lekk...                                             
                                                </div>";
                                       } else {
                                           echo "<div class='alert alert-danger alert-dismissible fade show'>
                                                    <button type=button class=btn-close data-bs-dismiss=alert></button>
                                                    <strong>Data</strong> GAGAL masuk lekk...                                             
                                                </div>"; 
                                             }        
                                        }
                                    else { //username kembar
                                        echo "<div class='alert alert-danger alert-dismissible fade show'>
                                                <button type=button class=btn-close data-bs-dismiss=alert></button>
                                                <strong>Username</strong> SUDAH digunakan!...
                                            </div>";

                                    }    
                                    } elseif($t=="user_edit") {

                                        $user   = $_POST['yuser'];
                                        $pass   = $_POST['passwet'];
                                        $nama   = $_POST['nama'];
                                        $alamat = $_POST['alamat'];
                                        $kota   = $_POST['kota'];
                                        $tlp    = $_POST['tlp'];
                                        $level  = $_POST['level'];
                                        $tipe   = $_POST['tipe'];
                                        $status = $_POST['status'];

                                        // CEK PASSWORD LAMA DI DATABASE [cite: 2]
                                        $q_cp = mysqli_query($koneksi, "SELECT password FROM login WHERE username='$user'");
                                        $d_cp = mysqli_fetch_row($q_cp);
                                        $pass_db = $d_cp[0]; // [cite: 3]

                                        // LOGIKA PERCABANGAN PASSWORD [cite: 3]
                                        if($pass == $pass_db) {
                                            // Jika tidak ada perubahan password
                                            mysqli_query($koneksi, "UPDATE login SET nama='$nama', alamat='$alamat', kota='$kota', tlp='$tlp', level='$level', tipe='$tipe', status='$status' WHERE username='$user'");
                                        } else {
                                            // Jika password diubah, lakukan hashing [cite: 3]
                                            $pass2 = password_hash($pass, PASSWORD_DEFAULT);
                                            mysqli_query($koneksi, "UPDATE login SET password='$pass2', nama='$nama', alamat='$alamat', kota='$kota', tlp='$tlp', level='$level', tipe='$tipe', status='$status' WHERE username='$user'");
                                        }

                                        // ALERT NOTIFIKASI
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button><strong>Data</strong> BERHASIL di UPDATE lekk...</div>";
                                        } else {
                                            echo "<div class='alert alert-primary alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button><strong>Data</strong> tidak ada perubahan.</div>"; // [cite: 5]
                                        }
               
                                        } elseif($t=="user_hapus") { // 
                                        $user = $_POST['yuser']; // 
                                        
                                        // Perintah saklar hapus dari database
                                        mysqli_query($koneksi, "DELETE FROM login WHERE username='$user'"); // 
                                        
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button><strong>User</strong> BERHASIL dihapus lekk...</div>";
                                        } else {
                                            echo "<div class='alert alert-danger alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button><strong>User</strong> GAGAL dihapus lekk...</div>"; // 
                                        }
                                    
                                    
                                        } elseif($t == "tarif_add") {
                                        $id_tarif   = $_POST['id_tarif'];
                                        $tipe_tarif = $_POST['tipe_tarif'];
                                        $tarif      = $_POST['tarif'];
                                        $status     = $_POST['status']; 

                                        // Cek apakah ID Tarif sudah ada agar tidak error duplikat
                                        $qc_t = mysqli_query($koneksi,"SELECT id_tarif FROM tarif WHERE id_tarif='$id_tarif'");
                                        if (mysqli_num_rows($qc_t) == 0) {
                                            mysqli_query($koneksi, "INSERT INTO tarif (id_tarif, tipe_tarif, tarif, status) VALUES ('$id_tarif', '$tipe_tarif', '$tarif', '$status')");
                                            
                                            if (mysqli_affected_rows($koneksi) > 0) {
                                                echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif BERHASIL ditambahkan.</div>";
                                            } else {
                                                echo "<div class='alert alert-danger alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif GAGAL ditambahkan.</div>";
                                            }
                                        } else {
                                            echo "<div class='alert alert-warning alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>ID Tarif SUDAH DIGUNAKAN!</div>";
                                        }

                                    
                                      } elseif($t == "tarif_edit") {
                                        // Perhatikan: ID diambil dari input hidden (id_tarif_lama) yang disuntikkan oleh JS
                                        $id_tarif      = $_POST['id_tarif_lama']; 
                                        $tipe_tarif    = $_POST['tipe_tarif'];
                                        $tarif         = $_POST['tarif'];
                                        $status        = $_POST['status']; 

                                        mysqli_query($koneksi, "UPDATE tarif SET tipe_tarif='$tipe_tarif', tarif='$tarif', status='$status' WHERE id_tarif='$id_tarif'");
                                        
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif BERHASIL diubah lekk....</div>";
                                        } else {
                                            echo "<div class='alert alert-primary alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif tidak ada perubahan lekk...</div>"; 
                                        }

                                    
                                       } elseif($t == "tarif_hapus") { 
                                        $id_tarif = $_POST['id_tarif']; 
                                        
                                        mysqli_query($koneksi, "DELETE FROM tarif WHERE id_tarif='$id_tarif'"); 
                                        
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif BERHASIL dihapus lekk...</div>";
                                        } else {
                                            echo "<div class='alert alert-danger alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif GAGAL dihapus lekk...</div>"; 
                                        }
                                    } elseif($t == "meter_add") {
                                        $username       = $_POST['username'];
                                        $meter_awal     = $_POST['meter_awal'];
                                        $meter_akhir    = $_POST['meter_akhir'];
                                        $kd_tarif       = $air->user_to_idtarif($username);
                                        $tarif          = $air->id_tarif_to_tarif($kd_tarif);
                                        $status = $_POST['status_bayar'];
                                        $bln_skrg = date('m');
                                        $thn_skrg = date('Y');
                                        

                                        // Cek meter awal dan akhir valid (akhir harus lebih besar dari awal)
                                        $pemakaian = $meter_akhir - $meter_awal;
                                        $tagihan        = $tarif * $pemakaian;

                                        // kalau pemakaian 0, auto lunas
                                        if ($pemakaian == 0) {
                                            $status = "LUNAS";
                                        }
                                        // sebulan input sekali
                                      // KUNCI MUTLAK: CEK DUPLIKAS I BULAN INI TERLEBIH DAHULU
                                        $q_cek_bulan = mysqli_query($koneksi, "SELECT no FROM pemakaian WHERE username='$username' AND MONTH(tgl) = '$bln_skrg' AND YEAR(tgl) = '$thn_skrg'");
                                        // MESIN HANYA AKAN MEMILIH SALAH SATU DARI 3 KONDISI INI:
                                        if (mysqli_num_rows($q_cek_bulan) > 0) {
                                            // Kondisi 1: Ditolak karena bulan ini sudah ada
                                            echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>GAGAL: Warga ini <b>sudah dicatat</b> meternya pada bulan ini!</div>";
                                        } 
                                        elseif ($pemakaian < 0) { 
                                            // Kondisi 2: Ditolak karena meteran minus
                                            echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>GAGAL: Meter Akhir harus lebih besar dari Meter Awal!</div>";
                                        } 
                                        else {
                                            // Kondisi 3: Aman, simpan ke database
                                            mysqli_query($koneksi, "INSERT INTO pemakaian (username, meter_awal, meter_akhir, pemakaian, tgl, waktu, kd_tarif, tagihan, status) VALUES ('$username', '$meter_awal', '$meter_akhir', '$pemakaian', CURRENT_DATE(), CURRENT_TIME(), '$kd_tarif', '$tagihan', '$status')");

                                            if (mysqli_affected_rows($koneksi) > 0) {
                                                echo "<div class='alert alert-success alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Meter BERHASIL ditambahkan.</div>";
                                                unset($_POST['username'], $_POST['meter_awal'], $_POST['meter_akhir']); 
                                            } else {
                                                echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Meter GAGAL ditambahkan.</div>";
                                            }
                                        }
                                       
                                    }
                                    elseif($t == "meter_edit") {
                                        // Perhatikan: ID diambil dari input hidden (id_tarif_lama) yang disuntikkan oleh JS
                                        $no            = $_POST['no'];
                                        $meter_awal      = $_POST['meter_awal']; 
                                        $meter_akhir    = $_POST['meter_akhir'];

                                        $username       = $air->no_to_user($no);
                                        $_POST['username'] = $username;
                                        $kd_tarif       = $air->user_to_idtarif($username);
                                        $tarif          = $air->id_tarif_to_tarif($kd_tarif);
                                         $pemakaian = $meter_akhir - $meter_awal;
                                        $tagihan        = $tarif * $pemakaian;
                                        $status = $_POST['status_bayar'];
                                       
                                        // SUNTIKAN AUTO-LUNAS
                                        if ($pemakaian == 0) {
                                            $status = "LUNAS";
                                        }
                                       

                                       if($pemakaian < 0) { 
                                            echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>GAGAL UBAH: Meter Akhir harus lebih besar dari Meter Awal!</div>";
                                        } else {
                                            // EKSEKUSI UPDATE (TANGGAL & WAKTU DIHAPUS AGAR HISTORI ASLI TIDAK RUSAK)
                                            mysqli_query($koneksi, "UPDATE pemakaian SET meter_awal='$meter_awal', meter_akhir='$meter_akhir', pemakaian='$pemakaian', tagihan='$tagihan', status='$status' WHERE no='$no'");
                                            
                                            if (mysqli_affected_rows($koneksi) > 0) {
                                                echo "<div class='alert alert-success alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Meter BERHASIL diubah.</div>";
                                            } else {
                                                echo "<div class='alert alert-primary alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Meter tidak ada perubahan.</div>"; 
                                            }
                                        }
                                    

                                    
                                       }elseif($t == "meter_hapus") { 
                                        $no = $_POST['no']; 
                                        
                                        mysqli_query($koneksi, "DELETE FROM pemakaian WHERE no='$no'"); 
                                        
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Meter BERHASIL dihapus lekk...</div>";
                                        } else {
                                            echo "<div class='alert alert-danger alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Meter GAGAL dihapus lekk...</div>"; 
                                        }
                                    }
                                
                                    } elseif(isset($_GET['p'])) {
                                        $p=$_GET ['p'];
                                        if($p=="user_edit") {
                                        
                                        $user=$_GET['user'];
                                        // echo "masuk kesini untuk ngedit user: $user";
                                            $q = mysqli_query($koneksi, "SELECT password, nama, alamat, kota, tlp, level, tipe, status FROM login WHERE username='$user'");                                      
                                            $d=mysqli_fetch_row($q);
                                                
                                                // 2. KODE ASLI 
                                            $pass   = $d[0];
                                            $pass2  = password_hash($pass, PASSWORD_DEFAULT); 
                                            $nama   = $d[1];
                                            $alamat = $d[2];
                                            $kota   = $d[3];
                                            $tlp    = $d[4];
                                            $level  = $d[5];
                                            $tipe   = $d[6];
                                            $status = $d[7];
                                            
                                            // 3. JEMBATAN LOGIKA PENYELAMAT HTML 
                                            
                                            $_POST['passwet'] = $pass;
                                            $_POST['nama']    = $nama;
                                            $_POST['alamat']  = $alamat;
                                            $_POST['kota']    = $kota;
                                            $_POST['tlp']     = $tlp;
                                            $_POST['yuser']   = $user; // Untuk mengisi kolom username  

                                    } elseif($p == "tarif_edit") {
                                        $id_tarif = $_GET['id_tarif']; 
                                        
                                        // Ambil data dari database
                                        $q_t = mysqli_query($koneksi, "SELECT tipe_tarif, tarif, status FROM tarif WHERE id_tarif='$id_tarif'");
                                        $d_t = mysqli_fetch_row($q_t);
                                        
                                        // Suntikkan ke POST agar otomatis muncul di form HTML-mu
                                        $_POST['id_tarif']   = $id_tarif;
                                        $_POST['tipe_tarif'] = $d_t[0];
                                        $_POST['tarif']      = $d_t[1];
                                        $_POST['status']     = $d_t[2];

                                    } elseif($p == "meter_edit") {
                                        $no = $_GET['no']; // ngedit meter
                                        
                                        // Ambil data dari database
                                        $q_m = mysqli_query($koneksi, "SELECT username, meter_awal, meter_akhir FROM pemakaian WHERE no='$no'");
                                        $d_m = mysqli_fetch_row($q_m);
                                        
                                        // Suntikkan ke POST agar otomatis muncul di form HTML-mu
                                        $_POST['username']   = $d_m[0];
                                        
                                        $_POST['meter_awal'] = $d_m[1];
                                        $_POST['meter_akhir'] = $d_m[2];
                                        $q_stat = mysqli_query($koneksi, "SELECT status FROM pemakaian WHERE no='$no'");
                                        $d_stat = mysqli_fetch_row($q_stat);
                                        $_POST['status_bayar'] = $d_stat[0];
                                      
                                        

                                    } 
                                }
                            ?>
                         <?php
                            // echo "sesi user: ".$_SESSION['user']." sesi pass: ".$_SESSION['pass'];

                            // session_destroy();
                            // echo "<BR> setelah session_destroy: sesi user: ".$_SESSION['user']." sesi pass: ".$_SESSION['pass'];
                            ?>
                        
                       <div class="row mb-4" id="pilih_waktu">
                            <div class="col-xl-3 col-md-12">
                                <label for="sel1" class="form-label">Silahkan Pilih Waktu:</label>
                                <?php
                                // KUNCI MUTLAK JAM SERVER: Deteksi bulan berjalan saat ini secara riil (Juni)
                                $bln_sekarang_riil = date("Y-m"); 
                                ?>
                                <select class="form-select" id="sel1" name="pilih_waktu">
                                    <!-- <option value="">Bulan</option> -->
                                    <?php
                                    for($i=1; $i<=12; $i++) {
                                        $bulan_angka = ($i < 10) ? "0".$i : $i;
                                        $nilai_opsi = date("Y") . "-" . $bulan_angka;
                                        
                                        // SINKRONISASI JAM SERVER: Jika cocok dengan bulan sekarang (Juni), pasang SELECTED otomatis!
                                        $pasang_selected = ($nilai_opsi == $bln_sekarang_riil) ? "selected" : "";
                                        
                                        echo "<option value='" . $nilai_opsi . "' " . $pasang_selected . ">" . $air->bln($i) . " " . date("Y") . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div> 

                       <div class="row" id="summary">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4 text-center">
                                    <div class="card-body d-flex justify-content-center">
                                        <h3 class="m-3" id="box1"><?php 
                                    if($level == "warga") 
                                            echo "Waktu & Jam";  
                                            
                                    else echo "orang"; 
                                        ?></h3>
                                    </div>
                                    <div class="card-footer">
                                        <?php 
                                        if($level == "warga") 
                                            echo "Waktu Pencatatan Bulan Ini"; 
                                        else 
                                            echo "Pelanggan"; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4 text-center">
                                    <div class="card-body">
                                        <h3 class="m-3" id="box2">m³</h3>
                                    </div>
                                    <div class="card-footer">
                                        <?php 
                                    if($level == "bendahara") 
                                            echo "Pemasukan (Rp)"; 
                                        elseif($level == "warga") 
                                            echo "Pemakaian Air Bulan Ini (m³)"; 
                                    else echo "Pemakaian Air"; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4 text-center">
                                    <div class="card-body">
                            <h3 class="m-3" id="box3"><?php 
                                    if($level == "warga") 
                                            echo "Rp";  
                                            
                                    else echo "orang"; 
                                        ?></h3>
                                    </div>
                                    <div class="card-footer">
                                        <?php 
                                    if($level == "bendahara") 
                                            echo "Sudah Lunas"; 
                                        elseif($level == "warga") 
                                            echo "Jumlah Tagihan Bulan Ini (Rp)"; 
                                    else echo "Sudah Dicatat"; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4 text-center">
                                    <div class="card-body">
                                        <h3 class="m-3" id="box4"><?php 
                                    if($level == "warga") 
                                            echo "?";  
                                            
                                    else echo "orang"; 
                                        ?></h3>
                                    </div>
                                    <div class="card-footer">
                                        <?php 
                                        if($level == "bendahara") 
                                            echo "Belum Bayar"; 
                                        elseif($level == "warga") 
                                            echo "Status Tagihan Bulan Ini"; 
                                        else echo "Belum Dicatat"; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                          <div class="row" id="chart">
                        <?php if($level == "admin" || $level == "bendahara" || $level == "petugas") { ?>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-area text-primary"></i> Total Pemakaian Air Per Bulan <span id="tot_pemakaian" class="fw-bold text-primary"></span></div><div class="card-body"><canvas id="chPemakaian" width="100%" height="40"></canvas></div></div></div>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-pie text-danger"></i> Pelanggan (Rumah Tangga vs Kos)</div><div class="card-body"><canvas id="chTipe" width="100%" height="40"></canvas></div></div></div>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-bar text-success"></i> Pelanggan Sudah Dicatat</div><div class="card-body"><canvas id="chTercatat" width="100%" height="40"></canvas></div></div></div>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-bar text-danger"></i> Pelanggan Belum Dicatat</div><div class="card-body"><canvas id="chBlmTercatat" width="100%" height="40"></canvas></div></div></div>
                        <?php } ?>

                        <?php if($level == "admin" || $level == "bendahara") { ?>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-area text-success"></i> Total Tagihan Air Per Bulan <span id="tot_tagihan" class="fw-bold text-success"></span></div><div class="card-body"><canvas id="chTagihan" width="100%" height="40"></canvas></div></div></div>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-area text-primary"></i> Total Pemasukan Lunas Per Bulan</div><div class="card-body"><canvas id="chPemasukan" width="100%" height="40"></canvas></div></div></div>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-bar text-success"></i> Tagihan Sudah Lunas</div><div class="card-body"><canvas id="chLunas" width="100%" height="40"></canvas></div></div></div>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-bar text-danger"></i> Tagihan Belum Lunas</div><div class="card-body"><canvas id="chBlmLunas" width="100%" height="40"></canvas></div></div></div>
                        <?php } ?>

                        <?php if($level == "warga") { ?>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-bar text-primary "></i> Pemakaian Air Pribadi <span id="tot_pemakaian_w" class="fw-bold text-primary"></span></div><div class="card-body"><canvas id="chPemakaianWarga" width="100%" height="40"></canvas></div></div></div>
                            <div class="col-xl-6 col-md-12"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-area text-success"></i> Tagihan Air Pribadi <span id="tot_tagihan_w" class="fw-bold text-success"></span></div><div class="card-body"><canvas id="chTagihanWarga" width="100%" height="40"></canvas></div></div></div>
                        <?php } ?>
                    </div>
                            
                    </div>
                        <div class="card mb-4" id="user_add">
                            <div class="card-header">
                                <i class="fa-solid fa-user-plus fa-fade text-success me-2"></i> Tambah User
                            </div>
                            <div class="card-body">
                                <form action="" method="post" id="user_form">           
                                <div class="mb-3">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="yuser" value="<?php echo isset($_POST['yuser']) ? $_POST['yuser'] : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="passwet" value="<?php echo isset($_POST['passwet']) ? $_POST['passwet'] : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nama">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? $_POST['nama'] : '' ?>" required>
                                </div>
                                
                            <div class="mb-3">
                                    <label for="alamat">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" required><?php echo isset($_POST['alamat']) ? $_POST['alamat'] : '' ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="kota">Kota</label>
                                    <input type="text" class="form-control" id="kota" name="kota" value="<?php echo isset($_POST['kota']) ? $_POST['kota'] : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="telepon">Telepon</label>
                                    <input type="text" class="form-control" id="telepon" name="tlp" value="<?php echo isset($_POST['tlp']) ? $_POST['tlp'] : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="level">Level</label>
                                    <select class="form-select" id="level" name="level" required>
                                        <?php
                                        // Looping PHP untuk memunculkan pilihan level
                                        $level_array = array('admin', 'bendahara', 'petugas', 'warga');
                                        foreach ($level_array as $lv2) {
                                            if($level==$lv2) $selected= "SELECTED"; 
                                            else $selected="";
                                            // ucwords digunakan agar huruf pertama menjadi kapital di tampilan, namun valuenya tetap huruf kecil
                                            echo "<option value='$lv2' $selected>" . ucwords($lv2) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
            
            <div class="mb-3">
                <label for="tipe">Tipe</label>
                <select class="form-select" id="tipe" name="tipe" required>
                    
                     <?php
                    // Looping PHP untuk memunculkan pilihan level
                    $level_array = array('RT', 'Kos');
                    foreach ($level_array as $t2) {
                         if($tipe==$t2) $selected= "SELECTED"; 
                         else $selected="";
                        // ucwords digunakan agar huruf pertama menjadi kapital di tampilan, namun valuenya tetap huruf kecil
                        echo "<option value='$t2'  $selected>" . ucwords($t2) . "</option>";
                    }
                    ?>
                    
                </select>
            </div>
            
            <div class="mb-3">
                <label for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="">Status</option>
                     <?php
                    // Looping PHP untuk memunculkan pilihan level
                    $level_array = array('AKTIF', 'TDK AKTIF');
                    foreach ($level_array as $s2) {
                         if($status==$s2) $selected= "SELECTED"; 
                         else $selected="";
                        // ucwords digunakan agar huruf pertama menjadi kapital di tampilan, namun valuenya tetap huruf kecil
                        echo "<option value='$s2' $selected>" . ucwords($s2) . "</option>";
                    }
                    ?>
                    <!-- Pastikan value sama persis termasuk spasinya jika menggunakan spasi -->
                </select>
            </div>
            
            <!-- Tombol submit dengan name="tombol" dan value="simpan" -->
            <button type="submit" class="btn btn-primary" name="tombol" value="user_add">Simpan</button>
            
        </form>
    </div>
</div>
                               
                         <div class="card mb-4" id="user_list">
                            <div class="card-header">
                                <i class="fa-solid fa-users-rectangle fa-fade text-success me-2"></i>
                                 Data User
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Nama</th>
                                            <th>Alamat</th>
                                            <th>Kota</th>
                                            <th>Telepon</th>
                                            <th>Level</th>
                                            <th>Tipe</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        <?php
                                         $q=mysqli_query($koneksi,"SELECT username,nama,alamat,kota,tlp,level,tipe,status FROM login ORDER BY level ASC");
                                         while($d=mysqli_fetch_row($q)) {
                                            $user = $d[0];
                                            $nama = $d[1];
                                            $alamat = $d[2];
                                            $kota = $d[3];
                                            $telp = $d[4];
                                            $level = $d[5];
                                            $tipe = $d[6];
                                            $status = $d[7];

                                            echo "<tr>
                                                    <td>$user</td>
                                                    <td>$nama</td>
                                                    <td>$alamat</td>
                                                    <td>$kota</td>
                                                    <td>$telp</td>
                                                    <td>$level</td>
                                                    <td>$tipe</td>
                                                    <td>$status</td>
                                                    <td>
    <a href='index.php?p=user_edit&user=$user'><button type='button' class='btn btn-outline-primary btn-sm'>Ubah</button></a>
    <button type='button' class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#myModal' data-user='$user'>Hapus</button>
</td>

                                                </tr>";
                                         }
                                        ?>
                                        
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>

    <div class="card mb-4" id="tarif_add" style="display:none;">
    <div class="card-header">
        <i class="fa-solid fa-money-bill-wave text-success me-2"></i> Tambah Tarif
    </div>
    <div class="card-body">
        <form action="" method="post" id="tarif_form">
            <div class="mb-3">
                <label for="id_tarif">ID Tarif</label>
                <input type="text" class="form-control" id="id_tarif" name="id_tarif" value="<?php echo isset($_POST['id_tarif']) ? $_POST['id_tarif'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipe_tarif">Tipe Tarif</label>
                <select class="form-select" id="tipe_tarif" name="tipe_tarif" required>
                    <option value="RT" <?php echo (isset($_POST['tipe_tarif']) && $_POST['tipe_tarif'] == 'RT') ? 'selected' : ''; ?>>Rumah</option>
                    <option value="Kos" <?php echo (isset($_POST['tipe_tarif']) && $_POST['tipe_tarif'] == 'Kos') ? 'selected' : ''; ?>>Kos</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="tarif">Tarif (Rp)</label>
                <input type="number" class="form-control" id="tarif" name="tarif" value="<?php echo isset($_POST['tarif']) ? $_POST['tarif'] : '' ?>" required>
            </div>
            <div class="mb-3">
                <label class="d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status_aktif" value="AKTIF" <?php echo (isset($_POST['status']) && $_POST['status'] == 'AKTIF') ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="status_aktif">Aktif</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="status_tdk" value="TDK AKTIF" <?php echo (isset($_POST['status']) && $_POST['status'] == 'TDK AKTIF') ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="status_tdk">Tidak Aktif</label>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary" name="tombol" value="tarif_add">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4" id="tarif_list" style="display:none;">
    <div class="card-header">
        <i class="fa-solid fa-table text-success me-2"></i> Data Tarif
    </div>
    <div class="card-body">
        <table id="tarif_table">
            <thead>
                <tr>
                    <th>ID Tarif</th>
                    <th>Tipe Tarif</th>
                    <th>Tarif (Rp)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_tarif = mysqli_query($koneksi,"SELECT id_tarif, tipe_tarif, tarif, status FROM tarif ORDER BY id_tarif ASC");
                while($d_tarif = mysqli_fetch_row($q_tarif)) {
                    $id_tarif   = $d_tarif[0];
                    $tipe_tarif = $d_tarif[1];
                    $tarif      = $d_tarif[2];
                    $status     = $d_tarif[3];

                    echo "<tr>
                            <td>$id_tarif</td>
                            <td>$tipe_tarif</td>
                            <td>$tarif</td>
                            <td>$status</td>
                           <td>
    <a href='index.php?p=tarif_edit&id_tarif=$id_tarif'><button type='button' class='btn btn-outline-primary btn-sm'>Ubah</button></a>
    <button type='button' class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#modalTarif' data-id='$id_tarif'>Hapus</button>
</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4" id="meter_list" style="display:none;">
    <div class="card-header">
        <i class="fa-solid fa-rupiah-sign text-success me-2"></i> Data Meter Warga
    </div>
    <div class="card-body">
        <table id="meter_table">
            <thead>
                <tr>
                    <th>Nama Warga</th>
                    <th>Tanggal & Waktu</th>
                    <th>Meter Awal (m³)</th>
                    <th>Meter Akhir (m³)</th>
                    <th>Pemakaian (m³)</th>
                   <?php 
                    // sembunyikan kepala tabel tagihan dan status dari petugas
                    if($dt_user[2] != "petugas") { 
             echo "<th>Tagihan</th>
                   <th>Status</th>";
                    }
                    ?>
                    
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_pemakaian = mysqli_query($koneksi,"SELECT no, username, meter_awal, meter_akhir, pemakaian, tgl, waktu, tagihan, status FROM pemakaian ORDER BY tgl DESC, username ASC");
                while($d_pemakaian = mysqli_fetch_row($q_pemakaian)) {
                    $no         = $d_pemakaian[0];
                    $dt_user2      = $air->dt_user($d_pemakaian[1]); 
                    $nama=$dt_user2[0];
                    $meter_awal = $d_pemakaian[2];
                    $meter_akhir = $d_pemakaian[3];
                    $pemakaian  = $d_pemakaian[4];
                    $tgl        = $air->tgl_balik($d_pemakaian[5]);
                    $waktu      = $d_pemakaian[6];
                    $tagihan    = $d_pemakaian[7];
                    $status_byr = $d_pemakaian[8];

                    // Query manual cepat untuk mengambil Tipe dari tabel login
                    $q_tipe = mysqli_query($koneksi, "SELECT tipe FROM login WHERE username='$user'");
                    $d_tipe = mysqli_fetch_row($q_tipe);
                    $tipe_warga = $d_tipe[0];

                    // Format Rupiah untuk Tagihan
                    $tagihan_rp = "Rp " . number_format($tagihan, 0, ',', '.');

                    // Label Status Berwarna
                    $badge_status = ($status_byr == "LUNAS") ? "<span class='badge bg-success'>LUNAS</span>" : "<span class='badge bg-danger'>BELUM LUNAS</span>";

                    $level_login = $dt_user[2]; // level login saat ini (untuk menentukan tombol aksi yang muncul)
                    $tgl_tabel=date_create($d_pemakaian[5]);
                    $tgl_sekarang=date_create();
                    $diff=date_diff($tgl_tabel,$tgl_sekarang);
                    $selisih=$diff->days;
                    

                    $tombol_aksi = ""; 
                    $btn_html = "<a href='index.php?p=meter_edit&no=$no'><button type='button' class='btn btn-outline-primary btn-sm'>Ubah</button></a>
    <button type='button' class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#modalMeter' data-no='$no'>Hapus</button>
</td>";
                        

                    echo "
                        
                            <tr>
                            <td>$nama</td>
                            <td>$tgl $waktu | ".date("Y-m-d")." $selisih hari</td>
                            <td>$meter_awal</td>
                            <td>$meter_akhir</td>
                            <td>$pemakaian</td> ";
                     if ($level_login != "petugas") { //sembunyikan tagihan dari petugas (isi tabel)
                     echo "<td>$tagihan_rp</td>
                           <td>$badge_status</td>";
                            }    
                            // untuk petugas
                            if ($level_login == "admin" || $level_login == "bendahara") {
                                echo "<td>
    <a href='index.php?p=meter_edit&no=$no'><button type='button' class='btn btn-outline-primary btn-sm'>Ubah</button></a>
    <button type='button' class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#modalMeter' data-no='$no'>Hapus</button>
</td>";}
                            
                            else {
                                if ($selisih <= 30) {
                                echo "<td>
                            <a href='index.php?p=meter_edit&no=$no'><button type='button' class='btn btn-outline-primary btn-sm'>Ubah</button></a>
                                <button type='button' class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#modalMeter' data-no='$no'>Hapus</button>
                            </td>";}
                            else {
                                echo "<td><span class='badge bg-secondary'>Terkunci</span></td>";
                            }
                            }
                            
                            echo "</tr>";
                          
                        
                        
                }
                ?>
                </tbody>
        </table>
    </div>
</div>
    <div class="card mb-4" id="meter_add" style="display:none;">
    <div class="card-header">
        <i class="fa-solid fa-money-bill-wave text-success me-2"></i> Tambah Meter
    </div>
    <div class="card-body">
        <?php
        if ($e[1]=="meter_edit&no") $dis='disabled';
        else $dis="";
        ?>
        <form action="" method="post" id="meter_form">
            <div class="mb-3">
                <label for="username">Nama Warga</label>
                <select class="form-select" id="username" name="username" required <?php echo $dis; ?>>
                    <option value="">Nama Warga</option>
                    <?php
                    $qw=mysqli_query($koneksi,"SELECT username,nama FROM login WHERE level='warga'");
                    while ($dw=mysqli_fetch_row($qw)) {
                        // HANYA BARIS INI YANG BOLEH ADA DI SINI
                        $selected = (isset($_POST['username']) && $_POST['username'] == $dw[0]) ? "selected" : "";
                        echo "<option value='$dw[0]' $selected>$dw[1]</option>";
                    }
                    ?>
                </select>
                <div id="info_last_input" class="small text-muted mt-1"></div>
                <div id="js_alert_container" class="mt-2"></div>
            </div>
            
           
                <?php 
           // disable utak utik angka meter untuk bendahara 
           $kunci_meter = ($dt_user[2] == "bendahara") ? "readonly" : ""; 
           ?>
           <div class="row mb-3">
                <div class="col-md-6">
                    <label for="meter_awal">Meter Awal (m³)</label>
                    <input type="number" class="form-control" id="meter_awal" name="meter_awal" value="<?php echo isset($_POST['meter_awal']) ? $_POST['meter_awal'] : ''; ?>" required <?php echo $kunci_meter; ?>>
                </div>
                <div class="col-md-6">
                    <label for="meter_akhir">Meter Akhir (m³)</label>
                    <input type="number" class="form-control" id="meter_akhir" name="meter_akhir" value="<?php echo isset($_POST['meter_akhir']) ? $_POST['meter_akhir'] : ''; ?>" required <?php echo $kunci_meter; ?>>
                </div>
            </div>
      
               

            <div class="mt-3">
               <?php if($dt_user[2] == "admin" || $dt_user[2] == "bendahara") { ?>
            <div class="mb-3">
                <label for="status_bayar">Status Pembayaran</label>
                <select class="form-select" id="status_bayar" name="status_bayar">
                    <option value="BELUM LUNAS" <?php echo (isset($_POST['status_bayar']) && $_POST['status_bayar'] == 'BELUM LUNAS') ? 'selected' : ''; ?>>BELUM LUNAS</option>
                    <option value="LUNAS" <?php echo (isset($_POST['status_bayar']) && $_POST['status_bayar'] == 'LUNAS') ? 'selected' : ''; ?>>LUNAS</option>
                </select>
            </div>
            <?php } else { ?>
                <input type="hidden" name="status_bayar" value="BELUM LUNAS">
            <?php } ?>
               <?php
                // pembaca tombol
                if(isset($_GET['p']) && $_GET['p'] == "meter_edit"){
                    $no_edit = $_GET['no'];
                    // biar edit gakebaca add   
                    echo "<input type='hidden' name='no' value='$no_edit'>";
                  // kalo update atau edit
                    echo '<button type="submit" class="btn btn-primary" name="tombol" value="meter_edit">Simpan</button>';
                } else {
                    echo '<button type="submit" class="btn btn-primary" name="tombol" value="meter_add">Simpan</button>';
                }
                ?>
                <?php
        // KOREKSI DOSEN: Mengumpulkan data komprehensif (Meter Terakhir, Status Bulan Ini, & Log Bulan Lalu)
        $q_last = mysqli_query($koneksi, "SELECT username, meter_akhir, tgl, waktu FROM pemakaian ORDER BY tgl DESC, waktu DESC");
        $data_last_meter = array();
        
        while($d_last = mysqli_fetch_assoc($q_last)) {
            $u = $d_last['username'];
            // Deteksi otomatis apakah data ini diinput pada bulan dan tahun berjalan saat ini
            $is_bulan_ini = (date('Y-m', strtotime($d_last['tgl'])) == date('Y-m')) ? true : false;
            
            if(!isset($data_last_meter[$u])) {
                // Menyimpan data terbaru mutlak milik warga
                $data_last_meter[$u] = array(
                    'meter_akhir' => $d_last['meter_akhir'],
                    'sudah_input_bulan_ini' => $is_bulan_ini,
                    'tgl_terakhir' => $air->tgl_balik($d_last['tgl']) . " " . $d_last['waktu'],
                    'tgl_bulan_lalu' => '-'
                );
            } else {
                // Jika data terbarunya adalah bulan ini, maka baris data kedua (elese) ini adalah log bulan lalu/sebelumnya
                if ($data_last_meter[$u]['sudah_input_bulan_ini'] && $data_last_meter[$u]['tgl_bulan_lalu'] == '-') {
                    $data_last_meter[$u]['tgl_bulan_lalu'] = $air->tgl_balik($d_last['tgl']) . " " . $d_last['waktu'];
                }
            }
        }
        ?>
<script>
// Ambil status kontekstual asli dari PHP agar JavaScript tidak amnesia Role
const currentLevel = "<?php echo $level; ?>";
const currentParam = "<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>";
const riwayatMeter = <?php echo json_encode($data_last_meter); ?>;

document.querySelector('#meter_form #username').addEventListener('change', function() {
    const userPilih = this.value;
    const inputMeterAwal = document.getElementById('meter_awal');
    const inputMeterAkhir = document.getElementById('meter_akhir');
    const infoLastInput = document.getElementById('info_last_input');
    const alertContainer = document.getElementById('js_alert_container');
    
    // Reset keadaan form info
    infoLastInput.innerHTML = "";
    alertContainer.innerHTML = "";
    
    // BARIKADE 1: KUNCI MUTLAK EDIT MODE
    // Jika sedang dalam mode edit data lama, STOP dan dilarang keras menimpa/mengosongkan angka!
    if (currentParam === 'meter_edit') {
        if (currentLevel === 'bendahara') {
            inputMeterAwal.readOnly = true;
            inputMeterAwal.style.backgroundColor = '#e8f0fe';
            inputMeterAkhir.readOnly = true;
            inputMeterAkhir.style.backgroundColor = '#e8f0fe';
        }
        return; // Keluar dari fungsi, amankan nilai asli yang dicetak PHP dari database!
    }
    
    if(userPilih === "") {
        if (currentLevel !== 'bendahara') {
            inputMeterAwal.value = '';
            inputMeterAwal.style.backgroundColor = '';
            inputMeterAwal.readOnly = false; 
            inputMeterAkhir.value = '';
        }
        return;
    }
    
    if(riwayatMeter[userPilih] !== undefined) {
        const dataWarga = riwayatMeter[userPilih];
        
        // Logika ini HANYA berjalan di mode METER_ADD (Tambah Data Baru)
        inputMeterAwal.value = dataWarga.meter_akhir;
        inputMeterAwal.style.backgroundColor = '#e8f0fe'; 
        inputMeterAwal.readOnly = true; 
        
        // Meter akhir dipaksa KOSONG bersih saat tambah data baru agar bisa diketik petugas
        if (currentLevel !== 'bendahara') {
            inputMeterAkhir.value = ''; 
            inputMeterAkhir.readOnly = false; 
            inputMeterAkhir.style.backgroundColor = '';
        }
        
        // Evaluasi Status Peringatan Duplikat Kalender
        if(dataWarga.sudah_input_bulan_ini) {
            infoLastInput.innerHTML = `<i class="fa-solid fa-clock text-danger"></i> Sudah tercatat bulan ini pada: <b class="text-danger">${dataWarga.tgl_terakhir}</b>`;
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show m-0 animate_fade" id="alert-meter">
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <strong>Gagal:</strong> Pencatatan bulan Juni sudah dilakukan (${dataWarga.tgl_terakhir}). Tidak boleh ada data ganda!
                </div>`;
        } else {
            infoLastInput.innerHTML = `<i class="fa-solid fa-clock text-success"></i> Input terakhir: <b class="text-success">${dataWarga.tgl_terakhir}</b>`;
        }
    } else {
        // Jika Warga Baru murni tanpa riwayat
        if (currentLevel !== 'bendahara') {
            inputMeterAwal.value = '';
            inputMeterAwal.style.backgroundColor = '';
            inputMeterAwal.readOnly = false; 
            inputMeterAkhir.value = '';
            inputMeterAkhir.readOnly = false;
            inputMeterAkhir.style.backgroundColor = '';
        }
        infoLastInput.innerHTML = `<i class="fa-solid fa-circle-info text-info"></i> Warga baru: Belum ada riwayat input meteran sebelumnya.`;
    }
});

// BARIKADE 2: Pemicu otomatis saat komponen DOM selesai dimuat
window.addEventListener('DOMContentLoaded', (event) => {
    const usernameSelect = document.querySelector('#meter_form #username');
    
    // Kunci paksa di detik pertama jika aktornya Bendahara
    if (currentLevel === 'bendahara') {
        document.getElementById('meter_awal').readOnly = true;
        document.getElementById('meter_awal').style.backgroundColor = '#e8f0fe';
        document.getElementById('meter_akhir').readOnly = true;
        document.getElementById('meter_akhir').style.backgroundColor = '#e8f0fe';
    }

    if(usernameSelect && usernameSelect.value !== "") {
        usernameSelect.dispatchEvent(new Event('change'));
    }
});
</script>

             </form>
        </div>
    </div>
           
                
                    </div>
     <div class="card mb-4" id="warga_pemakaian_list" style="display:none;">
    <div class="card-header">
        <i class="fa-solid fa-tint text-primary fa-bounce  me-2"></i> Riwayat Pemakaian Air Anda
    </div>
    <div class="card-body">
        <table id="warga_table">
            <thead>
                <tr>
                    <th>Waktu Pencatatan</th>
                    <th>Kode Tarif</th>
                    <th>Meter Awal (m³)</th>
                    <th>Meter Akhir (m³)</th>
                    <th>Pemakaian (m³)</th>
                    <th>Tagihan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php //tabel pemakaian pribadi warga sesuai username masing masing
                // ISOLASI DATA: Hanya panggil berdasarkan session username
                $sesi_user = $_SESSION['user'];
                $q_warga = mysqli_query($koneksi,"SELECT tgl, waktu, kd_tarif, meter_awal, meter_akhir, pemakaian, tagihan, status FROM pemakaian WHERE username='$sesi_user' ORDER BY tgl DESC");
                
                while($d_warga = mysqli_fetch_row($q_warga)) {
                    $tgl_w      = $d_warga[0];
                    $waktu_w    = $d_warga[1];
                    $kd_tarif_w = $d_warga[2];
                    $m_awal     = $d_warga[3];
                    $m_akhir    = $d_warga[4];
                    $pake       = $d_warga[5];
                    $tagihan_w  = $d_warga[6];
                    $status_w   = $d_warga[7];

                    $tagihan_rp_w = "Rp " . number_format($tagihan_w, 0, ',', '.');
                    $badge_w = ($status_w == "LUNAS") ? "<span class='badge bg-success'>LUNAS</span>" : "<span class='badge bg-danger'>BELUM LUNAS</span>";

                    echo "<tr>
                            <td>$tgl_w $waktu_w</td>
                            <td>$kd_tarif_w</td>
                            <td>$m_awal</td>
                            <td>$m_akhir</td>
                            <td>$pake</td>
                            <td>$tagihan_rp_w</td>
                            <td>$badge_w</td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
                    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Konfirmasi hapus data</h5> <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        </div>
      <div class="modal-footer">
        <form action="" method="post"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button> <button type="submit" class="btn btn-danger" name="tombol" value="user_hapus">Ya</button> </form>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalTarif" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Konfirmasi hapus Tarif</h5> 
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                </div>
                                                            <div class="modal-footer">
                                                                <form action="" method="post"> 
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button> 
                                                                    <button type="submit" class="btn btn-danger" name="tombol" value="tarif_hapus">Ya</button> 
                                                                </form>
                                                            </div>
                                                            </div>
                                                        </div>
                                                        </div>
<div class="modal fade" id="modalMeter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Data Meter</h5> 
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                </div>
            <div class="modal-footer">
                <form action="" method="post"> 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button> 
                    <button type="submit" class="btn btn-danger" name="tombol" value="meter_hapus">Ya</button> 
                </form>
            </div>
        </div>
    </div>
</div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; Your Website </div>
                            <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="
        js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <!-- <script src="../assets/demo/chart-area-demo.js"></script> -->
        <!-- <script src="../assets/demo/chart-bar-demo.js"></script> -->
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="../js/datatables-simple-demo.js"></script>
    </body>
</html>
