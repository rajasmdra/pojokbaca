<?php
session_start();
// Keluar satu folder untuk mengambil file koneksi database
include '../config/koneksi.php'; 

// Jika admin sudah login, langsung arahkan ke dashboard admin
if (isset($_SESSION['login_admin'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan = "";

if (isset($_POST['proses_login_admin'])) {
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];

    // 1. Cek email di tabel admin
    $query = "SELECT * FROM admin WHERE email = '$email'";
    $result = mysqli_query($mysqli, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // 2. Validasi password menggunakan teks biasa (Plain Text Mode)
        if ($password === $row['password']) {
            
            // 3. Set Session khusus untuk Admin
            $_SESSION['login_admin'] = true;
            $_SESSION['id_admin']    = $row['id_admin'];
            $_SESSION['nama_admin']  = $row['nama_admin'];
            $_SESSION['role_admin']  = $row['role']; 
            
            // Karena satu direktori, langsung ke dashboard.php
            header("Location: dashboard.php");
            exit;
            
        } else {
            $pesan = "<div class='alert alert-danger small'><i class='bi bi-exclamation-triangle-fill me-1'></i> Password Admin salah!</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger small'><i class='bi bi-x-circle-fill me-1'></i> Akun Admin tidak ditemukan!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PojokBaca | Login Admin</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="auth-body">
  <button class="icon-button theme-toggle auth-theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
    <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
  </button>
  <main class="auth-page">
    <section class="auth-card">
      <a class="auth-brand" href="#">
          <span class="brand-icon"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></span>
          <span><strong>PojokBaca</strong><small>Admin Management Control</small></span>
      </a>
      <div class="auth-visual"><img src="../assets/images/png/dasher-ui-bootstrap-5.jpg" alt="Perpustakaan interface"></div>
      
      <?= $pesan; ?>

      <form class="needs-validation" method="POST" action="" novalidate>
        <div class="mb-4">
          <p class="eyebrow mb-1 text-danger">Admin</p>
          <h1 class="h3 mb-1">Login</h1>
          <p class="text-muted mb-0">Silahkan masuk untuk mengelola sistem perpustakaan.</p>
        </div>
        
        <div class="mb-3">
            <label class="form-label" for="loginEmail">Email Admin</label>
            <input class="form-control" id="loginEmail" type="email" name="email" required placeholder="admin@email.com">
        </div>
        
        <div class="mb-4">
            <label class="form-label" for="loginPassword">Password</label>
            <input class="form-control" id="loginPassword" type="password" name="password" minlength="6" required placeholder="Masukkan Password">
        </div>
        
        <button class="btn btn-danger w-100" type="submit" name="proses_login_admin">
            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Login
        </button>
      </form>
      
      <div class="auth-footer text-muted">Akses terbatas hanya untuk staf perpustakaan.</div>
    </section>
  </main>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>