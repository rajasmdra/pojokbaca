<?php
session_start();
include 'config/koneksi.php';

// Proteksi: Jika user belum memasukkan email di forgot-password, usir balik
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit;
}

$pesan = "";
$email_target = $_SESSION['reset_email'];

if (isset($_POST['proses_update_password'])) {
    $password_baru = mysqli_real_escape_string($mysqli, $_POST['password_baru']);
    $konfirmasi    = $_POST['confirm_password'];

    // 1. Validasi kecocokan password baru & konfirmasi
    if ($password_baru !== $konfirmasi) {
        $pesan = "<div class='alert alert-danger small'><i class='bi bi-exclamation-triangle-fill me-1'></i> Konfirmasi password baru tidak cocok!</div>";
    } else {
        // 2. Update password menggunakan teks biasa langsung ke database
        $query = "UPDATE anggota SET password = '$password_baru' WHERE email = '$email_target'";
        
        if (mysqli_query($mysqli, $query)) {
            // Hapus session reset_email karena tugasnya sudah selesai
            unset($_SESSION['reset_email']);
            
            $pesan = "<div class='alert alert-success small'><i class='bi bi-check-circle-fill me-1'></i> Password berhasil diperbarui! Silakan <a href='index.php' class='fw-bold text-decoration-none'>Login di sini</a> dengan password baru Anda.</div>";
        } else {
            $pesan = "<div class='alert alert-danger small'><i class='bi bi-x-circle-fill me-1'></i> Gagal memperbarui database. Coba lagi.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PojokBaca | Ubah Password</title>

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
        <span class="brand-icon"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></span>
        <span><strong>PojokBaca</strong><small>Create a brand new password.</small></span>
      </a>
      <div class="auth-visual"><img src="assets/images/png/dasher-ui-bootstrap-5.jpg" alt="Interface"></div>
      
      <?= $pesan; ?>

      <?php if(isset($_SESSION['reset_email'])) : ?>
      <form class="needs-validation" method="POST" action="" novalidate>
        <div class="mb-4">
          <p class="eyebrow mb-1">ANGGOTA</p>
          <h1 class="h3 mb-1">Ubah Password</h1>
          <p class="text-muted mb-0">Mengubah password untuk email: <strong class="text-body"><?= htmlspecialchars($email_target); ?></strong></p>
        </div>
        
        <div class="mb-3">
          <label class="form-label" for="newPassword">Password Baru</label>
          <input class="form-control" id="newPassword" type="password" name="password_baru" minlength="6" required placeholder="Minimal 6 karakter">
          <div class="invalid-feedback">Password must be at least 6 characters.</div>
        </div>

        <div class="mb-4">
          <label class="form-label" for="confirmPassword">Konfirmasi Password Baru</label>
          <input class="form-control" id="confirmPassword" type="password" name="confirm_password" minlength="6" required placeholder="Ulangi password baru">
          <div class="invalid-feedback">Please repeat your password correctly.</div>
        </div>
        
        <button class="btn btn-primary w-100" type="submit" name="proses_update_password">
          <i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan Password Baru
        </button>
      </form>
      <?php endif; ?>
      
      <div class="auth-footer"><a href="index.php">Kembali ke Halaman Login</a></div>
    </section>
  </main>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>