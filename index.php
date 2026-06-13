<?php
// koneksi ke database
include './assets/func.php';
$air = new kelas_air;
$koneksi=$air->koneksi();

// masukkan data ke user tabel
// $pass=password_hash("aiman", PASSWORD_DEFAULT);
// mysqli_query($koneksi,"INSERT INTO login(username,password,nama,alamat,kota,tlp,level,tipe,status) VALUES ('aiman1','$pass','Aiman','Polines','Semarang','024111','bendahara','-','AKTIF')");
// if(mysqli_affected_rows($koneksi) > 0) echo "Data berhasil masuk...";
// else echo "Data GAGAL masuk...";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Login - Si-Air Kelompok 1</title> 
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
                                            $username=$_POST['username'];
                                            $password=$_POST['password'];
                                            
                                            $qc=mysqli_query($koneksi, "SELECT username,password FROM login WHERE username='$username'");
                                            $dc=mysqli_fetch_row($qc);

                                            if(!empty($dc[0])) $user_cek=$dc[0];
                                            
                                            if(!empty($user_cek)) {
                                                $pass_cek=$dc[1];

                                                if(password_verify($password, $pass_cek)) {
                                                    session_start();
                                                    $_SESSION['user']=$username;
                                                    $_SESSION['pass']=$password;
                                                    echo "<script>window.location.replace('./login/index.php')</script>";
                                                } else {
                                                    echo "<div class=\"alert alert-danger alert-dismissible fade show shadow-sm\">
                                                            <button type=button class=btn-close data-bs-dismiss=alert></button>
                                                            <strong>Gagal!</strong> Password tidak valid. 
                                                        </div>";
                                                }
                                            } else {
                                                echo "<div class=\"alert alert-danger alert-dismissible fade show shadow-sm\">
                                                        <button type=button class=btn-close data-bs-dismiss=alert></button>
                                                        <strong>Gagal!</strong> Username tidak ditemukan. 
                                                    </div>";
                                            }
                                         }
                                        ?>
                                        <form method="post">
                                            <div class="form-floating mb-3 mt-2">
                                                <input class="form-control" id="inputUser" type="text" placeholder="Username" name="username" required style="border-radius: 10px;" />
                                                <label for="inputUser"><i class="fas fa-user text-muted me-2"></i>Username</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="inputPassword" type="password" placeholder="Password" name="password" required style="border-radius: 10px;" />
                                                <label for="inputPassword"><i class="fas fa-lock text-muted me-2"></i>Password</label>
                                            </div>
                                            <div class="form-check mb-3 mt-3 ms-1 text-start">
                                            <input class="form-check-input" id="inputRememberPassword" type="checkbox" value="" style="transform: scale(1.1); cursor: pointer;" />
                                            <label class="form-check-label text-muted small" for="inputRememberPassword" style="cursor: pointer; user-select: none;">
                                                Remember Me
                                            </label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-3 px-1">
                                            <a class="text-decoration-none" href="password.html" style="color: #0d6efd; font-size: 0.9rem;">Lupa sandi?</a>
                                            <div style="font-size: 0.9rem; color: #333;">
                                                Belum punya akun? 
                                                <a href="register.html" class="text-decoration-none" style="color: #0d6efd;">Register</a>
                                            </div>
                                        </div>
                                            
                                            <div class="d-grid mt-4 mb-2">
                                                <button type="submit" name="tombol" class="btn btn-primary btn-lg fw-bold shadow-sm" style="background: linear-gradient(to right, #1e3c72, #2a5298); border: none; border-radius: 10px;">Login</button>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>