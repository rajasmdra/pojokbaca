<?php
session_start();
// 1. Proteksi Halaman
if (!isset($_SESSION['login_anggota'])) {
    header("Location: index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_anggota = $_SESSION['nama_anggota'] ?? 'Anggota';
$id_anggota   = $_SESSION['id_anggota'];

// Ambil tarif denda dinamis dari database (jika ada tabel pengaturan), atau gunakan default 2000
$tarif_denda_per_hari = 5000;

// ====================================================================
// LOGIKA OTOMATISASI: GENERATE BARU & UPDATE DENDA TIAP HARI
// ====================================================================

// TUGAS A: Otomatis INSERT baris denda baru jika ada peminjaman yang lewat tempo tapi belum terdaftar di tabel denda
$sql_auto_insert = "INSERT INTO denda (id_peminjaman, jumlah_denda, status_denda)
                    SELECT p.id_peminjaman, 0, 'belum_bayar'
                    FROM peminjaman p
                    LEFT JOIN denda d ON p.id_peminjaman = d.id_peminjaman
                    WHERE p.status = 'dipinjam' 
                      AND CURDATE() > p.tgl_jatuh_tempo
                      AND d.id_denda IS NULL";
mysqli_query($mysqli, $sql_auto_insert);

// TUGAS B: Otomatis UPDATE jumlah denda berjalan yang belum lunas berdasarkan selisih hari real-time
$sql_auto_update = "UPDATE denda d
                    JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman
                    SET d.jumlah_denda = DATEDIFF(CURDATE(), p.tgl_jatuh_tempo) * $tarif_denda_per_hari
                    WHERE d.status_denda = 'belum_bayar' 
                      AND p.status = 'dipinjam' 
                      AND CURDATE() > p.tgl_jatuh_tempo";
mysqli_query($mysqli, $sql_auto_update);

// ====================================================================


// 3. Ambil total denda aktif (belum_bayar) untuk kebutuhan indikator badge di sidebar
$query_badge_denda = mysqli_query($mysqli, "SELECT COUNT(d.id_denda) AS jumlah_pelanggaran 
                                            FROM denda d 
                                            JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman 
                                            WHERE p.id_anggota = '$id_anggota' AND d.status_denda = 'belum_bayar'");
$data_badge = mysqli_fetch_assoc($query_badge_denda);
$jumlah_denda_aktif = $data_badge['jumlah_pelanggaran'] ?? 0;

// 4. Kueri Utama: Mengambil data denda langsung dari tabel denda yang sudah disinkronisasi di atas
$sql_denda = "SELECT 
                d.id_denda,
                p.id_peminjaman,
                b.judul,
                p.tgl_pinjam,
                p.tgl_jatuh_tempo,
                p.tgl_kembali,
                p.status AS status_pinjam,
                d.status_denda,
                d.tgl_bayar,
                d.jumlah_denda
              FROM denda d
              JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman
              JOIN buku b ON p.id_buku = b.id_buku
              WHERE p.id_anggota = '$id_anggota'
              ORDER BY d.status_denda DESC, p.tgl_jatuh_tempo DESC";

$query_tabel_denda = mysqli_query($mysqli, $sql_denda);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PojokBaca Anggota Workspace">
  <title>PojokBaca | Denda Saya</title>

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
        <a class="nav-link" href="dashboard.php">
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
        
        <a class="nav-link active" href="denda.php" aria-current="page">
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
              <span class="page-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
              <div>
                <p class="eyebrow mb-1">Keuangan</p>
                <h1 class="h3 mb-1">Informasi Denda</h1>
                <p class="text-muted mb-0">Pantau denda keterlambatan pengembalian buku Anda.</p>
              </div>
            </div>
          </div>

          <?php if($jumlah_denda_aktif > 0): ?>
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center" role="alert">
              <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
              <div>
                <strong>Perhatian!</strong> Anda memiliki denda aktif yang belum dibayar. Jumlah denda untuk buku yang belum kembali akan otomatis <strong>bertambah Rp<?= number_format($tarif_denda_per_hari, 0, ',', '.'); ?>/hari</strong>.
              </div>
            </div>
          <?php endif; ?>

          <section class="panel">
            <div class="panel-header">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-list-ol" aria-hidden="true"></i><span>Daftar Transaksi Denda</span></h2>
                <p class="text-muted mb-0">Rincian perhitungan denda lunas maupun denda berjalan.</p>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover">
                <thead>
                  <tr>
                    <th>Buku / Transaksi</th>
                    <th>Batas Jatuh Tempo</th>
                    <th>Tanggal Kembali</th>
                    <th>Jumlah Denda</th>
                    <th>Status</th>
                    <th>Tanggal Bayar</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (mysqli_num_rows($query_tabel_denda) > 0): 
                    while($denda = mysqli_fetch_assoc($query_tabel_denda)):
                      
                      // Penentuan badge status denda
                      if($denda['status_denda'] == 'lunas') {
                          $status_badge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Lunas</span>';
                          $tgl_bayar = date('d M Y', strtotime($denda['tgl_bayar']));
                          $tgl_kembali = date('d M Y', strtotime($denda['tgl_kembali']));
                      } else {
                          // Jika belum_bayar
                          $status_badge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Belum Bayar</span>';
                          $tgl_bayar = '<span class="text-muted small">-</span>';
                          
                          if($denda['status_pinjam'] == 'dipinjam') {
                              $tgl_kembali = '<span class="badge bg-warning text-dark">Masih Dipinjam</span>';
                          } else {
                              $tgl_kembali = date('d M Y', strtotime($denda['tgl_kembali']));
                          }
                      }
                  ?>
                    <tr>
                      <td>
                        <div class="table-media">
                            <span class="brand-icon"><i class="bi bi-book-half"></i></span>
                          <span class="fw-semibold text-dark"><?= htmlspecialchars($denda['judul']); ?></span>
                        </div>
                      </td>
                      <td>
                        <span>
                          <?= date('d M Y', strtotime($denda['tgl_jatuh_tempo'])); ?>
                        </span>
                      </td>
                      <td><?= $tgl_kembali; ?></td>
                      <td>
                        <strong class="<?= $denda['status_denda'] == 'belum_bayar' ? 'text-danger' : 'text-dark'; ?>">
                            Rp<?= number_format($denda['jumlah_denda'], 0, ',', '.'); ?>
                        </strong>
                      </td>
                      <td><?= $status_badge; ?></td>
                      <td><?= $tgl_bayar; ?></td>
                    </tr>
                  <?php 
                    endwhile; 
                  else: 
                  ?>
                    <tr>
                      <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-shield-check display-5 d-block mb-2 text-success"></i>
                        <span class="fw-medium d-block text-dark">Bersih dari Denda</span>
                        <small>Hebat! Anda selalu mengembalikan buku tepat waktu.</small>
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
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