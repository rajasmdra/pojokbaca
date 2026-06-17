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

// 3. Ambil total denda aktif untuk sidebar badge
$query_denda = mysqli_query($mysqli, "SELECT COUNT(d.id_denda) AS jumlah_pelanggaran 
                                      FROM denda d 
                                      JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman 
                                      WHERE p.id_anggota = '$id_anggota' AND d.status_denda = 'belum_bayar'");
$data_denda = mysqli_fetch_assoc($query_denda);
$jumlah_denda_aktif = $data_denda['jumlah_pelanggaran'] ?? 0;

// 4. Logika Sorting via Header Tabel (Termasuk kolom status)
$sort_by   = $_GET['by'] ?? 'tgl_pinjam'; // Kolom target default
$sort_order = $_GET['order'] ?? 'DESC';     // Arah default

// Ambil arah sebaliknya untuk link klik berikutnya (Toggle ASC <=> DESC)
$next_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC';

// Validasi kolom whitelist (Ditambahkan 'status')
$allowed_columns = ['judul', 'tgl_pinjam', 'tgl_jatuh_tempo', 'tgl_kembali', 'status'];
$allowed_orders  = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_columns)) { $sort_by = 'tgl_pinjam'; }
if (!in_array($sort_order, $allowed_orders)) { $sort_order = 'DESC'; }

// Pemetaan nama kolom SQL sesungguhnya
if ($sort_by == 'judul') {
    $orderby_sql = "ORDER BY b.judul $sort_order";
} else {
    $orderby_sql = "ORDER BY p.$sort_by $sort_order";
}

// 5. Jalankan Query Riwayat
$query_riwayat = mysqli_query($mysqli, "SELECT p.*, b.judul 
                                        FROM peminjaman p
                                        INNER JOIN buku b ON p.id_buku = b.id_buku
                                        WHERE p.id_anggota = '$id_anggota'
                                        $orderby_sql");

// Fungsi bantu untuk menampilkan icon panah sort yang aktif di header
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
  <title>PojokBaca | Riwayat Pinjam</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    th.sortable-header {
      cursor: pointer;
      white-space: nowrap;
      transition: background-color 0.2s;
    }
    th.sortable-header:hover {
      background-color: rgba(0, 0, 0, 0.04) !important;
    }
    th.sortable-header a {
      text-decoration: none;
      color: inherit;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
  </style>
</head>

<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="admin-sidebar" id="adminSidebar">
      <div class="sidebar-header">
        <a class="brand-mark" href="dashboard.php">
          <span class="brand-icon"><i class="bi bi-book-half"></i></span>
          <span class="brand-copy">
            <span class="brand-title">PojokBaca</span>
            <span class="brand-subtitle">Anggota</span>
          </span>
        </a>
      </div>
      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
        <a class="nav-link" href="katalog.php"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Katalog Buku</span></a>
        <a class="nav-link active" href="riwayat.php"><span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span><span class="nav-text">Riwayat Pinjam</span></a>
        <a class="nav-link" href="denda.php">
          <span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Denda Saya</span>
          <?php if($jumlah_denda_aktif > 0): ?>
            <span class="badge bg-danger ms-auto px-2 rounded-pill"><?= $jumlah_denda_aktif; ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link" href="profil.php"><span class="nav-icon"><i class="bi bi-person-badge"></i></span><span class="nav-text">Profil Saya</span></a>
        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php"><span class="nav-icon"><i class="bi bi-box-arrow-left text-danger"></i></span><span class="nav-text fw-bold">Logout</span></a>
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
    </aside>

    <div class="admin-main">
      <nav class="navbar admin-navbar navbar-expand bg-white">
        <div class="container-fluid px-3 px-lg-4">
          <button class="sidebar-toggle" type="button" data-sidebar-toggle><span></span><span></span><span></span></button>
          <div class="navbar-actions ms-auto">
            <div class="dropdown">
              <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
              <span class="page-icon"><i class="bi bi-arrow-left-right"></i></span>
              <div>
                <p class="eyebrow mb-1">Aktivitas</p>
                <h1 class="h3 mb-1">Riwayat Peminjaman</h1>
                <p class="text-muted mb-0">Klik pada header kolom untuk mengurutkan Judul, Tanggal, maupun Status.</p>
              </div>
            </div>
          </div>

          <section class="panel">
            <div class="panel-header">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table"></i><span>Data Transaksi Pinjam</span></h2>
              </div>
              <input class="form-control form-control-sm table-search" type="search" placeholder="Cari data..." data-table-search="riwayatPinjamTable" style="max-width: 250px;">
            </div>
            
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover" id="riwayatPinjamTable">
                <thead>
                  <tr>
                    <th class="sortable-header">
                      <a href="riwayat.php?by=judul&order=<?= ($sort_by == 'judul') ? $next_order : 'ASC'; ?>">
                        Judul Buku <?= getSortIcon('judul', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="riwayat.php?by=tgl_pinjam&order=<?= ($sort_by == 'tgl_pinjam') ? $next_order : 'DESC'; ?>">
                        Tanggal Pinjam <?= getSortIcon('tgl_pinjam', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="riwayat.php?by=tgl_jatuh_tempo&order=<?= ($sort_by == 'tgl_jatuh_tempo') ? $next_order : 'ASC'; ?>">
                         Jatuh Tempo <?= getSortIcon('tgl_jatuh_tempo', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="riwayat.php?by=tgl_kembali&order=<?= ($sort_by == 'tgl_kembali') ? $next_order : 'ASC'; ?>">
                        Tanggal Kembali <?= getSortIcon('tgl_kembali', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="riwayat.php?by=status&order=<?= ($sort_by == 'status') ? $next_order : 'ASC'; ?>">
                        Status Peminjaman <?= getSortIcon('status', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="text-end">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (mysqli_num_rows($query_riwayat) > 0): 
                    while($row = mysqli_fetch_assoc($query_riwayat)):
                      $tgl_kembali_tampil = ($row['tgl_kembali'] != NULL) ? date('d M Y', strtotime($row['tgl_kembali'])) : '<span class="text-muted italic">-</span>';
                      
                      $status = $row['status'];
                      if ($status == 'kembali') {
                          $badge_status = '<span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i>Selesai</span>';
                      } elseif ($status == 'dipinjam') {
                          $hari_ini = date('Y-m-d');
                          if ($hari_ini > $row['tgl_jatuh_tempo']) {
                              $badge_status = '<span class="badge text-bg-danger"><i class="bi bi-exclamation-triangle me-1"></i>Terlambat / Denda</span>';
                          } else {
                              $badge_status = '<span class="badge text-bg-warning text-dark"><i class="bi bi-clock me-1"></i>Sedang Dipinjam</span>';
                          }
                      } else {
                          $badge_status = '<span class="badge text-bg-dark">'.ucfirst($status).'</span>';
                      }
                  ?>
                    <tr>
                      <td>
                        <div class="table-media">
                          <span class="brand-icon"><i class="bi bi-book-half"></i></span>
                          <span class="fw-semibold text-truncate" style="max-width: 250px;"><?= htmlspecialchars($row['judul']); ?></span>
                        </div>
                      </td>
                      <td><?= date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                      <td><span class="text-danger fw-medium"><?= date('d M Y', strtotime($row['tgl_jatuh_tempo'])); ?></span></td>
                      <td><?= $tgl_kembali_tampil; ?></td>
                      <td><?= $badge_status; ?></td>
                      <td class="text-end">
                        <a href="buku.php?id=<?= $row['id_buku']; ?>" class="btn btn-light btn-sm fw-medium">
                          <i class="bi bi-info-circle me-1"></i> Lihat Buku
                        </a>
                      </td>
                    </tr>
                  <?php 
                    endwhile; 
                  else: 
                  ?>
                    <tr>
                      <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-hourglass-split display-6 d-block mb-2"></i> Data riwayat tidak ditemukan.
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
        <div class="container-fluid px-3 px-lg-4"><span>Copyright 2026 PojokBaca. All rights reserved.</span></div>
      </footer>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>
</body>
</html>