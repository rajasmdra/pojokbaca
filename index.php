<?php
session_start();
include 'config/koneksi.php'; // Pastikan file koneksi ada di folder config/

// Jika anggota sudah login, langsung arahkan ke dashboard anggota
if (isset($_SESSION['login_anggota'])) {
    header("Location: user/dashboard.php");
    exit;
}

$pesan = "";

if (isset($_POST['proses_login'])) {
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];

    // 1. Cek email di tabel anggota
    $query = "SELECT * FROM anggota WHERE email = '$email'";
    $result = mysqli_query($mysqli, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // 2. Jika email ada, verifikasi password
        if ($password === $row['password']) {
            
            // 3. Jika password benar, cek status persetujuan
            if ($row['status_akun'] == 'approved') {
                $_SESSION['login_anggota'] = true;
                $_SESSION['id_anggota']    = $row['id_anggota'];
                $_SESSION['nama_anggota']  = $row['nama_lengkap'];
                
                header("Location: user/dashboard.php");
                exit;
            } elseif ($row['status_akun'] == 'pending') {
                $pesan = "<div class='alert alert-warning small'><i class='bi bi-info-circle-fill me-1'></i> Akun Anda masih menunggu persetujuan Admin.</div>";
            } else {
                $pesan = "<div class='alert alert-danger small'><i class='bi bi-x-circle-fill me-1'></i> Pendaftaran Anda ditolak oleh Admin.</div>";
            }
        } else {
            // Alert jika password salah
            $pesan = "<div class='alert alert-danger small'><i class='bi bi-exclamation-triangle-fill me-1'></i> Password yang Anda masukkan salah!</div>";
        }
    } else {
        // Alert jika email tidak ditemukan
        $pesan = "<div class='alert alert-danger small'><i class='bi bi-x-circle-fill me-1'></i> Email tidak ditemukan/belum terdaftar!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Perpustakaan authentication page">
  <title>Login Anggota | Perpustakaan</title>

  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="auth-body">
  <button class="icon-button theme-toggle auth-theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
    <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
  </button>
  <main class="auth-page">
    <section class="auth-card">
      <a class="auth-brand" href="index.php">
          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
          <span><strong>PojokBaca</strong><small>Sign in to your member account.</small></span>
      </a>
      <div class="auth-visual"><img src="assets/images/png/dasher-ui-bootstrap-5.jpg" alt="Perpustakaan interface"></div>
      
      <?= $pesan; ?>

      <form class="needs-validation" method="POST" action="" novalidate>
        <div class="mb-4">
          <p class="eyebrow mb-1">Akses Anggota</p>
          <h1 class="h3 mb-1">Login</h1>
          <p class="text-muted mb-0">Masuk untuk melihat riwayat peminjaman.</p>
        </div>
        
        <div class="mb-3">
            <label class="form-label" for="loginEmail">Email address</label>
            <input class="form-control" id="loginEmail" type="email" name="email" required>
            <div class="invalid-feedback">Enter a valid email.</div>
        </div>
        
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="loginPassword">Password</label>
                <a class="small fw-semibold" href="forgot-password.php">Forgot?</a>
            </div>
            <input class="form-control" id="loginPassword" type="password" name="password" minlength="6" required>
            <div class="invalid-feedback">Password must be at least 6 characters.</div>
        </div>
        
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
        
        <button class="btn btn-primary w-100" type="submit" name="proses_login">
            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Sign In
        </button>
      </form>
      
      <div class="auth-footer">Belum punya akun? <a href="register.php">Daftar Anggota</a></div>
    </section>
  </main>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>