<?php
session_start();
include 'config/koneksi.php';

// Jika user sudah login, langsung lempar ke dashboard
if (isset($_SESSION['login_user'])) {
    header("Location: user/dashboard.php");
    exit;
}

$pesan = "";

if (isset($_POST['proses_forgot'])) {
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);

    // Cek apakah email terdaftar di tabel anggota
    $query = "SELECT email FROM anggota WHERE email = '$email'";
    $result = mysqli_query($mysqli, $query);

    if (mysqli_num_rows($result) === 1) {
        // Jika ada, simpan email ke session untuk divalidasi di halaman berikutnya
        $_SESSION['reset_email'] = $email;
        
        // Alihkan ke halaman input password baru
        header("Location: new-password.php");
        exit;
    } else {
        $pesan = "<div class='alert alert-danger small'><i class='bi bi-exclamation-triangle-fill me-1'></i> Email tidak ditemukan atau belum terdaftar!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="adminHMD authentication page">
  <title>PojokBaca | Lupa Password</title>

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
        <span><strong>PojokBaca</strong><small>Dapatkan akses reset password akun anda.</small></span>
      </a>
      <div class="auth-visual"><img src="assets/images/png/dasher-ui-bootstrap-5.jpg" alt="adminHMD dashboard interface"></div>
      
      <?= $pesan; ?>

      <form class="needs-validation" method="POST" action="" novalidate>
        <div class="mb-4">
          <p class="eyebrow mb-1">Anggota</p>
          <h1 class="h3 mb-1">Lupa Password</h1>
          <p class="text-muted mb-0">Masukkan email Anda untuk memperbarui password.</p>
        </div>
        
        <div class="mb-4">
          <label class="form-label" for="forgotEmail">Email address</label>
          <input class="form-control" id="forgotEmail" type="email" name="email" required placeholder="Masukkan email terdaftar">
          <div class="invalid-feedback">Enter a valid email.</div>
        </div>
        
        <button class="btn btn-primary w-100" type="submit" name="proses_forgot">
          <i class="bi bi-envelope-arrow-up" aria-hidden="true"></i> Ubah Password
        </button>
      </form>
      
      <div class="auth-footer">Ingat Password? <a href="index.php">Kembali ke Halaman Login</a></div>
    </section>
  </main>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>