<?php
session_start();
// 1. Proteksi Halaman: Pastikan yang masuk adalah admin
if (!isset($_SESSION['login_admin'])) {
    header("Location: ../index.php");
    exit;
}

// 2. Hubungkan ke database
include '../config/koneksi.php';

$nama_admin = $_SESSION['nama_admin'] ?? 'Admin';

// 3. Ambil total pendaftaran anggota baru yang berstatus 'pending' untuk badge sidebar
$query_pending = mysqli_query($mysqli, "SELECT COUNT(*) AS total FROM anggota WHERE status_akun = 'pending'");
$data_pending  = mysqli_fetch_assoc($query_pending);
$anggota_pending = $data_pending['total'] ?? 0;

// 4. Aksi Update Status Denda (Bayar Denda) jika parameter lunas dikirim via URL
if (isset($_GET['aksi']) && $_GET['aksi'] == 'lunaskan' && isset($_GET['id_denda'])) {
    $id_denda_proses = $_GET['id_denda'];
    
    $sql_lunas = "UPDATE denda SET status_denda = 'lunas' WHERE id_denda = '$id_denda_proses'";
    if (mysqli_query($mysqli, $sql_lunas)) {
        $_SESSION['sukses_denda'] = "Status denda berhasil diperbarui menjadi <strong>Lunas</strong>!";
    } else {
        $_SESSION['gagal_denda'] = "Gagal memperbarui status denda.";
    }
    header("Location: denda.php");
    exit;
}

// 5. Logika Sorting via Header Tabel
$sort_by    = $_GET['by'] ?? 'status_denda'; // Kolom target default
$sort_order = $_GET['order'] ?? 'ASC';        // Arah default

// Ambil arah sebaliknya untuk link klik berikutnya (Toggle ASC <=> DESC)
$next_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC';

// Validasi kolom whitelist (MENAMBAHKAN KUNCI SORT BARU)
$allowed_columns = ['nama_lengkap', 'judul', 'tgl_pinjam', 'tgl_kembali', 'keterlambatan', 'jumlah_denda', 'status_denda'];
$allowed_orders  = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_columns)) { $sort_by = 'status_denda'; }
if (!in_array($sort_order, $allowed_orders)) { $sort_order = 'ASC'; }

// Pemetaan nama kolom SQL sesungguhnya untuk proses sorting
if ($sort_by == 'nama_lengkap') {
    $orderby_sql = "ORDER BY a.nama_lengkap $sort_order";
} elseif ($sort_by == 'judul') {
    $orderby_sql = "ORDER BY b.judul $sort_order";
} elseif ($sort_by == 'tgl_pinjam') {
    // Pengurutan berdasarkan tanggal pinjam awal sirkulasi
    $orderby_sql = "ORDER BY p.tgl_pinjam $sort_order";
} elseif ($sort_by == 'tgl_kembali') {
    // Pengurutan berdasarkan tanggal pengembalian buku
    $orderby_sql = "ORDER BY p.tgl_kembali $sort_order";
} elseif ($sort_by == 'keterlambatan') {
    // Pengurutan berdasarkan jumlah hari telat (menggunakan CURDATE() jika tanggal pengembalian masih kosong)
    $orderby_sql = "ORDER BY DATEDIFF(IF(d.status_denda = 'belum_bayar' AND (p.tgl_kembali IS NULL OR p.tgl_kembali = '0000-00-00'), CURDATE(), p.tgl_kembali), p.tgl_jatuh_tempo) $sort_order";
} elseif ($sort_by == 'jumlah_denda') {
    $orderby_sql = "ORDER BY d.jumlah_denda $sort_order";
} else {
    $orderby_sql = "ORDER BY d.status_denda $sort_order, p.tgl_kembali DESC";
}

// 6. Query gabungan mengambil rekam data denda beserta anggota dan buku yang bersangkutan
$sql_denda = "SELECT d.*, p.tgl_pinjam, p.tgl_jatuh_tempo, p.tgl_kembali, b.judul, a.nama_lengkap 
              FROM denda d
              INNER JOIN peminjaman p ON d.id_peminjaman = p.id_peminjaman
              INNER JOIN buku b ON p.id_buku = b.id_buku
              INNER JOIN anggota a ON p.id_anggota = a.id_anggota
              $orderby_sql";
$query_denda = mysqli_query($mysqli, $sql_denda);

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
  <title>PojokBaca | Data Denda</title>
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
            <span class="brand-subtitle">Administrator</span>
          </span>
        </a>
      </div>
      <nav class="sidebar-nav">
        <a class="nav-link" href="dashboard.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
        <a class="nav-link" href="katalog.php"><span class="nav-icon"><i class="bi bi-journal-text"></i></span><span class="nav-text">Data Buku</span></a>
        <a class="nav-link" href="anggota.php">
          <span class="nav-icon"><i class="bi bi-people"></i></span><span class="nav-text">Data Anggota</span>
          <?php if($anggota_pending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto px-2 rounded-pill"><?= $anggota_pending; ?></span>
          <?php endif; ?>
        </a>
        <a class="nav-link" href="peminjaman.php"><span class="nav-icon"><i class="bi bi-arrow-left-right"></i></span><span class="nav-text">Peminjaman</span></a>
        <a class="nav-link active" href="denda.php"><span class="nav-icon"><i class="bi bi-cash-coin"></i></span><span class="nav-text">Data Denda</span></a>
        
        <div class="nav-item-dropdown">
          <a class="nav-link dropdown-toggle" href="#menuKelola" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="menuKelola">
            <span class="nav-icon"><i class="bi bi-gear"></i></span><span class="nav-text">Kelola</span>
          </a>
          <div class="collapse ms-3" id="menuKelola">
            <a class="nav-link py-1 small" href="kategori.php"><i class="bi bi-tags me-2"></i>Kategori</a>
            <a class="nav-link py-1 small" href="penerbit.php"><i class="bi bi-building me-2"></i>Penerbit</a>
            <a class="nav-link py-1 small" href="rak.php"><i class="bi bi-bookshelf me-2"></i>Data Rak</a>
          </div>
        </div>

        <hr class="mx-3 my-2 text-secondary opacity-25">
        <a class="nav-link text-danger" href="../logout.php"><span class="nav-icon"><i class="bi bi-box-arrow-left text-danger"></i></span><span class="nav-text fw-bold">Logout</span></a>
      </nav>

      <div class="sidebar-user">
        <img class="avatar-img avatar-md sidebar-user-avatar" src="../assets/images/avatar/avatar.jpg" alt="<?= htmlspecialchars($nama_admin); ?>">
        <strong><?= htmlspecialchars($nama_admin); ?></strong>
        <small>Admin</small>
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
          <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="page-heading-copy">
              <span class="page-icon"><i class="bi bi-cash-coin"></i></span>
              <div>
                <p class="eyebrow mb-1">Sirkulasi Finansial</p>
                <h1 class="h3 mb-1">Data Denda Keterlambatan</h1>
                <p class="text-muted mb-0">Kelola dan pantau catatan denda peminjaman buku yang melewati batas waktu pengembalian sirkulasi.</p>
              </div>
            </div>
          </div>

          <?php if (isset($_SESSION['sukses_denda'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['sukses_denda']; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['sukses_denda']); ?>
          <?php endif; ?>

          <?php if (isset($_SESSION['gagal_denda'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $_SESSION['gagal_denda']; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['gagal_denda']); ?>
          <?php endif; ?>

          <section class="panel mt-3">
            <div class="panel-header">
              <div>
                <h2 class="h5 mb-1 section-title"><i class="bi bi-table"></i><span>Log Tagihan & Pembayaran</span></h2>
              </div>
              <input class="form-control form-control-sm table-search" type="search" placeholder="Cari data..." data-table-search="dendaTable" style="max-width: 250px;">
            </div>
            
            <div class="table-responsive">
              <table class="table align-middle mb-0 table-hover" id="dendaTable">
                <thead>
                  <tr>
                    <th class="sortable-header">
                      <a href="denda.php?by=nama_lengkap&order=<?= ($sort_by == 'nama_lengkap') ? $next_order : 'ASC'; ?>">
                        Nama Peminjam <?= getSortIcon('nama_lengkap', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=judul&order=<?= ($sort_by == 'judul') ? $next_order : 'ASC'; ?>">
                        Buku Terlambat <?= getSortIcon('judul', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <!-- FITUR SORT AKTIF: Masa Pinjam -->
                    <th class="sortable-header">
                      <a href="denda.php?by=tgl_pinjam&order=<?= ($sort_by == 'tgl_pinjam') ? $next_order : 'ASC'; ?>">
                        Masa Pinjam <?= getSortIcon('tgl_pinjam', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <!-- FITUR SORT AKTIF: Dikembalikan -->
                    <th class="sortable-header">
                      <a href="denda.php?by=tgl_kembali&order=<?= ($sort_by == 'tgl_kembali') ? $next_order : 'ASC'; ?>">
                        Dikembalikan <?= getSortIcon('tgl_kembali', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <!-- FITUR SORT AKTIF: Keterlambatan -->
                    <th class="sortable-header">
                      <a href="denda.php?by=keterlambatan&order=<?= ($sort_by == 'keterlambatan') ? $next_order : 'ASC'; ?>">
                        Keterlambatan <?= getSortIcon('keterlambatan', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=jumlah_denda&order=<?= ($sort_by == 'jumlah_denda') ? $next_order : 'ASC'; ?>">
                        Total Denda <?= getSortIcon('jumlah_denda', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="sortable-header">
                      <a href="denda.php?by=status_denda&order=<?= ($sort_by == 'status_denda') ? $next_order : 'ASC'; ?>">
                        Status <?= getSortIcon('status_denda', $sort_by, $sort_order); ?>
                      </a>
                    </th>
                    <th class="text-end" style="min-width: 130px;">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  if (mysqli_num_rows($query_denda) > 0): 
                    while($row = mysqli_fetch_assoc($query_denda)):
                      
                      $status_denda = $row['status_denda'];
                      $tgl_tempo = strtotime($row['tgl_jatuh_tempo']);
                      
                      // LOGIKA BARU: Jika belum bayar / belum dikembalikan, hitung selisih dari tanggal hari ini
                      if ($status_denda == 'belum_bayar' && (empty($row['tgl_kembali']) || $row['tgl_kembali'] == '0000-00-00')) {
                          $tgl_hitung = time(); // Menggunakan waktu sekarang/hari ini
                          $tgl_kembali_view = '<span class="text-muted fw-bold">-</span>';
                      } else {
                          $tgl_hitung = strtotime($row['tgl_kembali']);
                          $tgl_kembali_view = '<span class="fw-medium">' . date('d M Y', $tgl_hitung) . '</span>';
                      }

                      $selisih_hari = floor(($tgl_hitung - $tgl_tempo) / (60 * 60 * 24));
                      $hari_telat = ($selisih_hari > 0) ? $selisih_hari . ' Hari' : '0 Hari';

                      if ($status_denda == 'lunas') {
                          $badge_denda = '<span class="badge text-bg-success"><i class="bi bi-cash me-1"></i>Lunas</span>';
                      } else {
                          $badge_denda = '<span class="badge text-bg-danger"><i class="bi bi-clock-history me-1"></i>Belum Bayar</span>';
                      }
                  ?>
                    <tr>
                      <td class="fw-medium text-primary"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                      <td>
                        <div class="table-media">
                          <span class="brand-icon"><i class="bi bi-book-half"></i></span>
                          <span class="fw-semibold text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['judul']); ?>"><?= htmlspecialchars($row['judul']); ?></span>
                        </div>
                      </td>
                      <td class="small text-muted">
                        <?= date('d/m/y', strtotime($row['tgl_pinjam'])); ?> s/d <span class="text-danger"><?= date('d/m/y', strtotime($row['tgl_jatuh_tempo'])); ?></span>
                      </td>
                      <td><?= $tgl_kembali_view; ?></td>
                      <td><span class="badge bg-light text-dark border px-2"><?= $hari_telat; ?></span></td>
                      <td class="fw-bold">Rp <?= number_format($row['jumlah_denda'], 0, ',', '.'); ?></td>
                      <td><?= $badge_denda; ?></td>
                      <td class="text-end">
                        <?php if ($status_denda == 'belum_bayar'): ?>
                          <a href="denda.php?aksi=lunaskan&id_denda=<?= $row['id_denda']; ?>" class="btn btn-outline-success btn-sm fw-medium px-3 py-1" onclick="return confirm('Apakah denda untuk anggota ini dikonfirmasi telah lunas dibayarkan?')">
                            <i class="bi bi-check2-circle me-1"></i>Bayar
                          </a>
                        <?php else: ?>
                          <span class="text-success small fw-medium"><i class="bi bi-shield-check me-1 fs-5 align-middle"></i>Terverifikasi</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php 
                    endwhile; 
                  else: 
                  ?>
                    <tr>
                      <td colspan="8" class="text-center py-4 text-muted">
                        <i class="bi bi-emoji-smile display-6 d-block mb-2 text-secondary"></i> Bersih dari denda. Belum ada catatan tagihan keterlambatan buku.
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