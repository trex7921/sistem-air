<?php
session_start();

// koneksi ke database
include './assets/func.php';
$air = new kelas_air;
$koneksi=$air->koneksi();

// TOKEN CSRF buat form login ini sendiri
if (empty($_SESSION['csrf_token_login'])) {
    $_SESSION['csrf_token_login'] = bin2hex(random_bytes(32));
}

// RATE LIMIT percobaan login
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_limited = false;
$rl_check = @mysqli_prepare($koneksi, "SELECT COUNT(*) FROM login_attempts WHERE identifier=? AND attempt_time > (NOW() - INTERVAL 15 MINUTE)");
if ($rl_check) {
    mysqli_stmt_bind_param($rl_check, "s", $ip);
    mysqli_stmt_execute($rl_check);
    $rl_row = mysqli_fetch_row(mysqli_stmt_get_result($rl_check));
    if ($rl_row && $rl_row[0] >= 5) {
        $rate_limited = true;
    }
}
function catat_percobaan_gagal($koneksi, $ip) {
    $ins = @mysqli_prepare($koneksi, "INSERT INTO login_attempts (identifier, attempt_time) VALUES (?, NOW())");
    if ($ins) { mysqli_stmt_bind_param($ins, "s", $ip); mysqli_stmt_execute($ins); }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Si-Air | Login</title> 
        <link rel="icon" href="./assets/img/favicon.svg" type="image/svg+xml" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
        <link href="css/styles.css?v=2" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

        <style>
            .glass-card {
                background: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-radius: 15px;
                border: 1px solid rgba(255, 255, 255, 0.4);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            }
            .dev-badge {
                background: rgba(42, 82, 152, 0.1);
                color: #2a5298;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: 600;
                margin: 0 3px;
                border: 1px solid rgba(42, 82, 152, 0.2);
            }
        </style>
    </head>
    <body style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); height: 100vh;">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card glass-card border-0 mt-5">
                                    
                                    <div class="card-header bg-transparent border-0 pt-5 pb-2 text-center">
                                       <div class="sb-nav-link-icon"><i class="fas fa-tint fa-bounce fa-2x text-primary"></i></div> 
                                        <h3 class="font-weight-bold mb-1" style="color: #1e3c72;">Si-Air Login</h3>
                                        <p class="text-muted small mb-3">Sistem Manajemen Tagihan Air Terpadu</p>
                                        
                                        <div>
                                            <span class="dev-badge"><i class="fas fa-user-check me-1"></i> Aiman</span>
                                            <span class="dev-badge"><i class="fas fa-user-check me-1"></i> Alfian</span>
                                        </div>
                                    </div>

                                    <div class="card-body px-4 pb-4 pt-3">
                                        
<?php
if(isset($_POST['tombol'])){
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token_login'], $_POST['csrf_token'])) {
        echo "<div class=\"alert alert-danger alert-dismissible fade show shadow-sm\">
                <button type=button class=btn-close data-bs-dismiss=alert></button>
                <strong>Gagal!</strong> Sesi form kadaluarsa, muat ulang halaman dan coba lagi.
            </div>";
    } elseif ($rate_limited) {
        echo "<div class=\"alert alert-danger alert-dismissible fade show shadow-sm\">
                <button type=button class=btn-close data-bs-dismiss=alert></button>
                <strong>Gagal!</strong> Terlalu banyak percobaan gagal. Coba lagi dalam 15 menit.
            </div>";
    } else {
        $t = strtolower($_POST['tombol']);

        // --- PROSES 1: LOGIN ---
        if ($t == "login") {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            $stmt = mysqli_prepare($koneksi, "SELECT username, password FROM login WHERE username=?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $qc = mysqli_stmt_get_result($stmt);
            $dc = mysqli_fetch_row($qc);

            if ($dc) {
                if (password_verify($password, $dc[1])) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $dc[0];
                    unset($_SESSION['csrf_token_login']);

                    // FITUR REMEMBER ME: Simpan cookie username selama 30 hari
                    if (isset($_POST['remember_me'])) {
                        setcookie('remember_user', $dc[0], time() + (86400 * 30), "/");
                    } else {
                        setcookie('remember_user', '', time() - 3600, "/");
                    }

                    echo "<script>window.location.replace('./login/index.php')</script>";
                    exit();
                } else {
                    catat_percobaan_gagal($koneksi, $ip);
                    echo "<div class=\"alert alert-danger alert-dismissible fade show shadow-sm\">
                            <button type=button class=btn-close data-bs-dismiss=alert></button>
                            <strong>Gagal!</strong> Password tidak valid. 
                        </div>";
                }
            } else {
                catat_percobaan_gagal($koneksi, $ip);
                echo "<div class=\"alert alert-danger alert-dismissible fade show shadow-sm\">
                        <button type=button class=btn-close data-bs-dismiss=alert></button>
                        <strong>Gagal!</strong> Username tidak ditemukan. 
                    </div>";
            }
        }
        // --- PROSES 2: REGISTER WARGA BARU ---
        elseif ($t == "register_warga") {
            $reg_user   = trim($_POST['reg_username']);
            $reg_pass   = password_hash($_POST['reg_password'], PASSWORD_DEFAULT);
            $reg_nama   = trim($_POST['reg_nama']);
            $reg_alamat = trim($_POST['reg_alamat']);
            $reg_kota   = trim($_POST['reg_kota']);
            $reg_tlp    = trim($_POST['reg_tlp']);
            $reg_tipe   = $_POST['reg_tipe'];

            $stmt = mysqli_prepare($koneksi, "SELECT username FROM login WHERE username=?");
            mysqli_stmt_bind_param($stmt, "s", $reg_user);
            mysqli_stmt_execute($stmt);
            if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
                echo "<div class=\"alert alert-warning alert-dismissible fade show shadow-sm\">
                        <button type=button class=btn-close data-bs-dismiss=alert></button>
                        <strong>Gagal!</strong> Username '<b>".htmlspecialchars($reg_user)."</b>' sudah terdaftar. Gunakan username lain.
                    </div>";
            } else {
                $stmt_ins = mysqli_prepare($koneksi, "INSERT INTO login (username, password, nama, alamat, kota, tlp, level, tipe, status) VALUES (?, ?, ?, ?, ?, ?, 'warga', ?, 'AKTIF')");
                mysqli_stmt_bind_param($stmt_ins, "sssssss", $reg_user, $reg_pass, $reg_nama, $reg_alamat, $reg_kota, $reg_tlp, $reg_tipe);
                if (mysqli_stmt_execute($stmt_ins)) {
                    echo "<div class=\"alert alert-success alert-dismissible fade show shadow-sm\">
                            <button type=button class=btn-close data-bs-dismiss=alert></button>
                            <strong>Pendaftaran Berhasil!</strong> Akun warga Anda telah aktif. Silakan login.
                        </div>";
                } else {
                    echo "<div class=\"alert alert-danger alert-dismissible fade show shadow-sm\">
                            <button type=button class=btn-close data-bs-dismiss=alert></button>
                            <strong>Gagal!</strong> Terjadi kesalahan sistem saat mendaftar.
                        </div>";
                }
            }
        }
    }
}
?>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token_login']; ?>">
                                            <div class="form-floating mb-3 mt-2">
                                                <input class="form-control" id="inputUser" type="text" placeholder="Username" name="username" value="<?php echo htmlspecialchars($_COOKIE['remember_user'] ?? ''); ?>" required style="border-radius: 10px;" />
                                                <label for="inputUser"><i class="fas fa-user text-muted me-2"></i>Username</label>
                                            </div>
                                           <div class="form-floating mb-3 position-relative">
                                                <input class="form-control" id="inputPassword" type="password" placeholder="Password" name="password" required style="border-radius: 10px; padding-right: 45px;" />
                                                <label for="inputPassword"><i class="fas fa-lock text-muted me-2"></i>Password</label>
                                                <span id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #6c757d;">
                                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                                </span>
                                            </div>
                                            <div class="form-check mb-3 mt-3 ms-1 text-start">
                                                <input class="form-check-input" id="inputRememberPassword" type="checkbox" name="remember_me" value="1" <?php echo isset($_COOKIE['remember_user']) ? 'checked' : ''; ?> style="transform: scale(1.1); cursor: pointer;" />
                                                <label class="form-check-label text-muted small" for="inputRememberPassword" style="cursor: pointer; user-select: none;">
                                                    Remember Me
                                                </label>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-3 px-1">
                                                <a class="text-decoration-none" href="#" data-bs-toggle="modal" data-bs-target="#modalLupaSandi" style="color: #0d6efd; font-size: 0.9rem;">Lupa sandi?</a>
                                                <div style="font-size: 0.9rem; color: #333;">
                                                    Belum punya akun? 
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalRegister" class="text-decoration-none" style="color: #0d6efd;">Register</a>
                                                </div>
                                            </div>
                                                
                                            <div class="d-grid mt-4 mb-2">
                                                <button type="submit" name="tombol" value="login" class="btn btn-primary btn-lg fw-bold shadow-sm" style="background: linear-gradient(to right, #1e3c72, #2a5298); border: none; border-radius: 10px;">Login</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="card-footer bg-transparent border-top text-center py-4">
                                        <a href="profile.php" class="btn btn-outline-dark btn-sm rounded-pill px-4 fw-bold shadow-sm">
                                            <i class="fas fa-users-cog me-1"></i> Profil Tim Developer
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            
            <div id="layoutAuthentication_footer">
                <footer class="py-4 mt-auto" style="background: rgba(255,255,255,0.1);">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small text-white-50">
                            <div>Copyright &copy; Kelompok 1 - TK2B Polines <?php echo date('Y'); ?></div>
                            <div>
                                <a href="#" class="text-white-50">Privacy</a> &middot; <a href="#" class="text-white-50">Terms</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <div class="modal fade" id="modalLupaSandi" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-start">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-key me-2"></i>Bantuan Lupa Sandi</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-2">Demi keamanan data warga, reset kata sandi dilakukan melalui verifikasi pengurus:</p>
                        <div class="alert alert-info border-0 shadow-sm mb-0">
                            <i class="fas fa-info-circle me-2"></i> Silakan hubungi <b>Admin / Bendahara RT 01</b> untuk melakukan verifikasi identitas dan reset password akun Anda.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalRegister" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-start">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token_login']; ?>">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Pendaftaran Akun Warga Baru</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" name="reg_username" class="form-control" placeholder="Contoh: warga_budiman" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password</label>
                                <input type="password" name="reg_password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="reg_nama" class="form-control" placeholder="Nama sesuai KTP" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Kota</label>
                                    <input type="text" name="reg_kota" class="form-control" value="Semarang" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">No. Telepon</label>
                                    <input type="text" name="reg_tlp" class="form-control" placeholder="08123456789" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tipe Hunian</label>
                                <select name="reg_tipe" class="form-select" required>
                                    <option value="RT">Rumah Tangga (RT)</option>
                                    <option value="Kos">Kos</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Alamat Lengkap</label>
                                <textarea name="reg_alamat" class="form-control" rows="2" placeholder="Jl. Anggrek No. 12 RT 01" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="tombol" value="register_warga" class="btn btn-primary fw-bold">Daftar Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script>
            // Fitur Toggle Tampilkan / Sembunyikan Password
            document.getElementById('togglePassword').addEventListener('click', function () {
                const passwordInput = document.getElementById('inputPassword');
                const eyeIcon = document.getElementById('eyeIcon');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        </script>
    </body>
</html>