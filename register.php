<?php
session_start();
include 'config/koneksi.php';

if (isset($_SESSION['login_user'])) {
    header("Location: user/dashboard.php");
    exit;
}

$pesan = "";

if (isset($_POST['proses_register'])) {
    $nama_lengkap = mysqli_real_escape_string($mysqli, $_POST['nama_lengkap']);
    $email        = mysqli_real_escape_string($mysqli, $_POST['email']);
    $no_telepon   = mysqli_real_escape_string($mysqli, $_POST['no_telepon']);
    $alamat       = mysqli_real_escape_string($mysqli, $_POST['alamat']);
    $password     = mysqli_real_escape_string($mysqli, $_POST['password']); 
    $konfirmasi   = $_POST['confirm_password'];

    if ($password !== $konfirmasi) {
        $pesan = "<div class='alert alert-danger small'><i class='bi bi-exclamation-triangle-fill me-1'></i> Konfirmasi password tidak cocok!</div>";
    } else {
        // Cek email beserta status akunnya di database
        $cek_email = mysqli_query($mysqli, "SELECT status_akun FROM anggota WHERE email = '$email'");
        
        if (mysqli_num_rows($cek_email) > 0) {
            $data_akun = mysqli_fetch_assoc($cek_email);
            $status_sekarang = $data_akun['status_akun'];

            if ($status_sekarang == 'approved') {
                // JIKA APPROVED: Maka sudah terdaftar resmi
                $pesan = "<div class='alert alert-danger small'><i class='bi bi-exclamation-triangle-fill me-1'></i> Email ini sudah terdaftar dan aktif! Silahkan login.</div>";
            } elseif ($status_sekarang == 'pending') {
                // JIKA PENDING: Masih menunggu konfirmasi admin
                $pesan = "<div class='alert alert-warning small'><i class='bi bi-hourglass-split me-1'></i> Email ini sedang menunggu konfirmasi/persetujuan admin.</div>";
            } elseif ($status_sekarang == 'rejected') {
                // JIKA REJECTED: Dianggap belum terdaftar, hapus data lama yang ditolak agar bisa daftar ulang
                mysqli_query($mysqli, "DELETE FROM anggota WHERE email = '$email'");
                $eksekusi_daftar = true;
            }
        } else {
            // Jika tidak ditemukan di database, otomatis langsung diizinkan daftar
            $eksekusi_daftar = true;
        }

        // Proses Insert Data Baru jika lolos validasi status di atas
        if (isset($eksekusi_daftar) && $eksekusi_daftar === true) {
            $status_awal = 'pending';

            $query = "INSERT INTO anggota (nama_lengkap, email, no_telepon, alamat, password, status_akun) 
                      VALUES ('$nama_lengkap', '$email', '$no_telepon', '$alamat', '$password', '$status_awal')";
            
            if (mysqli_query($mysqli, $query)) {
                $pesan = "<div class='alert alert-success small'><i class='bi bi-check-circle-fill me-1'></i> Registrasi berhasil! Akun Anda sedang menunggu persetujuan admin. <a href='index.php' class='fw-bold text-decoration-none'>Login di sini</a></div>";
            } else {
                $pesan = "<div class='alert alert-danger small'><i class='bi bi-x-circle-fill me-1'></i> Terjadi kesalahan sistem. Coba lagi nanti.</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PojokBaca | Daftar Akun</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
  <main class="auth-page">
    <section class="auth-card">
      <a class="auth-brand" href="index.php">
        <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
        <span><strong>PojokBaca</strong><small>Silahkan Buat Akun Anda.</small></span>
      </a>
      <div class="auth-visual"><img src="assets/images/png/dasher-ui-bootstrap-5.jpg" alt="adminHMD dashboard interface"></div>
      
      <?= $pesan; ?>

      <form class="needs-validation" method="POST" action="" novalidate>
        <div class="mb-4">
          <p class="eyebrow mb-1">Anggota</p>
          <h1 class="h3 mb-1">Daftar Akun</h1>
          <p class="text-muted mb-0">Lengkapi data diri Anda untuk daftar anggota.</p>
        </div>
        <div class="mb-3">
          <label class="form-label" for="registerName">Nama Lengkap</label>
          <input class="form-control" id="registerName" type="text" name="nama_lengkap" required placeholder="Masukkan nama lengkap">
        </div>
        <div class="mb-3">
          <label class="form-label" for="registerEmail">Email</label>
          <input class="form-control" id="registerEmail" type="email" name="email" required placeholder="contoh@email.com">
        </div>
        <div class="mb-3">
          <label class="form-label" for="registerPhone">No. Telepon</label>
          <input class="form-control" id="registerPhone" type="tel" name="no_telepon" required placeholder="08xxxxxxxxxx">
        </div>
        <div class="mb-3">
          <label class="form-label" for="registerAddress">Alamat Rumah</label>
          <input class="form-control" id="registerAddress" name="alamat" required placeholder="Masukkan alamat lengkap"></input>
        </div>
        <div class="mb-3">
          <label class="form-label" for="registerPassword">Password</label>
          <input class="form-control" id="registerPassword" type="password" name="password" minlength="6" required placeholder="Minimal 6 karakter">
        </div>
        <div class="mb-4">
          <label class="form-label" for="confirmPassword">Konfirmasi Password</label>
          <input class="form-control" id="confirmPassword" type="password" name="confirm_password" minlength="6" required placeholder="Ulangi password">
        </div>
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="terms" required>
          <label class="form-check-label" for="terms">Saya menyetujui syarat & ketentuan</label>
        </div>
        <button class="btn btn-primary w-100" type="submit" name="proses_register">Daftar</button>
      </form>
      <div class="auth-footer">Sudah memiliki akun? <a href="index.php">Login</a></div>
    </section>
  </main>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>