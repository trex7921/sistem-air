<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
if(empty($_SESSION['user'])) {
    echo "<script>window.location.replace('../index.php')</script>";
    exit();
}

// TIMEOUT SESI: nganggur >30 menit -> paksa logout. Sebelumnya sesi staff gak punya batas waktu sama sekali,
// jadi kalau lupa logout dari komputer bersama, sesi admin nyala terus selamanya.
$batas_diam_detik = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $batas_diam_detik)) {
    session_unset();
    session_destroy();
    echo "<script>alert('Sesi berakhir karena tidak ada aktivitas. Silakan login ulang.'); window.location.replace('../index.php');</script>";
    exit();
}
$_SESSION['last_activity'] = time();

// koneksi ke database
include '../assets/func.php';
$air = new kelas_air;
$koneksi = $air->koneksi();
$dt_user=$air->dt_user($_SESSION['user']);
$level=$dt_user[2];

// JARING PENGAMAN: akun di $_SESSION['user'] udah gak ada di DB (dihapus admin pas user lain masih login)
// dt_user() sekarang balikin [null,null,null] alih-alih Fatal Error, tapi tetap harus dipentalkan ke login
if ($level === null) {
    session_destroy();
    echo "<script>alert('Sesi tidak valid, silakan login ulang.'); window.location.replace('../index.php');</script>";
    exit();
}

// TOKEN CSRF: sebelumnya GAK ADA sama sekali di aplikasi ini. Semua form add/edit/hapus cuma modal cookie-session,
// artinya situs jahat bisa nyuruh browser admin yang lagi login buat auto-submit form (hapus user, ubah tarif, dst).
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
        <title>Si-Air | Dashboard</title>
        <link rel="icon" href="../assets/img/favicon.svg" type="image/svg+xml" />
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="../js/air.js"></script>
    </head>
    
    <script>
        var level_user = <?php echo json_encode($level); ?>;
        var user = <?php echo json_encode($_SESSION['user']); ?>;    
    </script>

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
                            <a class="nav-link" href="index.php?p=settings">
                                <div class="sb-nav-link-icon"><i class="fas fa-gear text-secondary"></i></div>
                                Settings
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
                            
                             <a class="nav-link" href="index.php?p=manajemen_tarif_air">
                             <div class="sb-nav-link-icon"><i class="fa-solid fa-money-bill-wave fa-beat text-success"></i></div>
                                Tarif Air Warga
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
                            
                              <a class="nav-link" href="index.php?p=manajemen_tarif_air">
                             <div class="sb-nav-link-icon"><i class="fa-solid fa-hand fa-shake text-success"></i></div>
                                Manajemen Tarif Air
                            </a>
                            
                             <!-- <a class="nav-link" href="index.php?p=infografis_tagihan_warga">
                             <div class="sb-nav-link-icon"><i class="fa-solid fa-eye fa-beat-fade text-info"></i></div>
                                Infografis Tagihan Warga
                            </a> -->
                            
                           
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

                            <?php if($level=="admin" || $level=="bendahara") { ?>
                            <a class="nav-link" href="index.php?p=activity_log">
                                <div class="sb-nav-link-icon"><i class="fas fa-clock-rotate-left text-dark"></i></div>
                                Activity Log
                            </a>
                            <?php } ?>

                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                         <div class="small"><i class="fa-regular fa-user fa-flip text-danger"></i> Logged in as : <?php echo htmlspecialchars($dt_user[2])?></div>
                        <?php echo htmlspecialchars($dt_user[0]) . ' (' . htmlspecialchars($dt_user[1]) . ')'?>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <?php
                       // echo $_SERVER['REQUEST_URI'];
                        $p_halaman = isset($_GET['p']) ? $_GET['p'] : '';
                        
                        if(!empty($p_halaman)) {
                            // --- BLOK ADMIN & GLOBAL ---
                            if($p_halaman=="dashboard") {
                                $h1="Dashboard Si-Air";
                                $li="Selamat Datang di Dashboard Si-Air Kel 01";
                            }
                            elseif($p_halaman=="user" || $p_halaman=="user_edit") {
                                $h1="Manajemen User";
                                $li="Menu untuk CRUD User";
                            }
                            elseif($p_halaman=="pemakaian_warga") {
                                $h1="Pemakaian Warga";
                                $li="Data Pemakaian Air Warga";
                            }
                            elseif($p_halaman=="manajemen_tarif_air" || $p_halaman=="tarif_edit") {
                                $h1="Tarif Warga";
                                $li="Lihat dan Edit Tarif Air Seluruh Warga";
                            }
                            elseif($p_halaman=="infografis_warga") {
                                $h1="Infografis Warga";
                                $li="Visualisasi Grafik Pemakaian dan Tagihan Air Warga";
                            }
                            elseif($p_halaman=="manajemen_tarif_air") {
                                $h1="Manajemen Tarif Air";
                                $li="Pengaturan Harga Tarif Air per Kubik";
                            }

                            // --- BLOK BENDAHARA ---
                            elseif($p_halaman=="ubah_datameter_warga") {
                                $h1="Ubah Data Meter Warga";
                                $li="Halaman untuk mengubah data meter warga";
                            }
                            elseif($p_halaman=="infografis_tagihan_warga") {
                                $h1="Infografis Tagihan Warga";
                                $li="Visualisasi Grafik Tagihan Air Warga";
                            }
                            elseif($p_halaman=="tagihan_warga") {
                                $h1="Tagihan Warga";
                                $li="Tagihan Air Bulanan Warga";
                            }

                            // --- BLOK PETUGAS ---
                            elseif($p_halaman=="catat_edit_meter" || $p_halaman=="meter_edit") {
                                $h1="Catat & Edit Data Meter";
                                $li="Masukkan Angka Meteran Bulan Ini";
                            }
                            // elseif($p_halaman=="info_pelanggan") {
                            //     $h1="Total Pelanggan";
                            //     $li="Informasi Jumlah Pelanggan dan Pemakaian";
                            // }
                            // elseif($p_halaman=="infografis_pemakaian") {
                            //     $h1="Infografis Pemakaian";
                            //     $li="Grafik Total Pemakaian Air Warga";
                            // }

                            // --- BLOK WARGA ---
                            elseif($p_halaman=="pantau_pemakaian") {
                                $h1="Pantau Pemakaian";
                                $li="Riwayat Pemakaian Air Anda";
                            }
                            elseif($p_halaman=="tagihan_saya") {
                                $h1="Tagihan Saya";
                                $li="Rincian Tagihan Air Bulanan Anda";
                            }
                            elseif($p_halaman=="infografis_warga") {
                                $h1="Infografis Saya";
                                $li="Grafik Pemakaian dan Tagihan Air Anda";
                            }

                            // --- BLOK SEMUA ROLE (settings) & ADMIN/BENDAHARA (activity_log) ---
                            elseif($p_halaman=="settings") {
                                $h1="Pengaturan Akun";
                                $li="Ubah Password dan Data Diri Anda";
                            }
                            elseif($p_halaman=="activity_log") {
                                $h1="Rekam Jejak Aktivitas";
                                $li="Riwayat Perubahan Data oleh Semua Role";
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

                                // GUARD CSRF: token wajib cocok sama punya sesi. Ini nutup celah "situs jahat auto-submit form pake sesi admin".
                                if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                                    echo "<script>alert('Sesi form kadaluarsa. Muat ulang halaman dan coba lagi.'); window.location.replace('index.php');</script>";
                                    exit();
                                }

                                // GUARD OTORISASI: cek level session, bukan input dari klien
                                $aksi_admin_saja      = array("user_add", "user_edit", "user_hapus");
                                $aksi_admin_bendahara = array("tarif_add", "tarif_edit", "tarif_hapus");
                                $aksi_pencatat_meter  = array("meter_add", "meter_edit", "meter_hapus");

                                if (in_array($t, $aksi_admin_saja) && $level != "admin") {
                                    echo "<script>alert('Akses ditolak. Fitur ini khusus admin.'); window.location.replace('index.php');</script>";
                                    exit();
                                } elseif (in_array($t, $aksi_admin_bendahara) && !in_array($level, array("admin", "bendahara"))) {
                                    echo "<script>alert('Akses ditolak. Fitur ini khusus admin/bendahara.'); window.location.replace('index.php');</script>";
                                    exit();
                                } elseif (in_array($t, $aksi_pencatat_meter) && !in_array($level, array("admin", "bendahara", "petugas"))) {
                                    echo "<script>alert('Akses ditolak.'); window.location.replace('index.php');</script>";
                                    exit();
                                }

                                // echo "<div class='alert alert-warning text-center fs-4' style='z-index:9999;'>TOMBOL YANG DITERIMA PHP ADALAH: <b>" . htmlspecialchars($t) . "</b></div>";
                                if($t=="user_add") {

                                    $user=$_POST['yuser'];
                                    $pass=password_hash($_POST['passwet'], PASSWORD_DEFAULT);
                                    $pass2=$_POST['passwet'];
                                    $nama=$_POST['nama'];
                                    $alamat=$_POST['alamat'];
                                    $kota=$_POST['kota'];
                                    $tlp=$_POST['tlp'];
                                    $level_form=$_POST['level']; // isolasi, gak nimpa $level session (konsisten kaya blok user_edit)
                                    if (!in_array($level_form, array("admin", "bendahara", "petugas", "warga"))) $level_form = "warga"; // JARING PENGAMAN: cegah nilai sembarangan masuk kolom level
                                    $tipe=$_POST['tipe'];
                                    $status=$_POST['status'];

                                    //cek sudah ada atau belum di tabel user
                                    $stmt=mysqli_prepare($koneksi,"SELECT username FROM login WHERE username=?");
                                    mysqli_stmt_bind_param($stmt, "s", $user);
                                    mysqli_stmt_execute($stmt);
                                    $qc=mysqli_stmt_get_result($stmt);
                                    $qj=mysqli_num_rows($qc);
                                    // echo "hasil cek user: $qj";
                                    if (empty($qj)) {
                                        $stmt = mysqli_prepare($koneksi, "INSERT INTO login (username, password, nama, alamat, kota, tlp, level, tipe, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                                        mysqli_stmt_bind_param($stmt, "sssssssss", $user, $pass, $nama, $alamat, $kota, $tlp, $level_form, $tipe, $status);
                                        mysqli_stmt_execute($stmt);
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            $air->catat_log($_SESSION['user'], $level, 'user_add', 'login', $user, "tambah user baru, level=$level_form");
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
    // PERBAIKAN: Tambahkan id='alert-user' di bawah ini
    echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-user'>
            <button type=button class=btn-close data-bs-dismiss=alert></button>
            <strong>Username</strong> SUDAH digunakan!...
        </div>";
}
                                    }  elseif($t=="user_edit") {

                                        $user   = $_POST['yuser'];
                                        $pass   = $_POST['passwet'];
                                        $nama   = $_POST['nama'];
                                        $alamat = $_POST['alamat'];
                                        $kota   = $_POST['kota'];
                                        $tlp    = $_POST['tlp'];
                                        $level_form  = $_POST['level']; // Menggunakan nama terisolasi biar gak bentrok
                                        if (!in_array($level_form, array("admin", "bendahara", "petugas", "warga"))) $level_form = "warga"; // JARING PENGAMAN
                                        $tipe   = $_POST['tipe'];
                                        $status = $_POST['status'];

                                        // LOGIKA PERCABANGAN PASSWORD: form password dikosongkan by default (lihat blok GET p=user_edit di bawah).
                                        // Field kosong pas submit = admin gak niat ganti password = jangan sentuh kolom password.
                                        // (Sebelumnya ini cek $pass == hash asli dari DB -- itu justru ALASAN hash bocor ke HTML, karena hash mentahnya
                                        // harus di-roundtrip ke form buat dibandingin. Sekarang gak perlu lagi.)
                                        if (empty($pass)) {
                                            $stmt = mysqli_prepare($koneksi, "UPDATE login SET nama=?, alamat=?, kota=?, tlp=?, level=?, tipe=?, status=? WHERE username=?");
                                            mysqli_stmt_bind_param($stmt, "ssssssss", $nama, $alamat, $kota, $tlp, $level_form, $tipe, $status, $user);
                                            mysqli_stmt_execute($stmt);
                                        } else {
                                            $pass2 = password_hash($pass, PASSWORD_DEFAULT);
                                            $stmt = mysqli_prepare($koneksi, "UPDATE login SET password=?, nama=?, alamat=?, kota=?, tlp=?, level=?, tipe=?, status=? WHERE username=?");
                                            mysqli_stmt_bind_param($stmt, "sssssssss", $pass2, $nama, $alamat, $kota, $tlp, $level_form, $tipe, $status, $user);
                                            mysqli_stmt_execute($stmt);
                                        }

                                        // PERBAIKAN 1: Paksa terpental balik ke halaman list user biar gak double submit
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            $air->catat_log($_SESSION['user'], $level, 'user_edit', 'login', $user, empty($pass) ? 'ubah data diri user' : 'ubah data diri user + reset password');
                                            $_SESSION['res_user'] = 'sukses_edit';
                                            echo "<script>window.location.replace('index.php?p=user');</script>";
                                            exit();
                                        } else {
                                            $_SESSION['res_user'] = 'tanpa_perubahan';
                                            echo "<script>window.location.replace('index.php?p=user');</script>"; 
                                            exit();
                                        }
               
                                    } elseif($t=="user_hapus") { 
                                        $user = $_POST['yuser']; 
                                        
                                        // PERBAIKAN 4 (ANTI-HANTU): Bantai data anak (pemakaian) dulu sebelum akunnya dihapus
                                        $stmt = mysqli_prepare($koneksi, "DELETE FROM pemakaian WHERE username=?");
                                        mysqli_stmt_bind_param($stmt, "s", $user);
                                        mysqli_stmt_execute($stmt);
                                        
                                        // Baru bantai akun induknya
                                        $stmt = mysqli_prepare($koneksi, "DELETE FROM login WHERE username=?");
                                        mysqli_stmt_bind_param($stmt, "s", $user);
                                        mysqli_stmt_execute($stmt);
                                        
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            $air->catat_log($_SESSION['user'], $level, 'user_hapus', 'login', $user, 'hapus akun user (+riwayat pemakaian ikut terhapus)');
                                            $_SESSION['res_user'] = 'sukses_hapus';
                                            echo "<script>window.location.replace('index.php?p=user');</script>";
                                            exit();
                                        } else {
                                            $_SESSION['res_user'] = 'gagal_hapus';
                                            echo "<script>window.location.replace('index.php?p=user');</script>";
                                            exit();
                                        }
                                    
                                    
                                    
                                    
                                        } elseif($t == "tarif_add") {
                                        $id_tarif   = $_POST['id_tarif'];
                                        $tipe_tarif = $_POST['tipe_tarif'];
                                        $tarif      = $_POST['tarif'];
                                        $status     = $_POST['status']; 

                                        // Cek apakah ID Tarif sudah ada agar tidak error duplikat
                                        $stmt = mysqli_prepare($koneksi,"SELECT id_tarif FROM tarif WHERE id_tarif=?");
                                        mysqli_stmt_bind_param($stmt, "s", $id_tarif);
                                        mysqli_stmt_execute($stmt);
                                        $qc_t = mysqli_stmt_get_result($stmt);
                                        if (mysqli_num_rows($qc_t) == 0) {
                                            $stmt = mysqli_prepare($koneksi, "INSERT INTO tarif (id_tarif, tipe_tarif, tarif, status) VALUES (?, ?, ?, ?)");
                                            mysqli_stmt_bind_param($stmt, "ssss", $id_tarif, $tipe_tarif, $tarif, $status);
                                            mysqli_stmt_execute($stmt);
                                            
                                            if (mysqli_affected_rows($koneksi) > 0) {
                                                $air->catat_log($_SESSION['user'], $level, 'tarif_add', 'tarif', $id_tarif, "tambah tarif $tipe_tarif = $tarif, status=$status");
                                                echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif BERHASIL ditambahkan.</div>";
                                            } else {
                                                echo "<div class='alert alert-danger alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif GAGAL ditambahkan.</div>";
                                            }
                                        } else {
                                            echo "<div class='alert alert-warning alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>ID Tarif SUDAH DIGUNAKAN!</div>";
                                        }

                                    
                                      } elseif($t == "tarif_edit") {
                                        $id_tarif      = $_POST['id_tarif_lama']; 
                                        $tipe_tarif    = $_POST['tipe_tarif'];
                                        $tarif         = $_POST['tarif'];
                                        $status        = $_POST['status']; 

                                        $stmt = mysqli_prepare($koneksi, "UPDATE tarif SET tipe_tarif=?, tarif=?, status=? WHERE id_tarif=?");
                                        mysqli_stmt_bind_param($stmt, "ssss", $tipe_tarif, $tarif, $status, $id_tarif);
                                        mysqli_stmt_execute($stmt);
                                        
                                        // PERBAIKAN 2: Paksa Redirect Tarif
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            $air->catat_log($_SESSION['user'], $level, 'tarif_edit', 'tarif', $id_tarif, "ubah tarif $tipe_tarif jadi $tarif, status=$status");
                                            $_SESSION['res_tarif'] = 'sukses_edit';
                                            echo "<script>window.location.replace('index.php?p=manajemen_tarif_air');</script>";
                                            exit();
                                        } else {
                                            $_SESSION['res_tarif'] = 'tanpa_perubahan';
                                            echo "<script>window.location.replace('index.php?p=manajemen_tarif_air');</script>"; 
                                            exit();
                                        }
                                    
                                       } elseif($t == "tarif_hapus") { 
                                        $id_tarif = $_POST['id_tarif']; 
                                        
                                        $stmt = mysqli_prepare($koneksi, "DELETE FROM tarif WHERE id_tarif=?");
                                        mysqli_stmt_bind_param($stmt, "s", $id_tarif);
                                        mysqli_stmt_execute($stmt);
                                        
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            $air->catat_log($_SESSION['user'], $level, 'tarif_hapus', 'tarif', $id_tarif, 'hapus data tarif');
                                            echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif BERHASIL dihapus lekk...</div>";
                                        } else {
                                            echo "<div class='alert alert-danger alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Tarif GAGAL dihapus lekk...</div>"; 
                                        }
                                    } 
                                    // =========================================================================
                                    // PERBAIKAN ALUR METERAN: MASUKKAN KEMBALI KE DALAM BLOK ISSET POST TOMBOL
                                    // =========================================================================
                                    elseif($t == "meter_add") {
                                        $username       = $_POST['username'];
                                        $meter_awal     = $_POST['meter_awal'];
                                        $meter_akhir    = $_POST['meter_akhir'];
                                        $kd_tarif       = $air->user_to_idtarif($username);
                                        $tarif          = $air->id_tarif_to_tarif($kd_tarif);
                                        $status         = $_POST['status_bayar'];
                                        $bln_skrg       = date('m');
                                        $thn_skrg       = date('Y');

                                        $pemakaian      = $meter_akhir - $meter_awal;
                                        $tagihan        = $tarif * $pemakaian;

                                        if ($pemakaian == 0) { $status = "LUNAS"; }

                                        $stmt = mysqli_prepare($koneksi, "SELECT no FROM pemakaian WHERE username=? AND MONTH(tgl) = ? AND YEAR(tgl) = ?");
                                        mysqli_stmt_bind_param($stmt, "sss", $username, $bln_skrg, $thn_skrg);
                                        mysqli_stmt_execute($stmt);
                                        $q_cek_bulan = mysqli_stmt_get_result($stmt);
                                        
                                        if (mysqli_num_rows($q_cek_bulan) > 0) {
                                            echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>GAGAL: Warga ini <b>sudah dicatat</b> meternya pada bulan ini!</div>";
                                        } 
                                        elseif ($pemakaian < 0) { 
                                            echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>GAGAL: Meter Akhir harus lebih besar dari Meter Awal!</div>";
                                        } 
                                        else {
                                            $stmt = mysqli_prepare($koneksi, "INSERT INTO pemakaian (username, meter_awal, meter_akhir, pemakaian, tgl, waktu, kd_tarif, tagihan, status) VALUES (?, ?, ?, ?, CURRENT_DATE(), CURRENT_TIME(), ?, ?, ?)");
                                            mysqli_stmt_bind_param($stmt, "sssssss", $username, $meter_awal, $meter_akhir, $pemakaian, $kd_tarif, $tagihan, $status);
                                            mysqli_stmt_execute($stmt);

                                            if (mysqli_affected_rows($koneksi) > 0) {
                                                $air->catat_log($_SESSION['user'], $level, 'meter_add', 'pemakaian', $username, "catat meter awal=$meter_awal akhir=$meter_akhir status=$status");
                                                $_SESSION['res_meter'] = 'sukses_add';
                                                echo "<script>window.location.replace('index.php?p=catat_edit_meter');</script>";
                                                exit();
                                            } else {
                                                echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>Data Meter GAGAL ditambahkan.</div>";
                                            }
                                        }
                                       
                                    }
                                    elseif($t == "meter_edit") {
                                        $no             = $_POST['no'];

                                        // GERBANG UMUR DATA: tombolnya emang udah disembunyiin di tabel kalau >30 hari,
                                        // tapi itu cuma tampilan. POST langsung ke sini gak pernah dicek -- IDOR kalau petugas tau nomor 'no'-nya.
                                        $stmt_cek = mysqli_prepare($koneksi, "SELECT tgl, status FROM pemakaian WHERE no = ?");
                                        mysqli_stmt_bind_param($stmt_cek, "i", $no);
                                        mysqli_stmt_execute($stmt_cek);
                                        $res_cek = mysqli_stmt_get_result($stmt_cek);
                                        $data_tgl = mysqli_fetch_row($res_cek);
                                        mysqli_stmt_close($stmt_cek);
                                        $status_lama = $data_tgl[1] ?? null; // dipake buat catet "status X -> Y" di log
                                        if ($data_tgl) {
                                            $selisih_cek = date_diff(date_create($data_tgl[0]), date_create())->days;
                                            if ($level == "petugas" && $selisih_cek > 30) {
                                                echo "<script>alert('Akses ditolak. Data ini sudah lebih dari 30 hari dan terkunci untuk petugas.'); window.location.replace('index.php?p=catat_edit_meter');</script>";
                                                exit();
                                            }
                                        }

                                        $meter_awal     = $_POST['meter_awal']; 
                                        $meter_akhir    = $_POST['meter_akhir'];

                                        $username       = $air->no_to_user($no);
                                        $_POST['username'] = $username;
                                        $kd_tarif       = $air->user_to_idtarif($username);
                                        $tarif          = $air->id_tarif_to_tarif($kd_tarif);
                                        $pemakaian      = $meter_akhir - $meter_awal;
                                        $tagihan        = $tarif * $pemakaian;
                                        $status         = $_POST['status_bayar'];
                                       
                                        if ($pemakaian == 0) { $status = "LUNAS"; }

                                        if($pemakaian < 0) { 
                                            echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type=button class=btn-close data-bs-dismiss=alert></button>GAGAL UBAH: Meter Akhir harus lebih besar dari Meter Awal!</div>";
                                        } else {
                                            $stmt = mysqli_prepare($koneksi, "UPDATE pemakaian SET meter_awal=?, meter_akhir=?, pemakaian=?, tagihan=?, status=? WHERE no=?");
                                            mysqli_stmt_bind_param($stmt, "sssssi", $meter_awal, $meter_akhir, $pemakaian, $tagihan, $status, $no);
                                            mysqli_stmt_execute($stmt);
                                            
                                            // PERBAIKAN 3: Paksa Redirect Meteran
                                            if (mysqli_affected_rows($koneksi) > 0) {
                                                $detail_log = ($status_lama !== null && $status_lama != $status) ? "status: $status_lama -> $status" : "ubah meter_awal/meter_akhir";
                                                $air->catat_log($_SESSION['user'], $level, 'meter_edit', 'pemakaian', $no, $detail_log);
                                                $_SESSION['res_meter'] = 'sukses_edit';
                                                echo "<script>window.location.replace('index.php?p=catat_edit_meter');</script>";
                                                exit();
                                            } else {
                                                $_SESSION['res_meter'] = 'tanpa_perubahan';
                                                echo "<script>window.location.replace('index.php?p=catat_edit_meter');</script>"; 
                                                exit();
                                            }
                                        }

                                    }
                                    elseif($t == "meter_hapus") { 
                                        $no = $_POST['no']; 

                                        // Sama kaya meter_edit -- delete lebih ngeri daripada edit, gak boleh lebih longgar.
                                        $stmt_cek = mysqli_prepare($koneksi, "SELECT tgl FROM pemakaian WHERE no = ?");
                                        mysqli_stmt_bind_param($stmt_cek, "i", $no);
                                        mysqli_stmt_execute($stmt_cek);
                                        $res_cek = mysqli_stmt_get_result($stmt_cek);
                                        $data_tgl = mysqli_fetch_row($res_cek);
                                        mysqli_stmt_close($stmt_cek);
                                        if ($data_tgl) {
                                            $selisih_cek = date_diff(date_create($data_tgl[0]), date_create())->days;
                                            if ($level == "petugas" && $selisih_cek > 30) {
                                                echo "<script>alert('Akses ditolak. Data ini sudah lebih dari 30 hari dan terkunci untuk petugas.'); window.location.replace('index.php?p=catat_edit_meter');</script>";
                                                exit();
                                            }
                                        }
                                        
                                        $stmt = mysqli_prepare($koneksi, "DELETE FROM pemakaian WHERE no=?");
                                        mysqli_stmt_bind_param($stmt, "i", $no);
                                        mysqli_stmt_execute($stmt);
                                        
                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            $air->catat_log($_SESSION['user'], $level, 'meter_hapus', 'pemakaian', $no, 'hapus data pemakaian/meter');
                                            $_SESSION['res_meter'] = 'sukses_hapus';
                                            echo "<script>window.location.replace('index.php?p=catat_edit_meter');</script>";
                                            exit();
                                        } else {
                                            $_SESSION['res_meter'] = 'gagal_hapus';
                                            echo "<script>window.location.replace('index.php?p=catat_edit_meter');</script>";
                                            exit();
                                        }
                                    }
                                    elseif($t == "profil_edit") {
                                        // OWNERSHIP, BUKAN ROLE: semua level boleh (ga masuk $aksi_admin_saja dkk di atas),
                                        // TAPI cuma baris dia sendiri. SENGAJA ga baca username/level/status/tipe dari POST --
                                        // itu bukan urusan warga ganti sendiri. Target selalu dari session, titik.
                                        $user_target = $_SESSION['user'];
                                        $pass        = $_POST['passwet'];
                                        $nama        = $_POST['nama'];
                                        $alamat      = $_POST['alamat'];
                                        $kota        = $_POST['kota'];
                                        $tlp         = $_POST['tlp'];

                                        if (empty($pass)) {
                                            $stmt = mysqli_prepare($koneksi, "UPDATE login SET nama=?, alamat=?, kota=?, tlp=? WHERE username=?");
                                            mysqli_stmt_bind_param($stmt, "sssss", $nama, $alamat, $kota, $tlp, $user_target);
                                            mysqli_stmt_execute($stmt);
                                        } else {
                                            $pass2 = password_hash($pass, PASSWORD_DEFAULT);
                                            $stmt = mysqli_prepare($koneksi, "UPDATE login SET password=?, nama=?, alamat=?, kota=?, tlp=? WHERE username=?");
                                            mysqli_stmt_bind_param($stmt, "ssssss", $pass2, $nama, $alamat, $kota, $tlp, $user_target);
                                            mysqli_stmt_execute($stmt);
                                        }

                                        if (mysqli_affected_rows($koneksi) > 0) {
                                            $air->catat_log($user_target, $level, 'profil_edit', 'login', $user_target, empty($pass) ? 'ubah data diri sendiri' : 'ubah data diri sendiri + ganti password');
                                            $_SESSION['res_settings'] = 'sukses_edit';
                                            echo "<script>window.location.replace('index.php?p=settings');</script>";
                                            exit();
                                        } else {
                                            $_SESSION['res_settings'] = 'tanpa_perubahan';
                                            echo "<script>window.location.replace('index.php?p=settings');</script>";
                                            exit();
                                        }
                                    }
                                    
                            } // <--- SEKARANG DI SINI BARU BENER TEMPATNYA UNTUK MENUTUP 'if(isset($_POST['tombol']))' UTAMA!
                            
                            // SAMBUNGKAN KEMBALI AKSI GET SECARA RESMI SEBAGAI PILIHAN KEDUA
                            if(isset($_GET['p'])) {
                                $p=$_GET ['p'];

                                // GUARD OTORISASI: sama kayak versi POST, cek level session sendiri
                                if ($p=="user_edit" && $level != "admin") {
                                    echo "<script>alert('Akses ditolak. Fitur ini khusus admin.'); window.location.replace('index.php');</script>";
                                    exit();
                                } elseif ($p=="tarif_edit" && !in_array($level, array("admin", "bendahara"))) {
                                    echo "<script>alert('Akses ditolak. Fitur ini khusus admin/bendahara.'); window.location.replace('index.php');</script>";
                                    exit();
                                } elseif ($p=="meter_edit" && !in_array($level, array("admin", "bendahara", "petugas"))) {
                                    echo "<script>alert('Akses ditolak.'); window.location.replace('index.php');</script>";
                                    exit();
                                }

                                if($p=="user_edit") {
                                        
                                        $user=$_GET['user'];
                                        // echo "masuk kesini untuk ngedit user: $user";
                                            // Gak ambil kolom 'password' lagi -- hash gak pernah butuh keluar dari DB buat halaman ini.
                                            $stmt = mysqli_prepare($koneksi, "SELECT nama, alamat, kota, tlp, level, tipe, status FROM login WHERE username=?");
                                            mysqli_stmt_bind_param($stmt, "s", $user);
                                            mysqli_stmt_execute($stmt);
                                            $q = mysqli_stmt_get_result($stmt);
                                            $d=mysqli_fetch_row($q);

                                            $nama   = $d[0];
                                            $alamat = $d[1];
                                            $kota   = $d[2];
                                            $tlp    = $d[3];
                                            $level_target  = $d[4]; // KODE ASLI pake nama "$level" -> nimpa level session sendiri, ganti biar gak ganggu guard di atas
                                            $tipe   = $d[5];
                                            $status = $d[6];
                                            
                                            // 3. JEMBATAN LOGIKA PENYELAMAT HTML 
                                            // Password SENGAJA dikosongkan -- jangan pernah roundtrip hash asli ke client (Bagian 1, poin 3, Gemini).
                                            $_POST['passwet'] = "";
                                            $_POST['nama']    = $nama;
                                            $_POST['alamat']  = $alamat;
                                            $_POST['kota']    = $kota;
                                            $_POST['tlp']     = $tlp;
                                            $_POST['yuser']   = $user; // Untuk mengisi kolom username  

                                    } elseif($p == "tarif_edit") {
                                        $id_tarif = $_GET['id_tarif']; 
                                        
                                        // Ambil data dari database
                                        $stmt = mysqli_prepare($koneksi, "SELECT tipe_tarif, tarif, status FROM tarif WHERE id_tarif=?");
                                        mysqli_stmt_bind_param($stmt, "s", $id_tarif);
                                        mysqli_stmt_execute($stmt);
                                        $q_t = mysqli_stmt_get_result($stmt);
                                        $d_t = mysqli_fetch_row($q_t);
                                        
                                        // Suntikkan ke POST agar otomatis muncul di form HTML-mu
                                        $_POST['id_tarif']   = $id_tarif;
                                        $_POST['tipe_tarif'] = $d_t[0];
                                        $_POST['tarif']      = $d_t[1];
                                        $_POST['status']     = $d_t[2];

                                    } elseif($p == "meter_edit") {
                                        $no = $_GET['no']; // ngedit meter
                                        
                                        // Ambil data dari database
                                        $stmt = mysqli_prepare($koneksi, "SELECT username, meter_awal, meter_akhir FROM pemakaian WHERE no=?");
                                        mysqli_stmt_bind_param($stmt, "i", $no);
                                        mysqli_stmt_execute($stmt);
                                        $q_m = mysqli_stmt_get_result($stmt);
                                        $d_m = mysqli_fetch_row($q_m);
                                        
                                        // Suntikkan ke POST agar otomatis muncul di form HTML-mu
                                        $_POST['username']   = $d_m[0];
                                        
                                        $_POST['meter_awal'] = $d_m[1];
                                        $_POST['meter_akhir'] = $d_m[2];
                                        $stmt = mysqli_prepare($koneksi, "SELECT status FROM pemakaian WHERE no=?");
                                        mysqli_stmt_bind_param($stmt, "i", $no);
                                        mysqli_stmt_execute($stmt);
                                        $q_stat = mysqli_stmt_get_result($stmt);
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
                                <form action="" method="post" id="user_form"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">           
                                <div class="mb-3">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="yuser" value="<?php echo isset($_POST['yuser']) ? htmlspecialchars($_POST['yuser']) : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="passwet" value="<?php echo isset($_POST['passwet']) ? htmlspecialchars($_POST['passwet']) : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nama">Nama</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required>
                                </div>
                                
                            <div class="mb-3">
                                    <label for="alamat">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="kota">Kota</label>
                                    <input type="text" class="form-control" id="kota" name="kota" value="<?php echo isset($_POST['kota']) ? htmlspecialchars($_POST['kota']) : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="telepon">Telepon</label>
                                    <input type="text" class="form-control" id="telepon" name="tlp" value="<?php echo isset($_POST['tlp']) ? htmlspecialchars($_POST['tlp']) : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="level">Level</label>
                                    <select class="form-select" id="level" name="level" required>
                                        <?php
                                        // Looping PHP untuk memunculkan pilihan level
                                        $level_array = array('admin', 'bendahara', 'petugas', 'warga');
                                        $level_form = isset($level_target) ? $level_target : (isset($_POST['level']) ? htmlspecialchars($_POST['level']) : '');
                                        foreach ($level_array as $lv2) {
                                            if($level_form==$lv2) $selected= "SELECTED"; 
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
                                <?php
                                if (isset($_SESSION['res_user'])) {
                                    if ($_SESSION['res_user'] == 'sukses_edit') {
                                        echo "<div class='alert alert-success alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-circle-check me-2'></i><strong>Sukses!</strong> Data user berhasil diubah.</div>";
                                    } elseif ($_SESSION['res_user'] == 'tanpa_perubahan') {
                                        echo "<div class='alert alert-primary alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-circle-info me-2'></i><strong>Info:</strong> Data user disimpan tanpa perubahan.</div>";
                                    } elseif ($_SESSION['res_user'] == 'sukses_hapus') {
                                        echo "<div class='alert alert-success alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-trash-can me-2'></i><strong>Sukses!</strong> Akun beserta riwayat airnya terhapus bersih.</div>";
                                    } elseif ($_SESSION['res_user'] == 'gagal_hapus') {
                                        echo "<div class='alert alert-danger alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-triangle-exclamation me-2'></i><strong>Gagal:</strong> User gagal dihapus.</div>";
                                    }
                                    unset($_SESSION['res_user']);
                                }
                                ?>
                            
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
                                        // GERBANG SERVER-SIDE: cuma admin yang boleh liat baris data user (nama/alamat/telp warga+staff).
                                        // Sebelumnya tabel ini kekirim penuh ke SEMUA role, cuma disembunyiin lewat .hide() di air.js -> bocor total via View Source.
                                        if ($level == "admin") {
                                         $q=mysqli_query($koneksi,"SELECT username,nama,alamat,kota,tlp,level,tipe,status FROM login ORDER BY level ASC");
                                         while($d=mysqli_fetch_row($q)) {
                                            $user    = htmlspecialchars($d[0]);
                                            $nama    = htmlspecialchars($d[1]);
                                            $alamat  = htmlspecialchars($d[2]);
                                            $kota    = htmlspecialchars($d[3]);
                                            $telp    = htmlspecialchars($d[4]);
                                            $u_level = htmlspecialchars($d[5]); // SOLUSI: Menggunakan $u_level agar tidak merusak level admin login utama
                                            $tipe    = htmlspecialchars($d[6]);
                                            $status  = htmlspecialchars($d[7]);

                                            echo "<tr>
                                                    <td>$user</td>
                                                    <td>$nama</td>
                                                    <td>$alamat</td>
                                                    <td>$kota</td>
                                                    <td>$telp</td>
                                                    <td>$u_level</td> <td>$tipe</td>
                                                    <td>$status</td>
                                                    <td>
    <a href='index.php?p=user_edit&user=$user'><button type='button' class='btn btn-outline-primary btn-sm'>Ubah</button></a>
    <button type='button' class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#myModal' data-user='$user'>Hapus</button>
</td>

                                                </tr>";
                                         }
                                        } else {
                                            echo "<tr><td colspan='9' class='text-center text-muted'>Akses ditolak: data ini khusus admin.</td></tr>";
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
        <form action="" method="post" id="tarif_form"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="id_tarif">ID Tarif</label>
                <input type="text" class="form-control" id="id_tarif" name="id_tarif" value="<?php echo isset($_POST['id_tarif']) ? htmlspecialchars($_POST['id_tarif']) : '' ?>" required>
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
                <input type="number" class="form-control" id="tarif" name="tarif" value="<?php echo isset($_POST['tarif']) ? htmlspecialchars($_POST['tarif']) : '' ?>" required>
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
        <?php
                                if (isset($_SESSION['res_tarif'])) {
                                    if ($_SESSION['res_tarif'] == 'sukses_edit') {
                                        echo "<div class='alert alert-success alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-circle-check me-2'></i><strong>Sukses!</strong> Data tarif berhasil diubah.</div>";
                                    } elseif ($_SESSION['res_tarif'] == 'tanpa_perubahan') {
                                        echo "<div class='alert alert-primary alert-dismissible fade show'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-circle-info me-2'></i><strong>Info:</strong> Data tarif disimpan tanpa perubahan.</div>";
                                    }
                                    unset($_SESSION['res_tarif']);
                                }
                                ?>
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
                // GERBANG SERVER-SIDE: samain sama syarat nulis (tarif_edit) -> admin/bendahara doang
                if (in_array($level, array("admin", "bendahara"))) {
                $q_tarif = mysqli_query($koneksi,"SELECT id_tarif, tipe_tarif, tarif, status FROM tarif ORDER BY id_tarif ASC");
                while($d_tarif = mysqli_fetch_row($q_tarif)) {
                    $id_tarif   = htmlspecialchars($d_tarif[0]);
                    $tipe_tarif = htmlspecialchars($d_tarif[1]);
                    $tarif      = htmlspecialchars($d_tarif[2]);
                    $status     = htmlspecialchars($d_tarif[3]);

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
                } else {
                    echo "<tr><td colspan='5' class='text-center text-muted'>Akses ditolak.</td></tr>";
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
    
       <?php
                                // =========================================================================
                                // PERBAIKAN MUTLAK: Filter Respon Berdasarkan Hak Akses Role & Perbaiki Bug air.js
                                // =========================================================================
                                if (isset($_SESSION['res_meter'])) {
                                    if ($_SESSION['res_meter'] == 'sukses_add') {
                                        echo "<div class='alert alert-success alert-dismissible fade show' id='alert-meter'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-circle-check me-2'></i><strong>Sukses!</strong> Data pencatatan meteran baru warga berhasil ditambahkan lekk...</div>";
                                    } 
                                    elseif ($_SESSION['res_meter'] == 'sukses_edit') {
                                        // Jika yang login Bendahara, tampilkan teks khusus Pembayaran
                                        if ($level == 'bendahara') {
                                            echo "<div class='alert alert-success alert-dismissible fade show' id='alert-meter'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-circle-check me-2'></i><strong>Sukses!</strong> Status pembayaran warga berhasil diperbarui lekk...</div>";
                                        } 
                                        // Jika Admin atau Petugas, tampilkan teks khusus Angka Meteran
                                        else {
                                            echo "<div class='alert alert-success alert-dismissible fade show' id='alert-meter'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-circle-check me-2'></i><strong>Sukses!</strong> Data angka meteran warga berhasil diperbarui lekk...</div>";
                                        }
                                    } 
                                    // FIX "KOK GINI": Tambahkan class alert-success ke dalam alert-primary agar air.js mau menutup form secara otomatis
                                    elseif ($_SESSION['res_meter'] == 'tanpa_perubahan') {
                                        echo "<div class='alert alert-success alert-primary alert-dismissible fade show' id='alert-meter'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-circle-info me-2'></i><strong>Info:</strong> Data disimpan tanpa ada perubahan fisik.</div>";
                                    } 
                                    elseif ($_SESSION['res_meter'] == 'sukses_hapus') {
                                        echo "<div class='alert alert-success alert-dismissible fade show' id='alert-meter'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-trash-can me-2'></i><strong>Sukses!</strong> Data meteran warga berhasil dihapus dari sistem!</div>";
                                    } 
                                    elseif ($_SESSION['res_meter'] == 'gagal_hapus') {
                                        echo "<div class='alert alert-danger alert-dismissible fade show' id='alert-meter'><button type='button' class='btn-close' data-bs-dismiss='alert'></button><i class='fa-solid fa-triangle-exclamation me-2'></i><strong>Gagal:</strong> Data pencatatan meteran warga gagal dihapus dari database.</div>";
                                    }
                                    unset($_SESSION['res_meter']); 
                                }
                                ?>
        
        <table id="meter_table">
            <thead>
                <tr>
                    <th>Nama Warga</th>
                    <th>Tanggal & Waktu</th>
                    <th>Meter Awal (m³)</th>
                    <th>Meter Akhir (m³)</th>
                    <th>Pemakaian (m³)</th>
                   <?php 
                    if($dt_user[2] != "petugas") { 
                         echo "<th>Tagihan</th><th>Status</th>";
                    }
                    ?>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // GERBANG SERVER-SIDE: samain sama syarat nulis (aksi_pencatat_meter) -> admin/bendahara/petugas.
                // warga sebelumnya dapat tabel FULL semua warga (nama+data meter orang lain) via view-source,
                // plus tombol Ubah/Hapus nongol juga buat dia gara-gara else-branch di bawah gak eksklusif ke petugas.
                if (in_array($level, array("admin", "bendahara", "petugas"))) {
                $q_pemakaian = mysqli_query($koneksi,"SELECT no, username, meter_awal, meter_akhir, pemakaian, tgl, waktu, tagihan, status FROM pemakaian ORDER BY tgl DESC, username ASC");
                while($d_pemakaian = mysqli_fetch_row($q_pemakaian)) {
                    $no         = $d_pemakaian[0];
                    $dt_user2   = $air->dt_user($d_pemakaian[1]); 
                    $nama       = htmlspecialchars($dt_user2[0]);
                    $meter_awal = $d_pemakaian[2];
                    $meter_akhir = $d_pemakaian[3];
                    $pemakaian  = $d_pemakaian[4];
                    $tgl        = $air->tgl_balik($d_pemakaian[5]);
                    $waktu      = $d_pemakaian[6];
                    $tagihan    = $d_pemakaian[7];
                    $status_byr = $d_pemakaian[8];

                    $tagihan_rp = "Rp " . number_format($tagihan, 0, ',', '.');
                    $badge_status = ($status_byr == "LUNAS") ? "<span class='badge bg-success'>LUNAS</span>" : "<span class='badge bg-danger'>BELUM LUNAS</span>";

                    $level_login = $dt_user[2]; 
                    $tgl_tabel = date_create($d_pemakaian[5]);
                    $tgl_sekarang = date_create();
                    $diff = date_diff($tgl_tabel,$tgl_sekarang);
                    $selisih = $diff->days;

                    echo "<tr>
                            <td>$nama</td>
                            <td>$tgl $waktu | ".date("Y-m-d")." $selisih hari</td>
                            <td>$meter_awal</td>
                            <td>$meter_akhir</td>
                            <td>$pemakaian</td> ";
                     if ($level_login != "petugas") { 
                         echo "<td>$tagihan_rp</td><td>$badge_status</td>";
                     }    
                     if ($level_login == "admin" || $level_login == "bendahara") {
                         echo "<td>
                                <a href='index.php?p=meter_edit&no=$no'><button type='button' class='btn btn-outline-primary btn-sm'>Ubah</button></a>
                                <button type='button' class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#modalMeter' data-no='$no'>Hapus</button>
                               </td>";
                     } else {
                         if ($selisih <= 30) {
                             echo "<td>
                                    <a href='index.php?p=meter_edit&no=$no'><button type='button' class='btn btn-outline-primary btn-sm'>Ubah</button></a>
                                    <button type='button' class='btn btn-outline-danger btn-sm' data-bs-toggle='modal' data-bs-target='#modalMeter' data-no='$no'>Hapus</button>
                                   </td>";
                         } else {
                             echo "<td><span class='badge bg-secondary'>Terkunci</span></td>";
                         }
                     }
                     echo "</tr>";
                }
                } else {
                    echo "<tr><td colspan='7' class='text-center text-muted'>Akses ditolak.</td></tr>";
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
        if (isset($_GET['p']) && $_GET['p'] == "meter_edit") $dis = 'disabled';
        else $dis = "";
        ?>
        <form action="" method="post" id="meter_form"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="username">Nama Warga</label>
                <select class="form-select" id="username" name="username" required <?php echo $dis; ?>>
                    <option value="">Nama Warga</option>
                    <?php
                    $qw = mysqli_query($koneksi,"SELECT username,nama FROM login WHERE level='warga'");
                    while ($dw = mysqli_fetch_row($qw)) {
                        $selected = (isset($_POST['username']) && $_POST['username'] == $dw[0]) ? "selected" : "";
                        echo "<option value='$dw[0]' $selected>$dw[1]</option>";
                    }
                    ?>
                </select>
                <div id="info_last_input" class="small text-muted mt-1"></div>
                <div id="js_alert_container" class="mt-2"></div>
            </div>
            
            <?php 
            // Meter awal WAJIB readonly jika Bendahara ATAU sedang berada di halaman meter_edit
            $kunci_meter_awal = ($dt_user[2] == "bendahara" || (isset($_GET['p']) && $_GET['p'] == "meter_edit")) ? "readonly style='background-color: #e8f0fe;'" : ""; 
            
            // Meter akhir HANYA readonly jika Bendahara (Petugas masih boleh edit buat benerin typo)
            $kunci_meter_akhir = ($dt_user[2] == "bendahara") ? "readonly style='background-color: #e8f0fe;'" : "";
            ?>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="meter_awal">Meter Awal (m³)</label>
                    <input type="number" class="form-control" id="meter_awal" name="meter_awal" value="<?php echo isset($_POST['meter_awal']) ? htmlspecialchars($_POST['meter_awal']) : ''; ?>" required <?php echo $kunci_meter_awal; ?>>
                </div>
                <div class="col-md-6">
                    <label for="meter_akhir">Meter Akhir (m³)</label>
                    <input type="number" class="form-control" id="meter_akhir" name="meter_akhir" value="<?php echo isset($_POST['meter_akhir']) ? htmlspecialchars($_POST['meter_akhir']) : ''; ?>" required <?php echo $kunci_meter_akhir; ?>>
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
                if(isset($_GET['p']) && $_GET['p'] == "meter_edit"){
                    $no_edit = $_GET['no'];
                    echo "<input type='hidden' name='no' value='$no_edit'>";
                    echo '<button type="submit" class="btn btn-primary" name="tombol" value="meter_edit">Simpan</button>';
                } else {
                    echo '<button type="submit" class="btn btn-primary" name="tombol" value="meter_add">Simpan</button>';
                }
                ?>
            </div>

            <?php
            $q_last = mysqli_query($koneksi, "SELECT username, meter_akhir, tgl, waktu FROM pemakaian ORDER BY tgl DESC, waktu DESC");
            $data_last_meter = array();
            while($d_last = mysqli_fetch_assoc($q_last)) {
                $u = $d_last['username'];
                $is_bulan_ini = (date('Y-m', strtotime($d_last['tgl'])) == date('Y-m')) ? true : false;
                if(!isset($data_last_meter[$u])) {
                    $data_last_meter[$u] = array(
                        'meter_akhir' => $d_last['meter_akhir'],
                        'sudah_input_bulan_ini' => $is_bulan_ini,
                        'tgl_terakhir' => $air->tgl_balik($d_last['tgl']) . " " . $d_last['waktu'],
                        'tgl_bulan_lalu' => '-'
                    );
                } else {
                    if ($data_last_meter[$u]['sudah_input_bulan_ini'] && $data_last_meter[$u]['tgl_bulan_lalu'] == '-') {
                        $data_last_meter[$u]['tgl_bulan_lalu'] = $air->tgl_balik($d_last['tgl']) . " " . $d_last['waktu'];
                    }
                }
            }
            ?>
            <script>
            const currentLevel = typeof level_user !== 'undefined' ? level_user : <?php echo json_encode($dt_user[2]); ?>;
            const currentParam = <?php echo json_encode(isset($_GET['p']) ? $_GET['p'] : ''); ?>;
            const riwayatMeter = <?php echo json_encode($data_last_meter); ?>;

            document.querySelector('#meter_form #username').addEventListener('change', function() {
                const userPilih = this.value;
                const inputMeterAwal = document.getElementById('meter_awal');
                const inputMeterAkhir = document.getElementById('meter_akhir');
                const infoLastInput = document.getElementById('info_last_input');
                const alertContainer = document.getElementById('js_alert_container');
                
                infoLastInput.innerHTML = "";
                alertContainer.innerHTML = "";
                
               // =========================================================================
            // JAVASCRIPT PROTEKSI: Kunci Mutlak Ketika Berada di Halaman Edit Meter
            // =========================================================================
            if (currentParam === 'meter_edit') {
                // Siapa pun aktornya (Petugas/Bendahara/Admin), Meter Awal KAGAK BOLEH BERUBAH!
                inputMeterAwal.readOnly = true;
                inputMeterAwal.style.backgroundColor = '#e8f0fe';
                
                if (currentLevel === 'bendahara') {
                    // Jika Bendahara, kunci juga meter akhirnya
                    inputMeterAkhir.readOnly = true;
                    inputMeterAkhir.style.backgroundColor = '#e8f0fe';
                } else {
                    // Jika Petugas/Admin, biarkan meter akhir terbuka untuk perbaikan typo
                    inputMeterAkhir.readOnly = false;
                    inputMeterAkhir.style.backgroundColor = '';
                }
                return; // Keluar dari fungsi, amankan data asli cetakan PHP
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
                    inputMeterAwal.value = dataWarga.meter_akhir;
                    inputMeterAwal.style.backgroundColor = '#e8f0fe'; 
                    inputMeterAwal.readOnly = true; 
                    
                    if (currentLevel !== 'bendahara') {
                        inputMeterAkhir.value = ''; 
                        inputMeterAkhir.readOnly = false; 
                        inputMeterAkhir.style.backgroundColor = '';
                    }
                    
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
                    if (currentLevel !== 'bendahara') {
                        inputMeterAwal.value = '0'; 
                        inputMeterAwal.style.backgroundColor = '#e8f0fe'; 
                        inputMeterAwal.readOnly = true; 
                        inputMeterAkhir.value = '';
                        inputMeterAkhir.readOnly = false;
                        inputMeterAkhir.style.backgroundColor = '';
                    }
                    infoLastInput.innerHTML = `<i class="fa-solid fa-circle-info text-info"></i> Warga baru: Belum ada riwayat input meteran sebelumnya. Otomatis dimulai dari 0 m³.`;
                }
            });

            window.addEventListener('DOMContentLoaded', (event) => {
                const alertUser = document.getElementById('alert-user');
                if (alertUser && alertUser.classList.contains('alert-danger')) {
                    const userList = document.getElementById('user_list');
                    const userAdd = document.getElementById('user_add');
                    
                    if (document.getElementById('summary')) document.getElementById('summary').style.display = 'none';
                    if (document.getElementById('chart')) document.getElementById('chart').style.display = 'none';
                    if (document.getElementById('pilih_waktu')) document.getElementById('pilih_waktu').style.display = 'none';
                    
                    if (userList) userList.style.display = 'none';
                    if (userAdd) userAdd.style.display = 'block';
                }
                
                const usernameSelect = document.querySelector('#meter_form #username');
                if (currentParam === 'meter_edit') {
                    if(document.getElementById('meter_awal')) {
                        document.getElementById('meter_awal').readOnly = true;
                        document.getElementById('meter_awal').style.backgroundColor = '#e8f0fe';
                    }
                }
                
                if (currentLevel === 'bendahara') {
                    if(document.getElementById('meter_awal')) {
                        document.getElementById('meter_awal').readOnly = true;
                        document.getElementById('meter_awal').style.backgroundColor = '#e8f0fe';
                    }
                    if(document.getElementById('meter_akhir')) {
                        document.getElementById('meter_akhir').readOnly = true;
                        document.getElementById('meter_akhir').style.backgroundColor = '#e8f0fe';
                    }
                }
                if(usernameSelect && usernameSelect.value !== "") {
                    usernameSelect.dispatchEvent(new Event('change'));
                }
            });
            </script>

             </form>
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
                $stmt = mysqli_prepare($koneksi, "SELECT tgl, waktu, kd_tarif, meter_awal, meter_akhir, pemakaian, tagihan, status FROM pemakaian WHERE username=? ORDER BY tgl DESC");
                mysqli_stmt_bind_param($stmt, "s", $sesi_user);
                mysqli_stmt_execute($stmt);
                $q_warga = mysqli_stmt_get_result($stmt);
                
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
        <form action="" method="post"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button> <button type="submit" class="btn btn-danger" name="tombol" value="user_hapus">Ya</button> </form>
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
                                                                <form action="" method="post"> <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
                <form action="" method="post"> <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button> 
                    <button type="submit" class="btn btn-danger" name="tombol" value="meter_hapus">Ya</button> 
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Data form settings -- SELALU dari session, TIDAK PERNAH dari GET/POST manapun. Ga ada parameter
// username buat halaman ini sama sekali, jadi ga ada celah IDOR "ganti username di URL edit punya orang lain".
$stmt = mysqli_prepare($koneksi, "SELECT nama, alamat, kota, tlp FROM login WHERE username=?");
mysqli_stmt_bind_param($stmt, "s", $_SESSION['user']);
mysqli_stmt_execute($stmt);
$d_profil = mysqli_fetch_row(mysqli_stmt_get_result($stmt));
?>
<div class="card mb-4" id="settings_page" style="display:none;">
    <div class="card-header">
        <i class="fas fa-gear text-secondary me-2"></i> Pengaturan Akun
    </div>
    <div class="card-body">
        <?php
        if (isset($_SESSION['res_settings'])) {
            if ($_SESSION['res_settings'] == 'sukses_edit') {
                echo "<div class='alert alert-success alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Data berhasil diperbarui.</div>";
            } elseif ($_SESSION['res_settings'] == 'tanpa_perubahan') {
                echo "<div class='alert alert-warning alert-dismissible fade show'><button type=button class=btn-close data-bs-dismiss=alert></button>Tidak ada perubahan tersimpan.</div>";
            }
            unset($_SESSION['res_settings']);
        }
        ?>
        <form action="" method="post" id="settings_form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="set_nama">Nama</label>
                <input type="text" class="form-control" id="set_nama" name="nama" value="<?php echo htmlspecialchars($d_profil[0] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="set_alamat">Alamat</label>
                <input type="text" class="form-control" id="set_alamat" name="alamat" value="<?php echo htmlspecialchars($d_profil[1] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="set_kota">Kota</label>
                <input type="text" class="form-control" id="set_kota" name="kota" value="<?php echo htmlspecialchars($d_profil[2] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="set_tlp">No. Telepon</label>
                <input type="text" class="form-control" id="set_tlp" name="tlp" value="<?php echo htmlspecialchars($d_profil[3] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="set_pass">Password Baru <span class="text-muted small">(kosongkan kalau tidak ingin ganti)</span></label>
                <input type="password" class="form-control" id="set_pass" name="passwet" placeholder="••••••••" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary" name="tombol" value="profil_edit">Simpan Perubahan</button>
        </form>
    </div>
</div>

<?php
// Query cuma jalan kalau levelnya emang boleh liat -- warga yang paksa buka ?p=activity_log lewat URL
// tetap ga dapet baris data apa pun, cuma dapet pesan ditolak di bawah.
if ($level == "admin" || $level == "bendahara") {
    $q_log = mysqli_query($koneksi, "SELECT actor_username, actor_level, aksi, tabel_target, id_target, detail, created_at FROM audit_log ORDER BY created_at DESC LIMIT 100");
}
?>
<div class="card mb-4" id="activity_log_page" style="display:none;">
    <div class="card-header">
        <i class="fas fa-clock-rotate-left text-dark me-2"></i> Rekam Jejak Aktivitas <span class="text-muted small">(100 terbaru)</span>
    </div>
    <div class="card-body">
        <?php if ($level == "admin" || $level == "bendahara") { ?>
        <div class="table-responsive">
            <table class="table table-striped table-sm datatable-log">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pelaku</th>
                        <th>Role</th>
                        <th>Aksi</th>
                        <th>Target</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($q_log)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['actor_username']); ?></td>
                        <td><?php echo htmlspecialchars($row['actor_level']); ?></td>
                        <td><?php echo htmlspecialchars($row['aksi']); ?></td>
                        <td><?php echo htmlspecialchars($row['tabel_target'] . ' #' . $row['id_target']); ?></td>
                        <td><?php echo htmlspecialchars($row['detail']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <div class="alert alert-warning">Halaman ini khusus admin/bendahara.</div>
        <?php } ?>
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
