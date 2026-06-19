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

// Tarif denda default
$tarif_denda_per_hari = 5000;

// ====================================================================
// LOGIKA OTOMATISASI: GENERATE BARU & UPDATE DENDA TIAP HARI
// ====================================================================
$sql_auto_insert = "INSERT INTO denda (id_peminjaman, jumlah_denda, status_denda)
                    SELECT p.id_peminjaman, 0, 'belum_bayar'
                    FROM peminjaman p
                    LEFT JOIN denda d ON p.id_peminjaman = d.id_peminjaman
                    WHERE p.status = 'dipinjam' 
                      AND CURDATE() > p.tgl_jatuh_tempo
                      AND d.id_denda IS NULL";
mysqli_query($mysqli, $sql_auto_insert);

$sql_auto_update = "UPDATE denda d
                    JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman
                    SET d.jumlah_denda = DATEDIFF(CURDATE(), p.tgl_jatuh_tempo) * $tarif_denda_per_hari
                    WHERE d.status_denda = 'belum_bayar' 
                      AND p.status = 'dipinjam' 
                      AND CURDATE() > p.tgl_jatuh_tempo";
mysqli_query($mysqli, $sql_auto_update);
// ====================================================================

// 3. Ambil total denda aktif untuk indikator badge sidebar
$query_badge_denda = mysqli_query($mysqli, "SELECT COUNT(d.id_denda) AS jumlah_pelanggaran 
                                            FROM denda d 
                                            JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman 
                                            WHERE p.id_anggota = '$id_anggota' AND d.status_denda = 'belum_bayar'");
$data_badge = mysqli_fetch_assoc($query_badge_denda);
$jumlah_denda_aktif = $data_badge['jumlah_pelanggaran'] ?? 0;

// 4. Logika Sort via Header Tabel
$sort_by    = $_GET['by'] ?? 'status_denda';
$sort_order = $_GET['order'] ?? 'DESC';
$next_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC';

$allowed_columns = ['judul', 'tgl_jatuh_tempo', 'tgl_kembali', 'jumlah_denda', 'status_denda', 'tgl_bayar'];
$allowed_orders  = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_columns)) { $sort_by = 'status_denda'; }
if (!in_array($sort_order, $allowed_orders)) { $sort_order = 'DESC'; }

if ($sort_by == 'judul') { 
    $sort_col = 'b.judul'; 
} elseif ($sort_by == 'tgl_jatuh_tempo' || $sort_by == 'tgl_kembali') {
    $sort_col = 'p.' . $sort_by;
} else { 
    $sort_col = 'd.' . $sort_by; 
}

// 5. Kueri Utama
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
              ORDER BY $sort_col $sort_order";

$query_tabel_denda = mysqli_query($mysqli, $sql_denda);

function getSortIcon($column, $current_by, $current_order) {
    if ($column === $current_by) {
        return ($current_order === 'ASC') ? ' <i class="bi bi-caret-up-fill text-dark small"></i>' : ' <i class="bi bi-caret-down-fill text-dark small"></i>';
    }
    return ' <i class="bi bi-arrow-down-up text-muted opacity-50 small"></i>';
}
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
  <style>
    th.sortable-header { cursor: pointer; white-space: nowrap; transition: background-color 0.2s; }
    th.sortable-header:hover { background-color: rgba(0, 0, 0, 0.04) !important; }
    th.sortable-header a { text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
  </style>
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
        <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')">
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
                <li><a class="dropdown-item text-danger" href="../logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun anda?')"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
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
              <input class="form-control form-control-sm table-search" type="search" placeholder="Cari judul buku..." data-table-search="dendaTable" aria-label="Search fines">
            </div>
            
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover" id="dendaTable" data-searchable-table>
                <thead>
                  <tr>
                    <th class="sortable-header">
                      <a href="denda.php?by=judul&order=<?= ($sort_by == 'judul') ? $next_order : 'ASC'; ?>">
                        Buku / Transaksi <?= getSortIcon('judul', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=tgl_jatuh_tempo&order=<?= ($sort_by == 'tgl_jatuh_tempo') ? $next_order : 'ASC'; ?>">
                        Batas Jatuh Tempo <?= getSortIcon('tgl_jatuh_tempo', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=tgl_kembali&order=<?= ($sort_by == 'tgl_kembali') ? $next_order : 'ASC'; ?>">
                        Tanggal Kembali <?= getSortIcon('tgl_kembali', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=jumlah_denda&order=<?= ($sort_by == 'jumlah_denda') ? $next_order : 'ASC'; ?>">
                        Jumlah Denda <?= getSortIcon('jumlah_denda', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=status_denda&order=<?= ($sort_by == 'status_denda') ? $next_order : 'ASC'; ?>">
                        Status <?= getSortIcon('status_denda', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=tgl_bayar&order=<?= ($sort_by == 'tgl_bayar') ? $next_order : 'ASC'; ?>">
                        Tanggal Bayar <?= getSortIcon('tgl_bayar', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (mysqli_num_rows($query_tabel_denda) > 0): 
                    while($denda = mysqli_fetch_assoc($query_tabel_denda)):
                      
                      if($denda['status_denda'] == 'lunas') {
                          $status_badge = '<span class="badge bg-success text-white px-3 py-1.5">Lunas</span>';
                          $tgl_bayar = date('d M Y', strtotime($denda['tgl_bayar']));
                          $tgl_kembali = date('d M Y', strtotime($denda['tgl_kembali']));
                      } else {
                          $status_badge = '<span class="badge bg-danger text-white px-3 py-1.5">Belum Bayar</span>';
                          $tgl_bayar = '<span class="text-muted small">-</span>';
                          
                          if($denda['status_pinjam'] == 'dipinjam') {
                              $tgl_kembali = '<span class="badge bg-warning text-dark px-3 py-1.5">Masih Dipinjam</span>';
                          } else {
                              $tgl_kembali = date('d M Y', strtotime($denda['tgl_kembali']));
                          }
                      }
                  ?>
                    <tr>
                      <td>
                        <div class="table-media">
                          <span class="brand-icon"><i class="bi bi-book-half"></i></span>
                          <span class="fw-semibold"><?= htmlspecialchars($denda['judul']); ?></span>
                        </div>
                      </td>
                      <td><?= date('d M Y', strtotime($denda['tgl_jatuh_tempo'])); ?></td>
                      <td><?= $tgl_kembali; ?></td>
                      <td>
                        <strong class="<?= $denda['status_denda'] == 'belum_bayar' ? 'text-danger' : 'text'; ?>">
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