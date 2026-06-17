<?php
session_start();
// Proteksi Halaman Admin: Jika tidak ada session login_admin, tendang ke login admin (index.php)
if (!isset($_SESSION['login_admin'])) {
    header("Location: index.php");
    exit;
}

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';
$role_admin = $_SESSION['role_admin'] ?? 'Staf';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PojokBaca | Dashboard Admin</title>

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php">
          <span class="brand-icon"><i class="bi bi-shield-fill-check" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Admin Panel</span>
          </span>
        </a>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link active" href="dashboard.php" aria-current="page">
          <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
          <span class="nav-text">Dashboard</span>
        </a>
        
        <a class="nav-link" href="verifikasi-anggota.php">
          <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
          <span class="nav-text">Persetujuan Anggota</span>
        </a>
        <a class="nav-link" href="data-buku.php">
          <span class="nav-icon"><i class="bi bi-book" aria-hidden="true"></i></span>
          <span class="nav-text">Kelola Buku</span>
        </a>
        <a class="nav-link" href="transaksi.php">
          <span class="nav-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
          <span class="nav-text">Sirkulasi Pinjam</span>
        </a>
        
        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="logout.php">
          <span class="nav-icon"><i class="bi bi-box-arrow-left text-danger" aria-hidden="true"></i></span>
          <span class="nav-text fw-bold">Logout</span>
        </a>
      </nav>

      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small class="text-capitalize"><?= htmlspecialchars($role_admin); ?></small>
      </div>
    </aside>

    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar">
            <span></span><span></span><span></span>
          </button>

          <div class="navbar-actions ms-auto">
            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
                <span class="profile-name d-none d-sm-inline"><?= htmlspecialchars($nama_admin); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-left me-2"></i>Sign out</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
          <div class="page-heading">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
              <div>
                <p class="eyebrow mb-1">Manajemen</p>
                <h1 class="h3 mb-1">Dashboard Admin</h1>
                <p class="text-muted mb-0">Selamat datang di pusat kendali data perpustakaan.</p>
              </div>
            </div>
          </div>

          <section class="row g-3 mt-1">
            <div class="col-12 col-sm-6 col-xl-4">
              <div class="metric-card metric-primary">
                <div class="metric-top">
                  <span class="metric-label">Total Anggota</span>
                  <span class="metric-icon"><i class="bi bi-users" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">120</div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
              <div class="metric-card metric-success">
                <div class="metric-top">
                  <span class="metric-label">Koleksi Buku</span>
                  <span class="metric-icon"><i class="bi bi-journal-bookmark" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">850</div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
              <div class="metric-card metric-danger">
                <div class="metric-top">
                  <span class="metric-label">Sedang Dipinjam</span>
                  <span class="metric-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">42</div>
              </div>
            </div>
          </section>

        </div>
      </main>

      <footer class="admin-footer">
        <div class="container-fluid px-3 px-lg-4">
          <span>Copyright 2026 PojokBaca Admin. All rights reserved.</span>
        </div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>