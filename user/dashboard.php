<?php
session_start();
// 1. Proteksi Halaman: Jika tidak ada session login_anggota, tendang kembali ke login (index.php)
if (!isset($_SESSION['login_anggota'])) {
    header("Location: index.php");
    exit;
}

// 2. Hubungkan ke database (keluar 1 folder ke config/)
include '../config/koneksi.php';

$nama_anggota = $_SESSION['nama_anggota'] ?? 'Anggota';
$id_anggota   = $_SESSION['id_anggota'];

// 3. Ambil Data Statistik Riil dari Database berdasarkan ID Anggota yang Login
// Hitung Buku yang Sedang Dipinjam (status = 'sedang dipinjam')
$query_pinjam = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_anggota = '$id_anggota' AND status = 'dipinjam'");
$data_pinjam  = mysqli_fetch_assoc($query_pinjam);
$buku_dipinjam = $data_pinjam['total'];

// Hitung Total Seluruh Riwayat Peminjaman (baik yang sedang dipinjam maupun sudah selesai)
$query_total = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_anggota = '$id_anggota'");
$data_total  = mysqli_fetch_assoc($query_total);
$total_peminjaman = $data_total['total'];

// Hitung Total Akumulasi Denda yang BELUM LUNAS milik Anggota Ini
$query_denda = mysqli_query($mysqli, "SELECT SUM(d.jumlah_denda) AS total_tagihan, COUNT(d.id_denda) AS jumlah_pelanggaran 
                                      FROM denda d 
                                      JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman 
                                      WHERE p.id_anggota = '$id_anggota' AND d.status_denda = 'belum_bayar'");
$data_denda  = mysqli_fetch_assoc($query_denda);
$tanggungan_denda = $data_denda['total_tagihan'] ?? 0;
$jumlah_denda_aktif = $data_denda['jumlah_pelanggaran'] ?? 0;

// 4. Ambil Aturan Batas Maksimal & Tarif Denda dari Tabel Pengaturan secara Dinamis
$query_setting = mysqli_query($mysqli, "SELECT * FROM denda LIMIT 1");
$setting = mysqli_fetch_assoc($query_setting);
$maks_buku = $setting['maks_buku_pinjam'] ?? 3;
$durasi_hari = $setting['durasi_pinjam_hari'] ?? 7;
$tarif_denda = $setting['tarif_denda_perhari'] ?? 2000;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PojokBaca Anggota Workspace">
  <title>PojokBaca | Dashboard</title>

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
            <span class="brand-subtitle">Anggota</span>
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
          <span class="nav-text">Katalog Buku</span>
        </a>
        
        <a class="nav-link" href="riwayat.php">
          <span class="nav-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
          <span class="nav-text">Riwayat Pinjam</span>
        </a>
        
        <a class="nav-link" href="denda.php">
          <span class="nav-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
          <span class="nav-text">Denda Saya</span>
          <?php if($jumlah_denda_aktif > 0): ?>
            <span class="badge bg-danger ms-auto px-2 rounded-pill"><?= $jumlah_denda_aktif; ?></span>
          <?php endif; ?>
        </a>
        
        <a class="nav-link" href="profil.php">
          <span class="nav-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
          <span class="nav-text">Profil Saya</span>
        </a>
        
        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php">
          <span class="nav-icon"><i class="bi bi-box-arrow-left text-danger" aria-hidden="true"></i></span>
          <span class="nav-text fw-bold">Logout</span>
        </a>
      </nav>

      <div class="sidebar-user d-none">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_anggota); ?>">
        <strong>Admin Hasan</strong>
        <small>Anggota Aktif</small>
      </div>

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
            <span></span><span></span><span></span>
          </button>

          <div class="navbar-actions ms-auto">
            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
              <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>

            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="avatar-img avatar-sm" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_anggota); ?>">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($nama_anggota); ?></span>
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
                <p class="eyebrow mb-1">Anggota</p>
                <h1 class="h3 mb-1">Dashboard</h1>
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
                <div class="metric-value"><?= $buku_dipinjam; ?></div>
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
                <div class="metric-value"><?= $total_peminjaman; ?></div>
                <div class="metric-meta">
                  <span>Buku yang pernah Anda baca</span>
                </div>
              </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
              <article class="metric-card <?= ($tanggungan_denda > 0) ? 'metric-danger' : 'metric-secondary'; ?>">
                <div class="metric-top">
                  <span class="metric-label">Tanggungan Denda</span>
                  <span class="metric-icon"><i class="bi bi-exclamation-octagon" aria-hidden="true"></i></span>
                </div>
                <div class="metric-value">Rp <?= number_format($tanggungan_denda, 0, ',', '.'); ?></div>
                <div class="metric-meta">
                  <?php if($tanggungan_denda > 0): ?>
                    <span class="text-danger fw-semibold">Silahkan bayar langsung ke petugas admin</span>
                  <?php else: ?>
                    <span class="text-success">Bebas denda keterlambatan</span>
                  <?php endif; ?>
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
                  <ul class="mb-0">
                    <li class="mb-2">Maksimal peminjaman adalah <strong class="text-primary"><?= $maks_buku; ?> buku</strong> sekaligus.</li>
                    <li class="mb-2">Durasi waktu peminjaman adalah <strong class="text-primary"><?= $durasi_hari; ?> hari</strong> sejak tanggal transaksi dilakukan fisik.</li>
                    <li>Keterlambatan pengembalian dikenakan denda keterlambatan sebesar <strong class="text-danger">Rp <?= number_format($tarif_denda, 0, ',', '.'); ?> / hari</strong> untuk masing-masing buku.</li>
                  </ul>
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