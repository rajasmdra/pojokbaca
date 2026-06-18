<?php
session_start();
// 1. Proteksi Halaman: Jika tidak ada session login_admin, tendang kembali ke login
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php"); // Menuju ke halaman login di luar folder admin
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// ====================================================================
// 3. Ambil Data Statistik Riil untuk Dashboard Admin
// ====================================================================

// Hitung Total Buku
$query_buku = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM buku");
$data_buku  = mysqli_fetch_assoc($query_buku);
$total_buku = $data_buku['total'] ?? 0;

// Hitung Total Seluruh Anggota Terverifikasi
$query_anggota = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'approved'");
$data_anggota  = mysqli_fetch_assoc($query_anggota);
$total_anggota = $data_anggota['total'] ?? 0;

// Hitung Anggota Baru yang berstatus 'pending' (Butuh Persetujuan)
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// Hitung Buku yang Sedang Dipinjam saat ini
$query_dipinjam = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam'");
$data_dipinjam  = mysqli_fetch_assoc($query_dipinjam);
$buku_dipinjam  = $data_dipinjam['total'] ?? 0;

// Hitung Total Denda Anggota yang Belum Dibayarkan
$query_denda = mysqli_query($mysqli, "SELECT SUM(jumlah_denda) AS total FROM denda WHERE status_denda = 'belum_bayar'");
$data_denda  = mysqli_fetch_assoc($query_denda);
$total_denda_aktif = $data_denda['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PojokBaca Admin Workspace">
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
        <a class="brand-mark" href="dashboard.php" aria-label="PojokBaca dashboard">
          <span class="brand-icon"><i class="bi bi-book-half" aria-hidden="true"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Administrator</span>
          </span>
        </a>
      </div>

      <nav class="sidebar-nav">
        <a class="nav-link active" href="dashboard.php" aria-current="page">
          <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
          <span class="nav-text">Dashboard</span>
        </a>
        
        <a class="nav-link" href="katalog.php">
          <span class="nav-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
          <span class="nav-text">Data Buku</span>
        </a>
        
        <a class="nav-link" href="anggota.php">
          <span class="nav-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
          <span class="nav-text">Data Anggota</span>
          <?php if($anggota_pending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto px-2 rounded-pill"><?= $anggota_pending; ?></span>
          <?php endif; ?>
        </a>
        
        <a class="nav-link" href="peminjaman.php">
          <span class="nav-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
          <span class="nav-text">Peminjaman</span>
        </a>
        
        <a class="nav-link" href="denda.php">
          <span class="nav-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
          <span class="nav-text">Data Denda</span>
        </a>
        
        <div class="nav-item-dropdown">
          <a class="nav-link dropdown-toggle" href="#menuKelola" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="menuKelola">
            <span class="nav-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
            <span class="nav-text">Kelola</span>
          </a>
          <div class="collapse ms-3" id="menuKelola">
            <a class="nav-link py-1 small" href="kategori.php"><i class="bi bi-tags me-2"></i>Kategori</a>
            <a class="nav-link py-1 small" href="penerbit.php"><i class="bi bi-building me-2"></i>Penerbit</a>
            <a class="nav-link py-1 small" href="rak.php"><i class="bi bi-bookshelf me-2"></i>Data Rak</a>
          </div>
        </div>
        
        <hr class="mx-3 my-2 text-secondary opacity-25">
        
        <a class="nav-link text-danger" href="../logout.php">
          <span class="nav-icon"><i class="bi bi-box-arrow-left text-danger" aria-hidden="true"></i></span>
          <span class="nav-text fw-bold">Logout</span>
        </a>
      </nav>

      <div class="sidebar-user d-none">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Super Admin</small>
      </div>
      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Admin</small>
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
            <span></span><span></span><span></span>
          </button>

          <div class="navbar-actions ms-auto">
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
              <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nama_admin); ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profil.php">Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
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
                <p class="eyebrow mb-1">Panel Administrasi</p>
                <h1 class="h3 mb-1">Dashboard Admin</h1>
                <p class="text-muted mb-0">Selamat datang kembali, <strong><?= htmlspecialchars($nama_admin); ?></strong>. Berikut adalah ringkasan operasional perpustakaan hari ini.</p>
              </div>
            </div>
          </div>

          <?php if ($anggota_pending > 0): ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-3 h4 mb-0"></i>
              <div>
                Ada <strong><?= $anggota_pending; ?> akun pendaftar baru</strong> yang berstatus pending. Silakan lakukan validasi di halaman <a href="anggota.php" class="alert-link">Data Anggota</a>.
              </div>
            </div>
          <?php endif; ?>

          <section class="row g-3 mt-1" aria-label="Admin metrics">
            <div class="col-12 col-sm-6 col-xl-3">
              <article class="metric-card metric-primary">
                <div class="metric-top">
                  <span class="metric-label">Koleksi Buku</span>
                  <span class="metric-icon"><i class="bi bi-bookshelf" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value"><?= $total_buku; ?></div>
                <div class="metric-meta">
                  <span>Judul buku terdaftar</span>
                </div>
              </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
              <article class="metric-card metric-success">
                <div class="metric-top">
                  <span class="metric-label">Total Anggota</span>
                  <span class="metric-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value"><?= $total_anggota; ?></div>
                <div class="metric-meta">
                  <span>Anggota aktif</span>
                </div>
              </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
              <article class="metric-card metric-info">
                <div class="metric-top">
                  <span class="metric-label">Buku Dipinjam</span>
                  <span class="metric-icon"><i class="bi bi-journal-arrow-up" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value"><?= $buku_dipinjam; ?></div>
                <div class="metric-meta">
                  <span>Sedang dibawa sirkulasi</span>
                </div>
              </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
              <article class="metric-card <?= ($total_denda_aktif > 0) ? 'metric-danger' : 'metric-secondary'; ?>">
                <div class="metric-top">
                  <span class="metric-label">Total Denda Aktif</span>
                  <span class="metric-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">Rp <?= number_format($total_denda_aktif, 0, ',', '.'); ?></div>
                <div class="metric-meta">
                  <span>Belum diselesaikan anggota</span>
                </div>
              </article>
            </div>
          </section>

          <section class="row g-3 mt-2">
            <div class="col-12">
              <div class="panel">
                <div class="panel-header">
                  <div>
                    <h2 class="h5 mb-1 section-title"><i class="bi bi-lightning-charge" aria-hidden="true"></i><span>Akses Cepat Petugas</span></h2>
                    <p class="text-muted mb-0">Klik tombol di bawah ini untuk langsung menuju menu operasional sirkulasi.</p>
                  </div>
                </div>
                <div class="p-3 d-flex flex-wrap gap-2">
                  <a href="peminjaman.php" class="btn btn-primary"><i class="bi bi-arrow-left-right me-2"></i>Kelola Peminjaman</a>
                  <a href="pengembalian.php" class="btn btn-outline-success"><i class="bi bi-arrow-counterclockwise me-2"></i>Kelola Pengembalian</a>
                  <a href="buku.php" class="btn btn-outline-secondary"><i class="bi bi-journal-plus me-2"></i>Tambah Koleksi Buku</a>
                </div>
              </div>
            </div>
          </section>

        </div>
      </main>

      <footer class="admin-footer">
        <div class="container-fluid px-3 px-lg-4">
          <span>Copyright 2026 PojokBaca. All rights reserved.</span>
        </div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>