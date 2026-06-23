<?php
session_start();
// Ambil fungsi utama dari sistemmu
include 'assets/func.php';
$air = new kelas_air;
$koneksi = $air->koneksi();

echo "<h3>Memulai Injeksi Akun Spesifik 'warga' (Januari - Juni)...</h3>";

$username = "warga";
$nama = "Warga Default";
// Mengunci password menjadi kata mentah "warga" sesuai permintaanmu
$password = password_hash("warga", PASSWORD_DEFAULT); 
$tipe = "RT"; // Komposisi dikunci murni sebagai Rumah Tangga (RT)

// 1. EKSEKUSI PEMBUATAN AKUN (Hanya jika username 'warga' belum terdaftar)
$cek_user = mysqli_query($koneksi, "SELECT username FROM login WHERE username='$username'");
if (mysqli_num_rows($cek_user) == 0) {
    mysqli_query($koneksi, "INSERT INTO login (username, password, nama, alamat, kota, tlp, level, tipe, status) 
    VALUES ('$username', '$password', '$nama', 'Jl. Warga Default Utama No. 1', 'Semarang', '081222333444', 'warga', '$tipe', 'AKTIF')");
    echo "<p style='color:blue;'>Akun login untuk username <b>warga</b> berhasil dibuat.</p>";
} else {
    echo "<p style='color:orange;'>Username <b>warga</b> sudah terdaftar di database. Sistem langsung melompat ke injeksi riwayat pemakaian air...</p>";
}

// Ambil kode tarif secara dinamis berdasarkan fungsi yang sudah kamu punya di func.php
$kd_tarif = $air->user_to_idtarif($username);     
$harga_per_kubik = $air->id_tarif_to_tarif($kd_tarif); 

$meter_akumulasi = rand(10, 40); // Angka meteran awal acak
$tahun = 2026;

// 2. EKSEKUSI INJEKSI SIMULASI 6 BULAN (Januari s/d Juni)
for ($bln = 1; $bln <= 6; $bln++) {
    
    // Variabel organik acak per bulan
    $pemakaian_bulan_ini = rand(12, 38); 
    $meter_awal = $meter_akumulasi;
    $meter_akhir = $meter_awal + $pemakaian_bulan_ini;
    $meter_akumulasi = $meter_akhir; // Oper akumulasi ke bulan depan

    // Hitung tagihan finansial berdasarkan harga asli DB
    $tagihan = $pemakaian_bulan_ini * $harga_per_kubik;
    
    // Tanggal diacak dari tanggal 1 sampai 28
    $hari_acak = rand(1, 28);
    $tanggal = sprintf("%04d-%02d-%02d", $tahun, $bln, $hari_acak);
    
    // Waktu jam menit detik acak secara natural
    $waktu = sprintf("%02d:%02d:%02d", rand(8, 16), rand(0, 59), rand(0, 59));
    
    // Status pembayaran acak (Rasio 80% Lunas)
    $status_bayar = (rand(1, 10) <= 8) ? "LUNAS" : "BELUM LUNAS";

    // Jaring Pengaman Anti-Duplikasi: Cegah eror jika file ter-refresh di browser
    $cek_pemakaian = mysqli_query($koneksi, "SELECT no FROM pemakaian WHERE username='$username' AND MONTH(tgl)='$bln' AND YEAR(tgl)='$tahun'");
    if (mysqli_num_rows($cek_pemakaian) == 0) {
        mysqli_query($koneksi, "INSERT INTO pemakaian (username, meter_awal, meter_akhir, pemakaian, tgl, waktu, kd_tarif, tagihan, status) 
        VALUES ('$username', '$meter_awal', '$meter_akhir', '$pemakaian_bulan_ini', '$tanggal', '$waktu', '$kd_tarif', '$tagihan', '$status_bayar')");
    }
}

echo "<h3 style='color:green;'>Selesai! Akun utama 'warga' dengan password 'warga' beserta riwayat pemakaian air tipe RT selama 6 bulan telah berhasil disuntikkan ke sistem.</h3>";
echo "<p style='color:red;'>*Segera hapus file add_warga_default.php ini dari direktori server demi keamanan data.</p>";
?>