<?php
session_start();
// Proteksi Halaman: Jika tidak ada session login_anggota, tendang kembali ke login (index.php)
if (!isset($_SESSION['login_anggota'])) {
    header("Location: index.php");
    exit;
}

// Mengambil nama anggota dari session yang terdaftar saat login
$nama_anggota = $_SESSION['nama_anggota'] ?? 'Anggota';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="adminHMD professional admin dashboard template">
  <title>Dashboard Anggota | RuangPustaka</title>

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php" aria-label="adminHMD dashboard">
          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Anggota Area</span>
          </span>
        </a>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link active" href="dashboard.php" aria-current="page">
          <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
          <span class="nav-text">Dashboard</span>
        </a>
        <a class="nav-link" href="buku.php">
          <span class="nav-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
          <span class="nav-text">Katalog Buku</span>
        </a>
        <a class="nav-link" href="peminjaman.php">
          <span class="nav-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
          <span class="nav-text">Riwayat Pinjam</span>
        </a>
        <a class="nav-link" href="profile.php">
          <span class="nav-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
          <span class="nav-text">Profil Saya</span>
        </a>
        
        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php">
          <span class="nav-icon"><i class="bi bi-box-arrow-left text-danger" aria-hidden="true"></i></span>
          <span class="nav-text fw-bold">Logout</span>
        </a>
      </nav>

      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_anggota); ?>">
        <strong><?= htmlspecialchars($nama_anggota); ?></strong>
        <small>Anggota Aktif</small>
      </div>

      <div class="sidebar-footer">
        <span class="status-dot"></span>
        <span class="sidebar-footer-text">Sistem berjalan lancar</span>
      </div>
    </aside>

    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
          </button>

          <form class="d-none d-md-flex ms-3 flex-grow-1" role="search">
            <input class="form-control search-input" type="search" placeholder="Cari buku atau riwayat..." aria-label="Search">
          </form>

          <div class="navbar-actions ms-auto">
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
              <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_anggota); ?>">
                <span class="profile-name d-none d-sm-inline"><?= htmlspecialchars($nama_anggota); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php">Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Sign out</a></li>
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
                <p class="eyebrow mb-1">Ringkasan</p>
                <h1 class="h3 mb-1">Dashboard Anggota</h1>
                <p class="text-muted mb-0">Selamat datang kembali! Pantau status peminjaman buku Anda di sini.</p>
              </div>
            </div>
          </div>

          <section class="row g-3 mt-1" aria-label="Dashboard metrics">
            <div class="col-12 col-sm-6 col-xl-4">
              <article class="metric-card metric-primary">
                <div class="metric-top">
                  <span class="metric-label">Buku Dipinjam</span>
                  <span class="metric-icon"><i class="bi bi-journal-arrow-up" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">2</div>
                <div class="metric-meta">
                  <span>Saat ini sedang Anda bawa</span>
                </div>
              </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
              <article class="metric-card metric-success">
                <div class="metric-top">
                  <span class="metric-label">Total Peminjaman</span>
                  <span class="metric-icon"><i class="bi bi-bookshelf" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">14</div>
                <div class="metric-meta">
                  <span>Buku yang pernah Anda baca</span>
                </div>
              </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
              <article class="metric-card metric-danger">
                <div class="metric-top">
                  <span class="metric-label">Tanggungan Denda</span>
                  <span class="metric-icon"><i class="bi bi-exclamation-octagon" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">Rp 0</div>
                <div class="metric-meta">
                  <span class="text-success">Bebas denda keterlambatan</span>
                </div>
              </article>
            </div>
          </section>

          <section class="row g-3 mt-1">
            <div class="col-12">
              <div class="panel">
                <div class="panel-header">
                  <div>
                    <h2 class="h5 mb-1 section-title"><i class="bi bi-info-circle" aria-hidden="true"></i><span>Aturan Peminjaman</span></h2>
                    <p class="text-muted mb-0">Informasi penting mengenai sirkulasi buku perpustakaan.</p>
                  </div>
                </div>
                <div class="p-3">
                  <ul>
                    <li>Maksimal peminjaman adalah 3 buku sekaligus.</li>
                    <li>Durasi waktu peminjaman adalah 7 hari sejak tanggal pinjam.</li>
                    <li>Keterlambatan pengembalian dikenakan denda sesuai aturan yang berlaku.</li>
                  </ul>
                </div>
              </div>
            </div>
          </section>

        </div>
      </main>

      <footer class="admin-footer">
        <div class="container-fluid px-3 px-lg-4">
          <span>Copyright 2026 RuangPustaka. All rights reserved.</span>
        </div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>